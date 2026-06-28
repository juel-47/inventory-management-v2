<?php

namespace App\Services;

use App\Events\ProductsPublished;
use App\Imports\ProductsImport;
use App\Jobs\DispatchProductAnnouncementChunksJob;
use App\Models\Color;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\StockLedger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function createProduct(array $data, array $variantRows, ?string $imagePath): Product
    {
        $discountConfig = $this->normalizeDiscountInput($data);
        $vatConfig = $this->normalizeVatInput($data);
        $hasVariantRows = !empty($variantRows);

        $product = new Product();
        $product->thumb_image = $imagePath;
        $product->name = $data['name'];
        $product->slug = Str::slug($data['name']);
        $product->category_id = $data['category_id'];
        $product->sub_category_id = $data['sub_category_id'] ?? null;
        $product->child_category_id = $data['child_category_id'] ?? null;
        $product->brand_id = $data['brand_id'] ?? null;
        $product->vendor_id = $data['vendor_id'] ?? null;
        $product->unit_id = $data['unit_id'] ?? null;
        $product->product_number = $data['product_number'] ?? null;
        $product->long_description = $data['long_description'] ?? null;
        $product->purchase_price = $data['purchase_price'] ?? 0;
        $product->price = $data['price'] ?? 0;
        $product->outlet_price = $data['outlet_price'] ?? 0;
        $product->barcode = $data['barcode'] ?? null;
        $product->status = $data['status'] ?? 1;
        $product->product_type = $data['product_type'] ?? null;
        $product->product_type_id = $data['product_type_id'] ?? null;
        $product->custom_label = $data['custom_label'] ?? null;
        $product->self_number = $data['self_number'] ?? null;
        $product->raw_material_cost = max(0, (float) ($data['raw_material_cost'] ?? 0));
        $product->transport_cost = max(0, (float) ($data['transport_cost'] ?? 0));
        $product->tax = max(0, (float) ($data['tax'] ?? 0));
        $product->minimum_order_qty = max(1, (int) ($data['minimum_order_qty'] ?? 1));
        $product->discount_type = $discountConfig['type'];
        $product->discount = $discountConfig['value'];
        $product->vat_type = $vatConfig['type'];
        $product->vat_value = $vatConfig['value'];
        $product->qty = $hasVariantRows ? 0 : max(0, (int) ($data['qty'] ?? 0));
        $product->save();

        if ($product->qty > 0 && !$hasVariantRows) {
            $this->handleOpeningStock($product, $product->qty);
        }

        foreach ($variantRows as $row) {
            $productVariant = new ProductVariant();
            $productVariant->product_id = $product->id;
            $productVariant->color_id = $row['color_id'];
            $productVariant->size_id = $row['size_id'];
            $productVariant->qty = $row['qty'];
            $productVariant->name = $this->resolveVariantDisplayName($row['color_id'], $row['size_id']);
            $productVariant->price = $row['price'];
            $productVariant->outlet_price = $row['outlet_price'];
            $productVariant->save();

            if ($productVariant->qty > 0) {
                $this->handleVariantOpeningStock($product, $productVariant);
            }
        }

        return $product;
    }

    public function updateProduct(int $id, array $data, array $variantRows, ?string $imagePath): Product
    {
        $discountConfig = $this->normalizeDiscountInput($data);
        $vatConfig = $this->normalizeVatInput($data);
        $hasVariantRows = !empty($variantRows);
        $product = Product::findOrFail($id);

        if ($imagePath !== null) {
            $product->thumb_image = $imagePath;
        }

        $product->name = $data['name'];
        $product->slug = Str::slug($data['name']);
        $product->category_id = $data['category_id'];
        $product->sub_category_id = $data['sub_category_id'] ?? null;
        $product->child_category_id = $data['child_category_id'] ?? null;
        $product->brand_id = $data['brand_id'] ?? null;
        $product->vendor_id = $data['vendor_id'] ?? null;
        $product->unit_id = $data['unit_id'] ?? null;
        $product->product_number = $data['product_number'] ?? null;
        $product->long_description = $data['long_description'] ?? null;
        $product->purchase_price = $data['purchase_price'] ?? 0;
        $product->price = $data['price'] ?? 0;
        $product->outlet_price = $data['outlet_price'] ?? 0;
        $product->barcode = $data['barcode'] ?? null;
        $product->status = $data['status'] ?? 1;
        $product->product_type = $data['product_type'] ?? null;
        $product->product_type_id = $data['product_type_id'] ?? null;
        $product->custom_label = $data['custom_label'] ?? null;
        $product->self_number = $data['self_number'] ?? null;
        $product->raw_material_cost = max(0, (float) ($data['raw_material_cost'] ?? 0));
        $product->transport_cost = max(0, (float) ($data['transport_cost'] ?? 0));
        $product->tax = max(0, (float) ($data['tax'] ?? 0));
        $product->minimum_order_qty = max(1, (int) ($data['minimum_order_qty'] ?? 1));
        $product->discount_type = $discountConfig['type'];
        $product->discount = $discountConfig['value'];
        $product->vat_type = $vatConfig['type'];
        $product->vat_value = $vatConfig['value'];

        if ($hasVariantRows) {
            $product->qty = 0;
        }

        $product->save();

        if (!$hasVariantRows && isset($data['current_stock'])) {
            $currentDbStock = (float) $product->inventory_stock;
            $submittedStock = (float) $data['current_stock'];
            $adjustment = $submittedStock - $currentDbStock;

            if ($adjustment != 0) {
                $this->handleStockAdjustment($product, $adjustment);
            }
        }

        $keepVariantIds = [];
        foreach ($variantRows as $vData) {
            $variant = null;
            if (isset($vData['id'])) {
                $variant = ProductVariant::where('product_id', $product->id)->find($vData['id']);
            }

            if (!$variant) {
                $variant = new ProductVariant();
                $variant->product_id = $product->id;
            }

            $variant->color_id = $vData['color_id'];
            $variant->size_id = $vData['size_id'];
            $variant->name = $this->resolveVariantDisplayName($vData['color_id'], $vData['size_id']);
            $variant->price = $vData['price'];
            $variant->outlet_price = $vData['outlet_price'];
            $variant->save();

            $keepVariantIds[] = $variant->id;

            $vCurrentDbStock = (float) ($variant->inventory_stock ?? 0);
            $vSubmittedVal = (float) ($vData['current_stock'] ?? 0);
            $vAdjustment = $vSubmittedVal - $vCurrentDbStock;

            if ($vAdjustment != 0) {
                $this->handleVariantStockAdjustment($product, $variant, $vAdjustment);
            }
        }

        ProductVariant::where('product_id', $product->id)->whereNotIn('id', $keepVariantIds)->delete();

        return $product;
    }

    public function previewImport(UploadedFile $file): array
    {
        $originalName = $file->getClientOriginalName();
        $tempName = 'temp_import_' . time() . '_' . $originalName;
        $path = $file->storeAs('temp', $tempName, 'public');
        $fullPath = Storage::disk('public')->path($path);

        $importer = new ProductsImport();
        $preview = $importer->getPreviewData($fullPath, $originalName);

        return [
            'preview' => $preview,
            'temp_path' => $path,
            'original_name' => $originalName,
        ];
    }

    public function storeImport(string $fullPath, string $originalName, ?string $tempPath): array
    {
        if (!file_exists($fullPath)) {
            throw new \Exception('Could not access file');
        }

        $importer = new ProductsImport();
        $results = $importer->import($fullPath, $originalName);

        if ($tempPath) {
            Storage::disk('public')->delete($tempPath);
        }

        return $results;
    }

    public function sendAnnouncement(array $productIds, string $subject, string $message): array
    {
        $validProductIds = Product::query()
            ->whereIn('id', $productIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($validProductIds)) {
            return ['success' => false, 'message' => 'No valid products selected.'];
        }

        $subject = trim($subject);
        $message = trim($message);

        DispatchProductAnnouncementChunksJob::dispatch(
            productIds: $validProductIds,
            source: 'manual',
            actorId: Auth::id() ? (int) Auth::id() : null,
            customSubject: $subject !== '' ? $subject : null,
            customMessage: $message !== '' ? $message : null,
            campaignId: 'manual-' . (string) Str::uuid()
        )->onConnection('database')->onQueue('mail-notifications');

        return [
            'success' => true,
            'message' => 'Announcement queued for ' . count($validProductIds) . ' selected products.',
        ];
    }

    public function extractVariantRows($rawRows, bool $isUpdate): array
    {
        if (!is_array($rawRows)) {
            return [];
        }

        $rows = [];
        $seenPairs = [];

        foreach ($rawRows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $colorId = isset($row['color_id']) && $row['color_id'] !== '' ? (int) $row['color_id'] : null;
            $sizeId = isset($row['size_id']) && $row['size_id'] !== '' ? (int) $row['size_id'] : null;

            if ($colorId === null && $sizeId === null) {
                continue;
            }

            $pairKey = ($colorId ?? 0) . '|' . ($sizeId ?? 0);
            if (isset($seenPairs[$pairKey])) {
                throw ValidationException::withMessages([
                    'variants' => 'Duplicate variant combination found. Please keep each color-size combination unique.',
                ]);
            }
            $seenPairs[$pairKey] = true;

            $prepared = [
                'color_id' => $colorId,
                'size_id' => $sizeId,
                'price' => max(0, (float) ($row['price'] ?? 0)),
                'outlet_price' => max(0, (float) ($row['outlet_price'] ?? 0)),
            ];

            if ($isUpdate) {
                if (!empty($row['id'])) {
                    $prepared['id'] = (int) $row['id'];
                }
                $prepared['current_stock'] = max(0, (float) ($row['current_stock'] ?? 0));
            } else {
                $prepared['qty'] = max(0, (int) ($row['qty'] ?? 0));
            }

            $rows[] = $prepared;
        }

        return $rows;
    }

    public function resolveVariantDisplayName(?int $colorId, ?int $sizeId): string
    {
        $colorName = '';
        $sizeName = '';

        if ($colorId) {
            $colorName = trim((string) optional(Color::find($colorId))->name);
        }
        if ($sizeId) {
            $sizeName = trim((string) optional(Size::find($sizeId))->name);
        }

        $name = trim(implode(' ', array_filter([$colorName, $sizeName])));
        return $name !== '' ? $name : 'Default';
    }

    private function normalizeDiscountInput(array $data): array
    {
        $type = strtolower(trim((string) ($data['discount_type'] ?? '')));
        $value = max(0, (float) ($data['discount'] ?? 0));

        if (!in_array($type, ['flat', 'percent'], true) || $value <= 0) {
            return ['type' => null, 'value' => 0.0];
        }

        if ($type === 'percent' && $value > 100) {
            throw new \InvalidArgumentException('Product discount percent cannot be greater than 100.');
        }

        return ['type' => $type, 'value' => round($value, 2)];
    }

    private function normalizeVatInput(array $data): array
    {
        $type = strtolower(trim((string) ($data['vat_type'] ?? '')));
        $value = max(0, (float) ($data['vat_value'] ?? 0));

        if (!in_array($type, ['flat', 'percent'], true) || $value <= 0) {
            return ['type' => null, 'value' => null];
        }

        if ($type === 'percent' && $value > 100) {
            throw new \InvalidArgumentException('Product VAT percent cannot be greater than 100.');
        }

        return ['type' => $type, 'value' => round($value, 2)];
    }

    public function dispatchProductsPublishedEvent(array $productIds, string $source): void
    {
        $ids = array_values(array_unique(array_map(
            static fn ($id): int => (int) $id,
            array_filter($productIds, static fn ($id): bool => (int) $id > 0)
        )));
        sort($ids);

        if (empty($ids)) {
            return;
        }

        $source = in_array($source, ['created', 'imported'], true) ? $source : 'created';
        $dispatchLockKey = 'product-announcement:dispatch:' . $source . ':' . sha1(json_encode($ids));

        if (!Cache::add($dispatchLockKey, 1, now()->addMinutes(10))) {
            return;
        }

        try {
            event(new ProductsPublished(
                productIds: $ids,
                source: $source,
                actorId: Auth::id() ? (int) Auth::id() : null
            ));
        } catch (\Throwable $e) {
            Log::warning('Unable to dispatch product announcement event', [
                'source' => $source,
                'product_ids_count' => count($ids),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function handleOpeningStock(Product $product, int $qty): void
    {
        $stock = InventoryStock::firstOrCreate([
            'product_id' => $product->id,
            'variant_id' => null,
            'outlet_id' => 1,
        ]);
        $stock->increment('quantity', $qty);

        StockLedger::create([
            'product_id' => $product->id,
            'variant_id' => null,
            'outlet_id' => 1,
            'reference_type' => 'opening',
            'reference_id' => $product->id,
            'in_qty' => $qty,
            'out_qty' => 0,
            'balance_qty' => $stock->quantity,
            'date' => date('Y-m-d'),
        ]);
    }

    private function handleVariantOpeningStock(Product $product, ProductVariant $variant): void
    {
        $stock = InventoryStock::firstOrCreate([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'outlet_id' => 1,
        ]);
        $stock->increment('quantity', $variant->qty);

        StockLedger::create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'outlet_id' => 1,
            'reference_type' => 'opening',
            'reference_id' => $variant->id,
            'in_qty' => $variant->qty,
            'out_qty' => 0,
            'balance_qty' => $stock->quantity,
            'date' => date('Y-m-d'),
        ]);
    }

    private function handleStockAdjustment(Product $product, float $adjustment): void
    {
        $stock = InventoryStock::firstOrCreate([
            'product_id' => $product->id,
            'variant_id' => null,
            'outlet_id' => 1,
        ]);
        $stock->increment('quantity', $adjustment);

        StockLedger::create([
            'product_id' => $product->id,
            'variant_id' => null,
            'outlet_id' => 1,
            'reference_type' => 'adjustment',
            'reference_id' => $product->id,
            'in_qty' => $adjustment > 0 ? $adjustment : 0,
            'out_qty' => $adjustment < 0 ? abs($adjustment) : 0,
            'balance_qty' => $stock->quantity,
            'date' => date('Y-m-d'),
        ]);

        $product->increment('qty', $adjustment);
    }

    private function handleVariantStockAdjustment(Product $product, ProductVariant $variant, float $adjustment): void
    {
        $vStock = InventoryStock::firstOrCreate([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'outlet_id' => 1,
        ]);
        $vStock->increment('quantity', $adjustment);

        StockLedger::create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'outlet_id' => 1,
            'reference_type' => 'adjustment',
            'reference_id' => $variant->id,
            'in_qty' => $adjustment > 0 ? $adjustment : 0,
            'out_qty' => $adjustment < 0 ? abs($adjustment) : 0,
            'balance_qty' => $vStock->quantity,
            'date' => date('Y-m-d'),
        ]);

        $variant->increment('qty', $adjustment);
    }
}
