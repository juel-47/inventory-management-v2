<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'vendor_id',
        'user_id',
        'date',
        'total_amount',
        'note',
        'status',
        'shipping_method',
        'booking_id',
        'material_cost',
        'transport_cost',
        'tax',
        'invoice_attachment',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
        'material_cost' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function attachments()
    {
        return $this->hasMany(PurchaseAttachment::class)->latest();
    }
}
