<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * This controller provides a placeholder for frontend cart synchronization
     * if we decide to sync with the database in the future.
     * Currently, the cart is handled via LocalStorage in Alpine.js.
     */
    public function index()
    {
        return view('frontend.pages.cart');
    }
}
