<?php

namespace App\Models;

class Invoice extends FirebirdModel
{
    protected $fillable = [
        'invoice_number', 'order_id', 'customer_id', 'total_amount',
        'remaining_balance', 'invoice_date', 'due_date', 'status'
    ];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest('id');
    }
}