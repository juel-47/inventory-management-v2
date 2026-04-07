<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'outlet_id',
        'reference_type',
        'reference_id',
        'in_qty',
        'out_qty',
        'balance_qty',
        'date',
    ];

    protected $casts = [
        'in_qty' => 'decimal:2',
        'out_qty' => 'decimal:2',
        'balance_qty' => 'decimal:2',
        'date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
