<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderPaymentReceipt;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentReceipt;
use App\Models\Vendor;
use App\Support\AuditLogSupport;
use App\Support\StoredFileSupport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function findOrderForPayment(string $orderNo): ?array
    {
        $order = Order::with('user')->where('order_no', $orderNo)->first();

        if (!$order) {
            return null;
        }

        if ($order->status !== 'completed') {
            return ['error' => 'Payment can only be recorded for completed orders.', 'code' => 422];
        }

        $order->reconcileTotals();
        $order->refresh();

        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'customer_name' => $order->billing_name,
            'total_amount' => number_format($order->total_amount, 2),
            'paid_amount' => number_format($order->paid_amount, 2),
            'due_amount' => number_format($order->due_amount, 2),
            'due_raw' => $order->due_amount,
            'status' => ucfirst($order->status),
        ];
    }

    public function findPurchaseForPayment(string $invoiceNo): ?array
    {
        $purchase = Purchase::with('vendor')->where('invoice_no', $invoiceNo)->first();

        if (!$purchase) {
            return null;
        }

        return [
            'id' => $purchase->id,
            'invoice_no' => $purchase->invoice_no,
            'vendor_name' => $purchase->vendor->shop_name ?? 'N/A',
            'purchase_date' => (string) $purchase->date,
            'total_amount' => number_format((float) $purchase->total_amount, 2),
            'paid_amount' => number_format((float) $purchase->paid_amount, 2),
            'due_amount' => number_format((float) $purchase->due_amount, 2),
            'due_raw' => (float) $purchase->due_amount,
            'payment_status' => ucfirst((string) ($purchase->payment_status ?: 'pending')),
        ];
    }

    public function recordOrderPayment(Order $order, float $amount, string $method, ?string $transactionId, ?string $note, array $receiptFiles): array
    {
        if ($order->status !== 'completed') {
            return ['success' => false, 'error' => 'Payment can only be recorded for completed orders.'];
        }

        $order->reconcileTotals();
        $order->refresh();

        if ($amount > $order->due_amount) {
            return ['success' => false, 'error' => 'Payment amount cannot be greater than the due amount!'];
        }

        $before = [
            'paid_amount' => (float) $order->paid_amount,
            'due_amount' => (float) $order->due_amount,
            'payment_status' => (string) $order->payment_status,
        ];

        $payment = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'payment_method' => $method,
            'transaction_id' => $transactionId,
            'note' => $note,
        ]);

        $this->storePaymentReceipts($receiptFiles, "order-payments/{$payment->id}", fn ($data) => OrderPaymentReceipt::create(array_merge($data, ['order_payment_id' => $payment->id])));

        $order->paid_amount += $amount;
        $order->due_amount -= $amount;
        $order->payment_status = $order->due_amount <= 0 ? 'paid' : 'partial';
        $order->save();

        AuditLogSupport::log([
            'module' => 'accounts',
            'action' => 'customer_payment_created',
            'entity_type' => 'order_payment',
            'entity_id' => $payment->id,
            'reference_no' => $order->order_no,
            'description' => 'Customer payment recorded.',
            'old_values' => $before,
            'new_values' => [
                'payment_id' => $payment->id,
                'amount' => $amount,
                'payment_method' => $method,
                'transaction_id' => $transactionId,
                'receipt_count' => $payment->receipts()->count(),
                'paid_amount' => (float) $order->paid_amount,
                'due_amount' => (float) $order->due_amount,
                'payment_status' => (string) $order->payment_status,
            ],
        ]);

        return ['success' => true, 'payment' => $payment];
    }

    public function recordVendorPayment(Purchase $purchase, float $amount, string $method, ?string $transactionId, ?string $note, array $receiptFiles): array
    {
        if ($amount > (float) $purchase->due_amount) {
            return ['success' => false, 'error' => 'Payment amount cannot be greater than the due amount!'];
        }

        $before = [
            'paid_amount' => (float) $purchase->paid_amount,
            'due_amount' => (float) $purchase->due_amount,
            'payment_status' => (string) $purchase->payment_status,
        ];

        DB::beginTransaction();
        try {
            $payment = PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'vendor_id' => $purchase->vendor_id,
                'amount' => $amount,
                'payment_method' => $method,
                'transaction_id' => $transactionId,
                'note' => $note,
            ]);

            $this->storePaymentReceipts($receiptFiles, "purchase-payments/{$payment->id}", fn ($data) => PurchasePaymentReceipt::create(array_merge($data, ['purchase_payment_id' => $payment->id])));

            $purchase->paid_amount = round((float) $purchase->paid_amount + $amount, 2);
            $purchase->due_amount = max(0, round((float) $purchase->due_amount - $amount, 2));
            $purchase->payment_status = $purchase->due_amount <= 0 ? 'paid' : 'partial';
            $purchase->save();

            AuditLogSupport::log([
                'user_id' => auth()->id(),
                'vendor_id' => $purchase->vendor_id,
                'module' => 'accounts',
                'action' => 'vendor_payment_created',
                'entity_type' => 'purchase_payment',
                'entity_id' => $payment->id,
                'reference_no' => $purchase->invoice_no,
                'description' => 'Vendor payment recorded.',
                'old_values' => $before,
                'new_values' => [
                    'payment_id' => $payment->id,
                    'purchase_id' => $purchase->id,
                    'amount' => $amount,
                    'payment_method' => $method,
                    'transaction_id' => $transactionId,
                    'receipt_count' => $payment->receipts()->count(),
                    'paid_amount' => (float) $purchase->paid_amount,
                    'due_amount' => (float) $purchase->due_amount,
                    'payment_status' => (string) $purchase->payment_status,
                ],
            ]);

            DB::commit();
            return ['success' => true, 'payment' => $payment];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Payment failed: ' . $e->getMessage()];
        }
    }

    public function deleteReceipt($receipt, string $type = 'order'): void
    {
        if ($type === 'order') {
            $payment = $receipt->payment()->with('order')->first();
            AuditLogSupport::log([
                'module' => 'accounts',
                'action' => 'customer_payment_receipt_deleted',
                'entity_type' => 'order_payment_receipt',
                'entity_id' => $receipt->id,
                'reference_no' => $payment?->order?->order_no,
                'description' => 'Customer payment receipt deleted.',
                'old_values' => [
                    'receipt_id' => $receipt->id,
                    'payment_id' => $payment?->id,
                    'original_name' => $receipt->original_name,
                    'file_path' => $receipt->file_path,
                ],
            ]);
        } else {
            $payment = $receipt->payment()->with('purchase')->first();
            AuditLogSupport::log([
                'vendor_id' => $payment?->vendor_id,
                'module' => 'accounts',
                'action' => 'vendor_payment_receipt_deleted',
                'entity_type' => 'purchase_payment_receipt',
                'entity_id' => $receipt->id,
                'reference_no' => $payment?->purchase?->invoice_no,
                'description' => 'Vendor payment receipt deleted.',
                'old_values' => [
                    'receipt_id' => $receipt->id,
                    'payment_id' => $payment?->id,
                    'original_name' => $receipt->original_name,
                    'file_path' => $receipt->file_path,
                ],
            ]);
        }

        StoredFileSupport::delete($receipt->file_path);
        $receipt->delete();
    }

    private function storePaymentReceipts(array $files, string $directory, callable $createRecord): void
    {
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $storedPath = StoredFileSupport::storePrivateFile($file, $directory, $filename);

            $createRecord([
                'file_path' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }
}
