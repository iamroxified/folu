<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenditure extends Model
{
    use HasFactory;

    protected $table = 'expenditures';

    protected $fillable = [
        'expenditure_number',
        'title',
        'category',
        'amount',
        'expenditure_date',
        'vendor_recipient',
        'payment_method',
        'status',
        'description',
        'receipt_path',
        'recorded_by',
    ];

    protected $casts = [
        'expenditure_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
