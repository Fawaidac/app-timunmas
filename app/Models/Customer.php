<?php

namespace App\Models;

class Customer extends FirebirdModel
{
    protected $fillable = [
        'code', 'name', 'address', 'phone', 'email', 'latitude', 'longitude', 'current_debt'
    ];

    public function visits()
    {
        return $this->hasMany(SalesVisit::class);
    }

    public function orders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}