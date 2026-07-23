<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'code', 'name', 'address', 'is_active'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'warehouse_stocks')
                    ->withPivot('stock_quantity')
                    ->withTimestamps();
    }
}