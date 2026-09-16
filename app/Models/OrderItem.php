<?php

namespace App\Models;

class OrderItem extends FirebirdModel
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_per_unit',
        'subtotal',
    ];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}