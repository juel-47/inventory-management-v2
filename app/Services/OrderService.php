<?php

namespace App\Services;

use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderPiInvoiceReadyMail;
use App\Models\IssueItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\PiInfoSupport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OrderService
{
    public function __construct(
        private CheckoutTaxResolver $taxResolver,
        private CheckoutDiscountResolver $discountResolver
    ) {}

    public function reorder(Order $sourceOrder, $user): array
    {
        $sourceOrder->load('items');
        if ($sourceOrder->items->isEmpty()) {
            return ['success' => false, 'error' => 'Reorder failed: this order has no items.'];
        }

        $productIds = $sourceOrder->items->pluck('product_id')->filter()->unique()->values()->all();
        $variantIds = $sourceOrder->items->pluck('variant_id')->filter()->unique()->values()->all();

        $products = Product::query()->with('category')->whereIn('id', $productIds)->get()->keyBy('id');
        $variants = ProductVariant::query()->whereIn('id', $variantIds)->get()->keyBy('id');

        $missingLines = [];
        $preparedLines = [];
        $subtotal = 0.0;
        $taxAmount = 0.0;
        $discountAmount = 0.0;
        $appliedTaxSignatures = [];
        $hasDefaultFlatTax = false;
        $defaultFlatTaxValue = 0.0;

        foreach ($sourceOrder->items as $oldItem) {
            $product = $products->get((int) $oldItem->product_id);
            if (!$product) {
                $missingLines[] = $oldItem->product_name ?: ('Item #' . $oldItem->id);
                continue;
            }

            $variant = null;
            if (!empty($oldItem->variant_id)) {
                $variant = $variants->get((int) $oldItem->variant_id);
                if (!$variant || (int) $variant->product_id !== (int) $product->id) {
                    $missingLines[] = ($oldItem->product_name ?: $product->name) . ' (variant unavailable)';
                    continue;
                }
            }

            $qty = max(1, (int) $oldItem->quantity);
            $unitPrice = $this->resolveCurrentUnitPrice($product, $variant, $user);
            $lineSubtotal = round($unitPrice * $qty, 2);
            $subtotal += $lineSubtotal;

            $lineDiscount = $this->discountResolver->resolveForLine($product, $lineSubtotal, $qty);
            $discountAmount += (float) ($lineDiscount['amount'] ?? 0);

            $lineTax = $this->taxResolver->resolveForLine($product, $lineSubtotal);
            if ($lineTax['source'] === 'default' && $lineTax['type'] === 'flat') {
                $hasDefaultFlatTax = true;
                $defaultFlatTaxValue = max($defaultFlatTaxValue, (float) $lineTax['value']);
            } else {
                $taxAmount += (float) $lineTax['amount'];
            }

            if ($lineTax['source'] !== 'none') {
                $appliedTaxSignatures[] = ($lineTax['source'] . ':' . ($lineTax['type'] ?? 'none') . ':' . (string) $lineTax['value']);
            }

            $preparedLines[] = [
                'product_id' => (int) $product->id,
                'variant_id' => $variant?->id,
                'vendor_id' => $product->vendor_id ?: $oldItem->vendor_id,
                'product_name' => $product->name,
                'category_name' => $product->category->name ?? ($oldItem->category_name ?: 'General'),
                'variant_label' => $this->resolveVariantLabel($variant, $oldItem),
                'product_image' => $product->thumb_image ?: $oldItem->product_image,
                'unit_price' => (float) $unitPrice,
                'quantity' => $qty,
                'line_total' => $lineSubtotal,
            ];
        }

        if (!empty($missingLines)) {
            $preview = implode(', ', array_slice($missingLines, 0, 3));
            $moreCount = max(0, count($missingLines) - 3);
            $suffix = $moreCount > 0 ? (' and ' . $moreCount . ' more') : '';
            return ['success' => false, 'error' => 'Reorder failed. Unavailable item(s): ' . $preview . $suffix . '.'];
        }

        if (empty($preparedLines)) {
            return ['success' => false, 'error' => 'Reorder failed: no valid items found.'];
        }

        if ($hasDefaultFlatTax && !empty($preparedLines)) {
            $taxAmount += $defaultFlatTaxValue;
        }

        $subtotal = round($subtotal, 2);
        $taxAmount = round($taxAmount, 2);
        $discountAmount = round($discountAmount, 2);
        $total = round(max(0, $subtotal + $taxAmount - $discountAmount), 2);
        $taxMeta = $this->buildTaxMeta($appliedTaxSignatures);

        DB::beginTransaction();
        try {
            $newOrder = Order::create([
                'order_no' => $this->generateOrderNo($user),
                'user_id' => $user->id,
                'status' => 'pending',
                'shipping_method' => 'frontend_reorder',
                'ship_different' => (bool) $sourceOrder->ship_different,
                'billing_name' => $sourceOrder->billing_name,
                'billing_email' => $sourceOrder->billing_email,
                'billing_phone' => $sourceOrder->billing_phone,
                'billing_address' => $sourceOrder->billing_address,
                'billing_outlet_name' => $sourceOrder->billing_outlet_name,
                'shipping_name' => $sourceOrder->ship_different ? $sourceOrder->shipping_name : null,
                'shipping_email' => $sourceOrder->ship_different ? $sourceOrder->shipping_email : null,
                'shipping_phone' => $sourceOrder->ship_different ? $sourceOrder->shipping_phone : null,
                'shipping_address' => $sourceOrder->ship_different ? $sourceOrder->shipping_address : null,
                'shipping_city' => $sourceOrder->ship_different ? $sourceOrder->shipping_city : null,
                'shipping_state' => $sourceOrder->ship_different ? $sourceOrder->shipping_state : null,
                'shipping_zip_code' => $sourceOrder->ship_different ? $sourceOrder->shipping_zip_code : null,
                'shipping_country' => $sourceOrder->ship_different ? $sourceOrder->shipping_country : null,
                'shipping_outlet_name' => $sourceOrder->ship_different ? $sourceOrder->shipping_outlet_name : null,
                'subtotal_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $total,
                'tax_label' => $taxMeta['tax_label'],
                'vat_rate' => $taxMeta['vat_rate'],
                'placed_at' => now(),
            ]);

            foreach ($preparedLines as $line) {
                OrderItem::create([
                    'order_id' => $newOrder->id,
                    'product_id' => $line['product_id'],
                    'variant_id' => $line['variant_id'],
                    'vendor_id' => $line['vendor_id'],
                    'product_name' => $line['product_name'],
                    'category_name' => $line['category_name'],
                    'variant_label' => $line['variant_label'],
                    'product_image' => $line['product_image'],
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);
            }

            DB::commit();

            try {
                Mail::to('ctpwh2026@gmail.com')->send(new AdminOrderNotificationMail($newOrder));
            } catch (\Exception $e) {
                Log::error('Failed to send admin order notification on reorder: ' . $e->getMessage());
            }

            return ['success' => true, 'order' => $newOrder];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Reorder failed: ' . $e->getMessage()];
        }
    }

    public function savePiInfo(Order $order, array $piData): void
    {
        $order->pi_info = PiInfoSupport::sanitizePayload($piData);

        $path = 'invoices/pi-invoice-' . $order->order_no . '.pdf';
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $order->save();
        $this->notifyPiReady($order);
    }

    public function getIssuedItems(Order $order)
    {
        $issueItems = IssueItem::whereHas('issue', fn ($q) => $q->where('order_id', $order->id))
            ->with(['product', 'variant'])
            ->get();

        if ($issueItems->isEmpty()) {
            return $order->items;
        }

        return $issueItems->map(function ($issueItem) {
            $product = $issueItem->product;
            $variant = $issueItem->variant;

            return (object) [
                'id' => $issueItem->id,
                'product_id' => $issueItem->product_id,
                'variant_id' => $issueItem->variant_id,
                'product_name' => $product->name ?? 'Deleted Product',
                'product_number' => $product->product_number ?? 'N/A',
                'product_image' => $product->thumb_image ?? null,
                'category_name' => optional($product->category)->name ?? 'General',
                'variant_label' => $variant->name ?? 'Standard',
                'quantity' => $issueItem->quantity,
                'unit_price' => $product->outlet_price ?? 0,
                'line_total' => $issueItem->quantity * ($product->outlet_price ?? 0),
                'product' => $product,
                'variant' => $variant,
            ];
        });
    }

    public function generateOrderNo($user): string
    {
        $isOutletUser = $user && ($user->hasRole('Outlet User') || $user->hasRole('Outlet'));
        $prefix = $isOutletUser ? 'DS' : 'ORD';
        return OrderNumberService::generate($prefix, Order::class);
    }

    private function resolveCurrentUnitPrice(Product $product, ?ProductVariant $variant, $user): float
    {
        $isOutletRole = $user->hasRole('Outlet User') || $user->hasRole('User');
        $price = $isOutletRole
            ? ($variant ? ($variant->outlet_price ?: $product->outlet_price ?: $product->price) : ($product->outlet_price ?: $product->price))
            : ($variant ? ($variant->price ?: $product->price) : $product->price);
        return (float) $price;
    }

    private function resolveVariantLabel(?ProductVariant $variant, OrderItem $oldItem): ?string
    {
        if (!$variant) {
            $fallback = trim((string) ($oldItem->variant_label ?? ''));
            return $fallback !== '' ? $fallback : null;
        }
        $label = trim((string) ($variant->name ?? ''));
        if ($label === '') {
            $parts = array_filter([
                trim((string) ($variant->color ?? '')),
                trim((string) ($variant->size ?? '')),
            ]);
            $label = implode(' - ', $parts);
        }
        return $label !== '' ? $label : null;
    }

    private function buildTaxMeta(array $signatures): array
    {
        $taxLabel = 'VAT / Tax';
        $vatRate = null;
        $uniqueSignatures = array_values(array_unique($signatures));
        $defaultTax = $this->taxResolver->getDefaultTax();

        if (count($uniqueSignatures) > 1) {
            $taxLabel = 'VAT / Tax (Mixed)';
        } elseif (count($uniqueSignatures) === 1) {
            [$source, $type, $value] = explode(':', $uniqueSignatures[0]);
            if ($type === 'percent') {
                $vatRate = (float) $value;
                $taxLabel = 'VAT (' . rtrim(rtrim(number_format($vatRate, 2, '.', ''), '0'), '.') . '%)';
            } elseif ($type === 'flat') {
                $taxLabel = $source === 'product' ? 'Product VAT (Flat)' : 'VAT (Flat)';
            }
        } elseif ($defaultTax && $defaultTax->type === 'percent') {
            $vatRate = (float) $defaultTax->value;
            $taxLabel = 'VAT (' . rtrim(rtrim(number_format($vatRate, 2, '.', ''), '0'), '.') . '%)';
        } elseif ($defaultTax && $defaultTax->type === 'flat') {
            $taxLabel = 'VAT (Flat)';
        }

        return ['tax_label' => $taxLabel, 'vat_rate' => $vatRate];
    }

    private function notifyPiReady(Order $order): void
    {
        $order->loadMissing('user');
        $recipient = $order->pi_email ?: $order->billing_email ?: ($order->user?->email ?? null);
        if (!$recipient) {
            return;
        }

        $attachPdf = (bool) config('mail.attach_pi_pdf', true);

        try {
            Mail::to($recipient)->send(new OrderPiInvoiceReadyMail(
                $order,
                route('orders.pi-invoice', $order->id),
                route('orders.pi-invoice.download', $order->id),
                $attachPdf
            ));
        } catch (\Throwable $e) {
            Log::warning('Failed to send PI invoice email for order.', [
                'order_id' => $order->id,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
