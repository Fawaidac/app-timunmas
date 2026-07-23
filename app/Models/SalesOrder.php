<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'order_number', 'visit_id', 'customer_id', 'sales_id', 'order_date',
        'payment_type', 'payment_term_days', 'total_amount', 'status'
    ];

    public function visit()
    {
        return $this->belongsTo(SalesVisit::class, 'visit_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id');
    }
}