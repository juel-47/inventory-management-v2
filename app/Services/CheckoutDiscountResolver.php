<?php

namespace App\Services;

use App\Models\Discount;
use Illuminate\Support\Facades\Schema;

class CheckoutDiscountResolver
{
    private ?Discount $defaultDiscount = null;
    private bool $defaultDiscountLoaded = false;

    public function resolveForLine($product, float $lineSubtotal, int $quantity = 1): array
    {
        $lineSubtotal = max(0, $lineSubtotal);
        $quantity = max(1, $quantity);
        $productDiscount = $this->resolveProductDiscount($product);

        if ($productDiscount !== null) {
            return [
                'source' => 'product',
                'type' => $productDiscount['type'],
                'value' => $productDiscount['value'],
                'amount' => $this->calculateAmount(
                    $lineSubtotal,
                    $productDiscount['type'],
                    $productDiscount['value'],
                    $quantity,
                    true
                ),
            ];
        }

        $defaultDiscount = $this->getDefaultDiscount();
        if ($defaultDiscount) {
            $type = $defaultDiscount->type === 'flat' ? 'flat' : 'percent';
            $value = max(0, (float) $defaultDiscount->value);

            return [
                'source' => 'default',
                'type' => $type,
                'value' => $value,
                'amount' => $this->calculateAmount(
                    $lineSubtotal,
                    $type,
                    $value,
                    $quantity,
                    true
                ),
            ];
        }

        return [
            'source' => 'none',
            'type' => null,
            'value' => 0.0,
            'amount' => 0.0,
        ];
    }

    public function getDefaultDiscount(): ?Discount
    {
        if ($this->defaultDiscountLoaded) {
            return $this->defaultDiscount;
        }

        if (!Schema::hasTable('discounts')) {
            $this->defaultDiscountLoaded = true;
            $this->defaultDiscount = null;
            return null;
        }

        $this->defaultDiscount = Discount::query()
            ->where('is_default', true)
            ->where('status', true)
            ->first();

        $this->defaultDiscountLoaded = true;
        return $this->defaultDiscount;
    }

    private function resolveProductDiscount($product): ?array
    {
        if (!$product) {
            return null;
        }

        $type = strtolower(trim((string) data_get($product, 'discount_type', '')));
        $value = (float) data_get($product, 'discount', 0);

        // Backward compatibility: if type is missing but value exists, treat as percent.
        if ($type === '' && $value > 0) {
            $type = 'percent';
        }

        if (!in_array($type, ['flat', 'percent'], true)) {
            return null;
        }

        if ($value <= 0) {
            return null;
        }

        if ($type === 'percent' && $value > 100) {
            $value = 100.0;
        }

        return [
            'type' => $type,
            'value' => $value,
        ];
    }

    private function calculateAmount(
        float $lineSubtotal,
        string $type,
        float $value,
        int $quantity = 1,
        bool $flatPerUnit = false
    ): float
    {
        if ($lineSubtotal <= 0 || $value <= 0) {
            return 0.0;
        }

        if ($type === 'flat') {
            if ($flatPerUnit) {
                return round(min($lineSubtotal, $value * max(1, $quantity)), 2);
            }

            return round(min($lineSubtotal, $value), 2);
        }

        return round(($lineSubtotal * $value) / 100, 2);
    }
}
