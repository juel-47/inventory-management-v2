<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
