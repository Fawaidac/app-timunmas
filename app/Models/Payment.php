<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_number', 'visit_id', 'invoice_id', 'sales_id', 'customer_id',
        'payment_method', 'amount_paid', 'reference_number', 'proof_image_url',
        'status', 'rejection_reason', 'approved_by', 'approved_at'
    ];

    public function visit()
    {
        return $this->belongsTo(SalesVisit::class, 'visit_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}