<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
