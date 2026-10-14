<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $table = 'receipts';

    protected $fillable = [
        'receipt_number',
        'payment_id',
        'student_id',
        'amount_paid',
        'issued_date',
        'issued_by',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'issued_date' => 'date',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
