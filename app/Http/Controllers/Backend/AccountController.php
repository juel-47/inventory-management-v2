<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of all transactions (Ledger).
     */
    public function index(\App\DataTables\OrderPaymentDataTable $dataTable)
    {
        return $dataTable->render('backend.accounts.index');
    }

    /**
     * Display orders that have a due balance.
     */
    public function dueOrders(\App\DataTables\DueOrderDataTable $dataTable)
    {
        return $dataTable->render('backend.accounts.due_orders');
    }

    /**
     * Show the manual payment entry page.
     */
    public function create()
    {
        return view('backend.accounts.create');
    }

    /**
     * Search for an order by number for manual payment.
     */
    public function searchOrder(Request $request)
    {
        $request->validate([
            'order_no' => 'required|string',
        ]);

        $order = Order::with('user')->where('order_no', $request->order_no)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found!',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'customer_name' => $order->billing_name,
                'total_amount' => number_format($order->total_amount, 2),
                'paid_amount' => number_format($order->paid_amount, 2),
                'due_amount' => number_format($order->due_amount, 2),
                'due_raw' => $order->due_amount,
                'status' => ucfirst($order->status),
            ]
        ]);
    }

    /**
     * Store a new payment for an order.
     */
    public function storePayment(Request $request, Order $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $amount = (float) $request->amount;

        if ($amount > $order->due_amount) {
            Toastr::error('Payment amount cannot be greater than the due amount!');
            return redirect()->back();
        }

        // Create the payment record
        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'note' => $request->note,
        ]);

        // Update the order totals
        $order->paid_amount += $amount;
        $order->due_amount -= $amount;

        if ($order->due_amount <= 0) {
            $order->payment_status = 'paid';
        } else {
            $order->payment_status = 'partial';
        }

        $order->save();

        Toastr::success('Payment recorded successfully!');

        if ($request->filled('source') && $request->source === 'central_entry') {
            return redirect()->route('admin.accounts.index');
        }

        return redirect()->back();
    }
}
