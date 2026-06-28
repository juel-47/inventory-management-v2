<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\InventoryStock;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseAttachment;
use App\Models\PurchaseDetail;
use App\Models\StockLedger;
use App\Models\Vendor;
use App\Support\AuditLogSupport;
use App\Support\StoredFileSupport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function createPurchase(array $validated, array $items, $invoiceFile): Purchase
    {
        DB::beginTransaction();
        try {
            $purchase = new Purchase();
            $purchase->invoice_no = 'INV-' . mt_rand(100000, 999999);
            $purchase->vendor_id = $validated['vendor_id'];
            $purchase->booking_id = $validated['booking_id'] ?? null;
            $purchase->user_id = Auth::id();
            $purchase->date = $validated['date'];
            $purchase->note = $validated['note'] ?? null;
            $purchase->shipping_method = $validated['shipping_method'] ?? null;
            $purchase->material_cost = $validated['material_cost'] ?? 0;
            $purchase->transport_cost = $validated['transport_cost'] ?? 0;
            $purchase->tax = $validated['tax'] ?? 0;
            $purchase->total_amount = 0;
            $purchase->status = 1;

            $storedInvoiceAttachment = null;

            if ($invoiceFile) {
                $filename = 'invoice_' . time() . '_' . uniqid() . '.' . $invoiceFile->getClientOriginalExtension();
                $path = StoredFileSupport::storePrivateFile($invoiceFile, 'attachments/purchases', $filename);
                $purchase->invoice_attachment = $path;
                $storedInvoiceAttachment = [
                    'file_path' => $path,
                    'original_name' => $invoiceFile->getClientOriginalName(),
                    'mime_type' => $invoiceFile->getClientMimeType(),
                    'file_size' => $invoiceFile->getSize(),
                ];
            }

            $purchase->save();

            if ($storedInvoiceAttachment) {
                $purchase->attachments()->create([
                    'file_path' => $storedInvoiceAttachment['file_path'],
                    'original_name' => $storedInvoiceAttachment['original_name'],
                    'mime_type' => $storedInvoiceAttachment['mime_type'],
                    'file_size' => $storedInvoiceAttachment['file_size'],
                    'uploaded_by' => Auth::id(),
                ]);
            }

            $vendor = Vendor::findOrFail($validated['vendor_id']);
            $rate = $vendor->currency_rate > 0 ? $vendor->currency_rate : 1;

            $totalAmount = 0;

            $rule = null;
            if (!empty($validated['pricing_rule_id'])) {
                $rule = PricingRule::active()->find($validated['pricing_rule_id']);
            }

            foreach ($items as $item) {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $rawMaterial = (float) ($item['raw_material_cost'] ?? 0);
                $tax = (float) ($item['tax_cost'] ?? 0);
                $transport = (float) ($item['transport_cost'] ?? 0);
                $itemUnitCost = $rawMaterial + $tax + $transport;
                $subTotal = $itemUnitCost * $qty;
                $totalAmount += $subTotal;

                $detail = new PurchaseDetail();
                $detail->purchase_id = $purchase->id;
                $detail->product_id = $item['product_id'];
                $detail->qty = $qty;
                $detail->unit_cost = $itemUnitCost;
                $detail->unit_cost_vendor = (float) ($item['unit_cost'] ?? 0);
                $detail->raw_material_cost = $rawMaterial;
                $detail->tax_cost = $tax;
                $detail->transport_cost = $transport;
                $detail->total = $subTotal;

                $vInfo = null;
                if (!empty($item['variant_info'])) {
                    $vInfo = is_string($item['variant_info']) ? json_decode($item['variant_info'], true) : $item['variant_info'];
                    $detail->variant_info = $vInfo;
                }

                $detail->save();

                $product = Product::findOrFail($item['product_id']);
                $product->purchase_price = $itemUnitCost;
                $product->raw_material_cost = $rawMaterial;
                $product->tax = $tax;
                $product->transport_cost = $transport;

                if ($rule) {
                    $product->price = round($itemUnitCost * (float) $rule->sale_multiplier, 2);
                    $product->outlet_price = round($itemUnitCost * (float) $rule->outlet_multiplier, 2);
                } else {
                    if (isset($item['sale_price'])) {
                        $product->price = $item['sale_price'];
                    }
                    if (isset($item['outlet_price'])) {
                        $product->outlet_price = $item['outlet_price'];
                    }
                }

                $product->save();

                $this->processVariantStock($item['product_id'], $qty, $vInfo, $purchase->id, $validated['date'], $detail);
            }

            $purchase->total_amount = $totalAmount;
            $purchase->paid_amount = 0;
            $purchase->due_amount = $totalAmount;
            $purchase->payment_status = $totalAmount > 0 ? 'pending' : 'paid';
            $purchase->save();

            if ($purchase->booking_id) {
                $targetBooking = Booking::find($purchase->booking_id);
                if ($targetBooking) {
                    Booking::where('booking_no', $targetBooking->booking_no)->update(['status' => 'complete']);
                }
            }

            AuditLogSupport::log([
                'vendor_id' => $purchase->vendor_id,
                'module' => 'purchases',
                'action' => 'purchase_created',
                'entity_type' => 'purchase',
                'entity_id' => $purchase->id,
                'reference_no' => $purchase->invoice_no,
                'description' => 'Purchase created.',
                'new_values' => [
                    'vendor_id' => $purchase->vendor_id,
                    'booking_id' => $purchase->booking_id,
                    'date' => $purchase->date,
                    'total_amount' => (float) $purchase->total_amount,
                    'paid_amount' => (float) $purchase->paid_amount,
                    'due_amount' => (float) $purchase->due_amount,
                    'payment_status' => (string) $purchase->payment_status,
                    'item_count' => count($items),
                    'attachment_count' => $purchase->attachments()->count(),
                ],
            ]);

            DB::commit();
            return $purchase;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deletePurchase(int $id): void
    {
        DB::beginTransaction();
        try {
            $purchase = Purchase::with(['details', 'attachments'])->findOrFail($id);

            foreach ($purchase->details as $detail) {
                $variantId = $detail->variant_id;
                $stock = InventoryStock::where('product_id', $detail->product_id)
                    ->where('variant_id', $variantId)
                    ->where('outlet_id', 1)
                    ->first();

                if ($stock) {
                    $stock->decrement('quantity', $detail->qty);

                    StockLedger::create([
                        'product_id' => $detail->product_id,
                        'variant_id' => $variantId,
                        'outlet_id' => 1,
                        'reference_type' => 'purchase_delete',
                        'reference_id' => $purchase->id,
                        'in_qty' => 0,
                        'out_qty' => $detail->qty,
                        'balance_qty' => $stock->quantity,
                        'date' => date('Y-m-d'),
                    ]);
                }
            }

            $attachmentPaths = $purchase->attachments->pluck('file_path')->filter()->unique()->values()->all();
            if ($purchase->invoice_attachment) {
                $attachmentPaths[] = $purchase->invoice_attachment;
            }
            $attachmentPaths = array_values(array_unique($attachmentPaths));

            foreach ($attachmentPaths as $filePath) {
                StoredFileSupport::delete($filePath);
            }

            AuditLogSupport::log([
                'vendor_id' => $purchase->vendor_id,
                'module' => 'purchases',
                'action' => 'purchase_deleted',
                'entity_type' => 'purchase',
                'entity_id' => $purchase->id,
                'reference_no' => $purchase->invoice_no,
                'description' => 'Purchase deleted and stock reverted.',
                'old_values' => [
                    'vendor_id' => $purchase->vendor_id,
                    'total_amount' => (float) $purchase->total_amount,
                    'paid_amount' => (float) $purchase->paid_amount,
                    'due_amount' => (float) $purchase->due_amount,
                    'payment_status' => (string) $purchase->payment_status,
                    'detail_count' => $purchase->details->count(),
                    'attachment_count' => $purchase->attachments->count(),
                ],
            ]);

            $purchase->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function uploadAttachments(Purchase $purchase, array $files): void
    {
        DB::beginTransaction();
        try {
            $storedPaths = [];

            foreach ($files as $file) {
                $filename = 'invoice_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = StoredFileSupport::storePrivateFile($file, 'attachments/purchases/' . $purchase->id, $filename);
                $storedPaths[] = $path;

                $purchase->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);

                if (empty($purchase->invoice_attachment)) {
                    $purchase->invoice_attachment = $path;
                    $purchase->save();
                }

                $latestAttachment = $purchase->attachments()->latest('id')->first();

                AuditLogSupport::log([
                    'vendor_id' => $purchase->vendor_id,
                    'module' => 'purchases',
                    'action' => 'purchase_attachment_uploaded',
                    'entity_type' => 'purchase_attachment',
                    'entity_id' => $latestAttachment?->id,
                    'reference_no' => $purchase->invoice_no,
                    'description' => 'Purchase attachment uploaded.',
                    'new_values' => [
                        'purchase_id' => $purchase->id,
                        'attachment_id' => $latestAttachment?->id,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                    ],
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($storedPaths ?? [] as $filePath) {
                StoredFileSupport::delete($filePath);
            }
            throw $e;
        }
    }

    public function deleteAttachment(Purchase $purchase, int $attachmentId): void
    {
        DB::beginTransaction();
        try {
            $attachment = PurchaseAttachment::where('purchase_id', $purchase->id)->findOrFail($attachmentId);
            $filePath = $attachment->file_path;
            $legacyMatchesThis = $purchase->invoice_attachment === $filePath;

            AuditLogSupport::log([
                'vendor_id' => $purchase->vendor_id,
                'module' => 'purchases',
                'action' => 'purchase_attachment_deleted',
                'entity_type' => 'purchase_attachment',
                'entity_id' => $attachment->id,
                'reference_no' => $purchase->invoice_no,
                'description' => 'Purchase attachment deleted.',
                'old_values' => [
                    'purchase_id' => $purchase->id,
                    'attachment_id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'file_path' => $attachment->file_path,
                ],
            ]);

            StoredFileSupport::delete($filePath);
            $attachment->delete();

            if ($legacyMatchesThis) {
                $nextAttachmentPath = PurchaseAttachment::where('purchase_id', $purchase->id)
                    ->orderByDesc('id')
                    ->value('file_path');
                $purchase->invoice_attachment = $nextAttachmentPath;
                $purchase->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function processVariantStock(int $productId, int $qty, $vInfo, int $purchaseId, string $date, PurchaseDetail $detail): void
    {
        if ($vInfo && is_array($vInfo)) {
            $processedVariants = [];

            if (isset($vInfo['variant'])) {
                $variantName = $vInfo['variant'];
                $variantQty = $qty;
                $pVariant = ProductVariant::where('product_id', $productId)->where('name', $variantName)->first();

                if ($pVariant && !in_array($pVariant->id, $processedVariants)) {
                    $processedVariants[] = $pVariant->id;
                    $stock = $this->createOrIncrementStock($productId, $pVariant->id, $variantQty);
                    $this->createLedger($productId, $pVariant->id, $purchaseId, $variantQty, $stock->quantity, $date, 'purchase');
                    $detail->variant_id = $pVariant->id;
                    $detail->save();
                }
            } else {
                foreach ($vInfo as $vName => $vQty) {
                    $pVariant = $this->findVariant($productId, $vName);

                    if ($pVariant) {
                        $stock = $this->createOrIncrementStock($productId, $pVariant->id, $vQty);
                        $this->createLedger($productId, $pVariant->id, $purchaseId, $vQty, $stock->quantity, $date, 'purchase');

                        if (!$detail->variant_id) {
                            $detail->variant_id = $pVariant->id;
                            $detail->save();
                        }
                    } else {
                        $stock = $this->createOrIncrementStock($productId, null, $vQty);
                        $this->createLedger($productId, null, $purchaseId, $vQty, $stock->quantity, $date, 'purchase');
                    }
                }
            }
        } else {
            $stock = $this->createOrIncrementStock($productId, null, $qty);
            $this->createLedger($productId, null, $purchaseId, $qty, $stock->quantity, $date, 'purchase');
        }
    }

    private function findVariant(int $productId, string $name): ?ProductVariant
    {
        $pVariant = ProductVariant::where('product_id', $productId)->where('name', trim($name))->first();
        if ($pVariant) return $pVariant;

        $cleanName = preg_replace('/(Color|Size):\s*/i', '', $name);
        $cleanName = trim($cleanName);
        if ($cleanName !== $name) {
            $pVariant = ProductVariant::where('product_id', $productId)->where('name', $cleanName)->first();
            if ($pVariant) return $pVariant;
        }

        $cleanNameLegacy = preg_replace('/\s*-\s*/', ' ', $cleanName);
        $cleanNameLegacy = trim($cleanNameLegacy);
        if ($cleanNameLegacy !== $cleanName) {
            $pVariant = ProductVariant::where('product_id', $productId)->where('name', $cleanNameLegacy)->first();
            if ($pVariant) return $pVariant;
        }

        return null;
    }

    private function createOrIncrementStock(int $productId, ?int $variantId, int $qty): InventoryStock
    {
        $stock = InventoryStock::firstOrCreate([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'outlet_id' => 1,
        ]);
        $stock->increment('quantity', $qty);
        return $stock;
    }

    private function createLedger(int $productId, ?int $variantId, int $referenceId, int $inQty, int $balanceQty, string $date, string $type): void
    {
        StockLedger::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'outlet_id' => 1,
            'reference_type' => $type,
            'reference_id' => $referenceId,
            'in_qty' => $inQty,
            'out_qty' => 0,
            'balance_qty' => $balanceQty,
            'date' => $date,
        ]);
    }
}
