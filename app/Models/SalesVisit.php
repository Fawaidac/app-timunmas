<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesVisit extends Model
{
    protected $fillable = [
        'sales_id', 'customer_id', 'visit_date', 'purpose', 'status',
        'checkin_time', 'checkin_latitude', 'checkin_longitude', 'distance_meters', 'notes'
    ];

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->hasOne(SalesOrder::class, 'visit_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'visit_id');
    }
}