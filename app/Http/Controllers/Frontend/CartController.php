<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Cart\FrontendCartAddRequest;
use App\Http\Requests\Frontend\Cart\FrontendCartRemoveRequest;
use App\Http\Requests\Frontend\Cart\FrontendCartUpdateQtyRequest;
use App\Http\Requests\Frontend\Cart\CheckoutPlaceOrderRequest;
use App\Models\Cart;
use App\Services\Frontend\CartService;
use App\Services\Frontend\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CheckoutService $checkoutService
    ) {}

    public function index()
    {
        return view('frontend.pages.cart');
    }

    public function checkout(Request $request)
    {
        $savedFormId = (int) $request->query('saved_form', 0);
        $data = $this->checkoutService->getCheckoutData($savedFormId > 0 ? $savedFormId : null);

        return view('frontend.pages.checkout', [
            'cartItems' => $data['items'],
            'subtotal' => $data['summary']['subtotal'],
            'vatRate' => $data['summary']['vat_rate'],
            'vatAmount' => $data['summary']['tax_amount'],
            'taxLabel' => $data['summary']['tax_label'],
            'taxBreakdown' => $data['summary']['tax_breakdown'],
            'discountBreakdown' => $data['summary']['discount_breakdown'],
            'discountAmount' => $data['summary']['discount_amount'],
            'total' => $data['summary']['total'],
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'savedFormId' => $data['resolvedSavedFormId'],
            'user' => $data['user'],
        ]);
    }

    public function items()
    {
        $items = $this->cartService->getUserCartItems();

        return response()->json([
            'items' => $items,
            'count' => $items->count(),
        ]);
    }

    public function add(FrontendCartAddRequest $request)
    {
        $result = $this->cartService->add($request->validated());

        if (($result['code'] ?? null) === 422) {
            unset($result['code']);
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function remove(FrontendCartRemoveRequest $request)
    {
        $result = $this->cartService->remove($request->validated());

        if (($result['code'] ?? null) === 422) {
            unset($result['code']);
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function updateQuantity(FrontendCartUpdateQtyRequest $request)
    {
        $result = $this->cartService->updateQuantity($request->validated());

        if (($result['code'] ?? null) === 422) {
            unset($result['code']);
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    public function clear()
    {
        return response()->json($this->cartService->clear());
    }

    public function placeOrder(CheckoutPlaceOrderRequest $request)
    {
        $validated = $request->validated();

        $cartRows = Cart::where('user_id', Auth::id())
            ->where('cart_type', 'frontend')
            ->with(['product.category', 'variant'])
            ->get()
            ->filter(fn ($item) => $item->product && (int) ($item->product->status ?? 0) === 1)
            ->values();

        if ($cartRows->isEmpty()) {
            return redirect()->route('checkout.index')->with('error', 'Your cart is empty.');
        }

        $result = $this->checkoutService->placeOrder($validated, $cartRows);

        if (!$result['success']) {
            return redirect()
                ->route('checkout.index')
                ->with('error', $result['error'])
                ->withInput();
        }

        $order = $result['order'];

        return redirect()
            ->route('orders.show', $order->id)
            ->with('success', 'Order placed successfully. Reference: ' . $order->order_no);
    }
}
