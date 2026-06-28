<?php

namespace App\Services\Frontend;

use App\Mail\AdminOrderNotificationMail;
use App\Models\Cart;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SavedPurchaseForm;
use App\Services\CheckoutDiscountResolver;
use App\Services\CheckoutTaxResolver;
use App\Services\OrderNumberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutService
{
    public function __construct(
        private CartService $cartService,
        private CheckoutTaxResolver $taxResolver,
        private CheckoutDiscountResolver $discountResolver
    ) {}

    public function getCheckoutData(?int $savedFormId = null): array
    {
        $user = Auth::user();
        $items = $this->cartService->getUserCartItems();
        $summary = $this->calculateCheckoutSummary($items);

        $requestedSavedFormId = $savedFormId ?? 0;
        $resolvedSavedFormId = null;
        if ($requestedSavedFormId > 0) {
            $resolvedSavedFormId = SavedPurchaseForm::query()
                ->where('user_id', (int) $user->id)
                ->whereKey($requestedSavedFormId)
                ->value('id');
            $resolvedSavedFormId = $resolvedSavedFormId ? (int) $resolvedSavedFormId : null;
        }

        $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')));
        $firstName = $nameParts[0] ?? '';
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        return compact('items', 'summary', 'firstName', 'lastName', 'resolvedSavedFormId', 'user');
    }

    public function placeOrder(array $validated, $cartRows): array
    {
        $shipDifferent = (bool) ($validated['ship_different'] ?? false);
        $checkoutItems = $this->cartService->getUserCartItems();
        $summary = $this->calculateCheckoutSummary($checkoutItems);

        $shippingName = trim((($validated['shipping_first_name'] ?? '') . ' ' . ($validated['shipping_last_name'] ?? '')));
        $shippingName = $shippingName !== '' ? $shippingName : null;

        $billingName = $shipDifferent ? ($shippingName ?? ($validated['shipping_first_name'] ?? null)) : ($validated['first_name'] ?? null);
        $billingEmail = $shipDifferent ? ($validated['shipping_email'] ?? null) : ($validated['email'] ?? null);
        $billingPhone = $shipDifferent ? ($validated['shipping_phone'] ?? null) : ($validated['phone'] ?? null);
        $billingAddress = $shipDifferent ? ($validated['shipping_street_address'] ?? null) : ($validated['address'] ?? null);
        $billingOutletName = $shipDifferent ? ($validated['shipping_outlet_name'] ?? null) : ($validated['outlet_name'] ?? null);
        $piEmail = $validated['pi_email'] ?? null;

        DB::beginTransaction();
        try {
            $outletId = $this->cartService->resolveOrderOutletId();
            $requestedLines = $this->buildRequestedLinesFromCart($cartRows);
            $stockRows = $this->lockInventoryForRequestedLines($requestedLines, $outletId);

            foreach ($requestedLines as $key => $line) {
                $available = (int) optional($stockRows->get($key))->quantity;
                $requestedQty = (int) $line['requested_qty'];
                if ($available < $requestedQty) {
                    DB::rollBack();
                    return ['success' => false, 'error' => 'Insufficient stock for ' . $line['name'] . '. Available: ' . $available . ', requested: ' . $requestedQty . '.'];
                }
            }

            $orderNo = $this->generateUniqueOrderNoForUser();

            $order = Order::create([
                'order_no' => $orderNo,
                'user_id' => Auth::id(),
                'status' => 'pending',
                'shipping_method' => 'frontend_checkout',
                'ship_different' => $shipDifferent,
                'billing_name' => $billingName,
                'billing_email' => $billingEmail,
                'billing_phone' => $billingPhone,
                'billing_address' => $billingAddress,
                'billing_outlet_name' => $billingOutletName,
                'pi_email' => $piEmail,
                'shipping_name' => $shipDifferent ? $shippingName : null,
                'shipping_email' => $shipDifferent ? ($validated['shipping_email'] ?? null) : null,
                'shipping_phone' => $shipDifferent ? ($validated['shipping_phone'] ?? null) : null,
                'shipping_address' => $shipDifferent ? ($validated['shipping_street_address'] ?? null) : null,
                'shipping_city' => $shipDifferent ? ($validated['shipping_city'] ?? null) : null,
                'shipping_state' => $shipDifferent ? ($validated['shipping_state'] ?? null) : null,
                'shipping_zip_code' => $shipDifferent ? ($validated['shipping_zip_code'] ?? null) : null,
                'shipping_country' => $shipDifferent ? ($validated['shipping_country'] ?? null) : null,
                'shipping_outlet_name' => $shipDifferent ? ($validated['shipping_outlet_name'] ?? null) : null,
                'subtotal_amount' => $summary['subtotal'],
                'tax_amount' => $summary['tax_amount'],
                'discount_amount' => $summary['discount_amount'],
                'total_amount' => $summary['total'],
                'paid_amount' => 0,
                'due_amount' => $summary['total'],
                'payment_status' => 'pending',
                'tax_label' => $summary['tax_label'],
                'vat_rate' => $summary['vat_rate'],
                'placed_at' => now(),
            ]);

            foreach ($cartRows as $item) {
                $product = $item->product;
                $variant = $item->variant;
                $unitPrice = (float) $this->cartService->resolveCartItemUnitPrice($product, $variant);
                $variantLabel = $this->cartService->resolveVariantLabel($variant);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'vendor_id' => $item->vendor_id ?: $product->vendor_id,
                    'product_name' => $product->name,
                    'category_name' => $product->category->name ?? 'General',
                    'variant_label' => $variantLabel,
                    'product_image' => $product->thumb_image,
                    'unit_price' => $unitPrice,
                    'quantity' => (int) $item->quantity,
                    'line_total' => round($unitPrice * (int) $item->quantity, 2),
                ]);
            }

            Cart::where('user_id', Auth::id())->where('cart_type', 'frontend')->delete();

            $savedFormId = (int) ($validated['saved_form_id'] ?? 0);
            if ($savedFormId > 0) {
                SavedPurchaseForm::query()->where('user_id', (int) Auth::id())->whereKey($savedFormId)->delete();
            }

            DB::commit();

            try {
                Mail::to('tofayelhossaintuhin79@gmail.com')->send(new AdminOrderNotificationMail($order));
            } catch (\Exception $e) {
                Log::error('Failed to send admin order notification: ' . $e->getMessage());
            }

            return ['success' => true, 'order' => $order];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Order placement failed: ' . $e->getMessage()];
        }
    }

    public function calculateCheckoutSummary($items): array
    {
        $subtotal = $items->sum(fn ($item) => ((float) $item['price']) * ((int) $item['quantity']));

        $productsById = Product::query()->whereIn('id', $items->pluck('product_id')->all())->get()->keyBy('id');

        $taxAmount = 0.0;
        $productTaxAmount = 0.0;
        $defaultTaxAmount = 0.0;
        $discountAmount = 0.0;
        $appliedTaxSignatures = [];
        $appliedDiscountSignatures = [];
        $productDiscountAmount = 0.0;
        $userDiscountAmount = 0.0;
        $defaultDiscountAmount = 0.0;
        $productTaxRates = [];
        $defaultTaxRates = [];
        $productDiscountRates = [];
        $userDiscountRates = [];
        $defaultDiscountRates = [];
        $hasDefaultFlatTax = false;
        $defaultFlatTaxValue = 0.0;

        foreach ($items as $item) {
            $lineSubtotal = ((float) $item['price']) * ((int) $item['quantity']);
            $lineQty = max(1, (int) ($item['quantity'] ?? 1));
            $product = $productsById->get((int) $item['product_id']);

            $lineDiscount = $this->discountResolver->resolveForLine($product, $lineSubtotal, $lineQty);
            $lineDiscountAmount = (float) ($lineDiscount['amount'] ?? 0);
            $discountAmount += $lineDiscountAmount;

            foreach ($lineDiscount['discounts'] ?? [] as $subDiscount) {
                $subAmount = (float) ($subDiscount['amount'] ?? 0);
                $source = $subDiscount['source'] ?? 'none';
                $type = $subDiscount['type'] ?? null;
                $value = $subDiscount['value'] ?? 0;
                if ($source === 'product') {
                    $productDiscountAmount += $subAmount;
                    $this->addAppliedRate($productDiscountRates, $type, $value);
                } elseif ($source === 'default') {
                    $defaultDiscountAmount += $subAmount;
                    $this->addAppliedRate($defaultDiscountRates, $type, $value);
                }
                if ($source !== 'none') {
                    $appliedDiscountSignatures[] = ($source . ':' . ($type ?? 'none') . ':' . (string) $value);
                }
            }

            $lineTax = $this->taxResolver->resolveForLine($product, $lineSubtotal);
            $lineTaxAmount = (float) ($lineTax['amount'] ?? 0);
            if ($lineTax['source'] === 'default' && $lineTax['type'] === 'flat') {
                $hasDefaultFlatTax = true;
                $defaultFlatTaxValue = max($defaultFlatTaxValue, (float) $lineTax['value']);
                $this->addAppliedRate($defaultTaxRates, $lineTax['type'] ?? null, $lineTax['value'] ?? 0);
            } else {
                $taxAmount += $lineTaxAmount;
                if (($lineTax['source'] ?? 'none') === 'product') {
                    $productTaxAmount += $lineTaxAmount;
                    $this->addAppliedRate($productTaxRates, $lineTax['type'] ?? null, $lineTax['value'] ?? 0);
                } elseif (($lineTax['source'] ?? 'none') === 'default') {
                    $defaultTaxAmount += $lineTaxAmount;
                    $this->addAppliedRate($defaultTaxRates, $lineTax['type'] ?? null, $lineTax['value'] ?? 0);
                }
            }
            if ($lineTax['source'] !== 'none') {
                $appliedTaxSignatures[] = ($lineTax['source'] . ':' . ($lineTax['type'] ?? 'none') . ':' . (string) $lineTax['value']);
            }
        }

        if ($hasDefaultFlatTax && $items->isNotEmpty()) {
            $taxAmount += $defaultFlatTaxValue;
            $defaultTaxAmount += $defaultFlatTaxValue;
        }

        $taxAmount = round($taxAmount, 2);
        $productTaxAmount = round($productTaxAmount, 2);
        $defaultTaxAmount = round($defaultTaxAmount, 2);
        $productDiscountAmount = round($productDiscountAmount, 2);
        $defaultDiscountAmount = round($defaultDiscountAmount, 2);

        $userDiscountAmount = 0.0;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->discount_type && $user->discount_value > 0) {
                $subtotalAfterProductDiscounts = $subtotal - $productDiscountAmount - $defaultDiscountAmount;
                $canApply = true;
                if ($user->discount_type === 'flat') {
                    $minOrder = (float) ($user->min_order_amount ?? 0);
                    if ($subtotalAfterProductDiscounts < $minOrder) $canApply = false;
                }
                if ($canApply) {
                    if ($user->discount_type === 'percent') {
                        $userDiscountAmount = round(($subtotalAfterProductDiscounts * $user->discount_value) / 100, 2);
                    } else {
                        $userDiscountAmount = round(min($subtotalAfterProductDiscounts, (float) $user->discount_value), 2);
                    }
                    if ($userDiscountAmount > 0) {
                        $this->addAppliedRate($userDiscountRates, $user->discount_type, $user->discount_value);
                        $appliedDiscountSignatures[] = ('user:' . $user->discount_type . ':' . (string) $user->discount_value);
                    }
                }
            }
        }

        $discountAmount = round($productDiscountAmount + $defaultDiscountAmount + $userDiscountAmount, 2);
        $userDiscountAmount = round($userDiscountAmount, 2);
        $taxLabel = 'VAT / Tax';
        $vatRate = null;
        $defaultTax = $this->taxResolver->getDefaultTax();
        $uniqueSignatures = array_values(array_unique($appliedTaxSignatures));
        $isMixedTax = count($uniqueSignatures) > 1;
        $uniqueDiscountSignatures = array_values(array_unique($appliedDiscountSignatures));
        $isMixedDiscount = count($uniqueDiscountSignatures) > 1;

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

        $total = max(0, $subtotal + $taxAmount - $discountAmount);

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => round($total, 2),
            'tax_label' => $taxLabel,
            'vat_rate' => $vatRate,
            'tax_breakdown' => [
                'product_vat' => $productTaxAmount,
                'default_vat' => $defaultTaxAmount,
                'total_vat' => $taxAmount,
                'product_rate_label' => $this->buildAppliedRateLabel(array_values($productTaxRates)),
                'default_rate_label' => $this->buildAppliedRateLabel(array_values($defaultTaxRates)),
                'total_rate_label' => $this->buildCombinedRateLabel(array_values(array_merge($defaultTaxRates, $productTaxRates))),
                'is_mixed' => $isMixedTax,
            ],
            'discount_breakdown' => [
                'product_discount' => $productDiscountAmount,
                'user_discount' => $userDiscountAmount,
                'default_discount' => $defaultDiscountAmount,
                'total_discount' => $discountAmount,
                'product_rate_label' => $this->buildAppliedRateLabel(array_values($productDiscountRates)),
                'user_rate_label' => $this->buildAppliedRateLabel(array_values($userDiscountRates)),
                'default_rate_label' => $this->buildAppliedRateLabel(array_values($defaultDiscountRates)),
                'total_rate_label' => $this->buildCombinedRateLabel(array_values(array_merge($defaultDiscountRates, $productDiscountRates, $userDiscountRates))),
                'is_mixed' => $isMixedDiscount,
            ],
        ];
    }

    private function addAppliedRate(array &$bucket, ?string $type, $value): void
    {
        if (!$type) return;
        $normalizedType = strtolower((string) $type);
        if (!in_array($normalizedType, ['percent', 'flat'], true)) return;
        $normalizedValue = max(0, (float) $value);
        if ($normalizedValue <= 0) return;
        if ($normalizedType === 'percent' && $normalizedValue > 100) $normalizedValue = 100.0;
        $key = $normalizedType . ':' . number_format($normalizedValue, 4, '.', '');
        $bucket[$key] = ['type' => $normalizedType, 'value' => $normalizedValue];
    }

    private function buildAppliedRateLabel(array $rates): ?string
    {
        if (empty($rates)) return null;
        $labels = [];
        foreach ($rates as $rate) {
            $type = strtolower((string) ($rate['type'] ?? ''));
            $value = max(0, (float) ($rate['value'] ?? 0));
            if ($value <= 0) continue;
            $formattedValue = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
            $labels[] = $type === 'percent' ? $formattedValue . '%' : 'Flat ' . $formattedValue;
        }
        $labels = array_values(array_unique($labels));
        sort($labels, SORT_NATURAL);
        return !empty($labels) ? implode(', ', $labels) : null;
    }

    private function buildCombinedRateLabel(array $rates): ?string
    {
        if (empty($rates)) return null;
        $percentTotal = 0.0;
        $flatTotal = 0.0;
        foreach ($rates as $rate) {
            $type = strtolower((string) ($rate['type'] ?? ''));
            $value = max(0, (float) ($rate['value'] ?? 0));
            if ($value <= 0) continue;
            if ($type === 'percent') $percentTotal += $value;
            elseif ($type === 'flat') $flatTotal += $value;
        }
        $parts = [];
        if ($percentTotal > 0) $parts[] = rtrim(rtrim(number_format($percentTotal, 2, '.', ''), '0'), '.') . '%';
        if ($flatTotal > 0) $parts[] = 'Flat ' . rtrim(rtrim(number_format($flatTotal, 2, '.', ''), '0'), '.');
        return !empty($parts) ? implode(' + ', $parts) : null;
    }

    private function buildRequestedLinesFromCart($cartRows): array
    {
        $requestedLines = [];
        foreach ($cartRows as $item) {
            $productId = (int) $item->product_id;
            $variantId = $item->variant_id ? (int) $item->variant_id : null;
            $key = $productId . '|' . ($variantId ?? 0);
            if (!isset($requestedLines[$key])) {
                $requestedLines[$key] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'requested_qty' => 0,
                    'name' => (string) ($item->product?->name ?? ('Product #' . $productId)),
                ];
            }
            $requestedLines[$key]['requested_qty'] += max(1, (int) $item->quantity);
        }
        return $requestedLines;
    }

    private function lockInventoryForRequestedLines(array $requestedLines, int $outletId)
    {
        if (empty($requestedLines)) return collect();
        $query = InventoryStock::query()->where('outlet_id', $outletId)
            ->where(function ($query) use ($requestedLines) {
                foreach ($requestedLines as $line) {
                    $query->orWhere(function ($or) use ($line) {
                        $or->where('product_id', (int) $line['product_id']);
                        if ($line['variant_id'] !== null) $or->where('variant_id', (int) $line['variant_id']);
                        else $or->whereNull('variant_id');
                    });
                }
            })->lockForUpdate();
        return $query->get()->keyBy(fn ($row) => (int) $row->product_id . '|' . ($row->variant_id ? (int) $row->variant_id : 0));
    }

    private function generateUniqueOrderNoForUser(): string
    {
        $user = Auth::user();
        $isOutletUser = $user && ($user->hasRole('Outlet User') || $user->hasRole('Outlet'));
        $prefix = $isOutletUser ? 'DS' : 'ORD';
        return OrderNumberService::generate($prefix, Order::class);
    }
}
