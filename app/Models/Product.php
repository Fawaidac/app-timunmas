<?php

namespace App\Models;

class Product extends FirebirdModel
{
    protected $fillable = [
        'sku', 'name', 'category', 'price', 'unit'
    ];

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_stocks')
                    ->withPivot('stock_quantity')
                    ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}