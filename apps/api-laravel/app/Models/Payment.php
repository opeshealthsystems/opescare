<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invoice_id',
        'patient_id',
        'facility_id',
        'cashier_id',
        'cashier_session_id',
        'wallet_id',
        'payment_reference',
        'method',
        'status',
        'amount',
        'refunded_amount',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
