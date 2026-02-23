<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomProductRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no',
        'user_id',
        'product_name',
        'product_description',
        'example_image',
        'quantity_needed',
        'expected_price',
        'status',
        'admin_note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status badge class for display
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'badge-warning';
            case 'approved':
                return 'badge-success';
            case 'rejected':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get the status label for display
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }
}
