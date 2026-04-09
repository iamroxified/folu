<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['payment_reference', 'payable_type', 'payable_id', 'amount', 'payment_method', 'payment_date', 'transaction_id', 'payer_name', 'payer_phone', 'description', 'status', 'payment_details'];

    protected $dates = ['payment_date'];

    protected $casts = [
        'payment_details' => 'array',
    ];

    public function payable()
    {
        return $this->morphTo();
    }
}
