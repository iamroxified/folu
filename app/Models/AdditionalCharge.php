<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'student_fee_id',
        'session_id',
        'term_id',
        'fee_name',
        'amount',
        'amount_paid',
        'balance',
        'reason',
        'added_by',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function recordPayment(float $paymentAmount): void
    {
        $this->amount_paid = (float) $this->amount_paid + $paymentAmount;
        $this->balance = max(0.00, (float) $this->amount - (float) $this->amount_paid);

        if ($this->balance <= 0.001) {
            $this->status = 'paid';
            $this->balance = 0.00;
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partially_paid';
        } else {
            $this->status = 'unpaid';
        }

        $this->save();

        if ($this->studentFee) {
            $this->studentFee->recalculateTotals();
        }
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentFee()
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function session()
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
