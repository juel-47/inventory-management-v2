<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductPriceSupport
{
    public static function resolveWholesalePrice(Product $product, ?ProductVariant $variant = null): float
    {
        return self::firstPositive([
            $variant?->outlet_price,
            $product->outlet_price,
            $variant?->price,
            $product->price,
        ]);
    }

    public static function resolveCustomerPrice(Product $product, ?ProductVariant $variant = null): float
    {
        return self::firstPositive([
            $variant?->price,
            $product->price,
            $variant?->outlet_price,
            $product->outlet_price,
        ]);
    }

    public static function resolveRoleUnitPrice(Product $product, ?ProductVariant $variant, $user): float
    {
        $usesWholesalePrice = $user
            && method_exists($user, 'hasRole')
            && ($user->hasRole('Outlet User') || $user->hasRole('User'));

        return $usesWholesalePrice
            ? self::resolveWholesalePrice($product, $variant)
            : self::resolveCustomerPrice($product, $variant);
    }

    private static function firstPositive(array $candidates): float
    {
        foreach ($candidates as $candidate) {
            $value = (float) ($candidate ?? 0);
            if ($value > 0) {
                return $value;
            }
        }

        return 0.0;
    }
}
