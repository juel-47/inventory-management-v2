<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_no',
        'user_id',
        'status',
        'shipping_method',
        'ship_different',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_outlet_name',
        'pi_email',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zip_code',
        'shipping_country',
        'shipping_outlet_name',
        'subtotal_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'tax_label',
        'vat_rate',
        'placed_at',
    ];

    protected $casts = [
        'ship_different' => 'boolean',
        'subtotal_amount' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
        'vat_rate' => 'float',
        'placed_at' => 'datetime',
        'pi_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }
}
