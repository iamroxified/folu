<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'session_id',
        'term_id',
        'base_amount',
        'additional_amount',
        'total_payable',
        'amount_due',
        'amount_paid',
        'amount_owed',
        'balance',
        'due_date',
        'status',
        'academic_year',
        'semester',
        'notes',
        'itemized_snapshot',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'additional_amount' => 'decimal:2',
        'total_payable' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_owed' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
        'itemized_snapshot' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function session()
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function additionalCharges()
    {
        return $this->hasMany(AdditionalCharge::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function recalculateTotals()
    {
        $additionalSum = $this->additionalCharges()->sum('amount');
        $paymentsSum = Payment::where(function($q) {
            $q->where(function($sub) {
                $sub->where('payable_type', 'student_fee')
                    ->where('payable_id', $this->id);
            })->orWhere(function($sub) {
                $sub->where('payable_type', 'App\\Models\\StudentFee')
                    ->where('payable_id', $this->id);
            });
        })->where('status', 'completed')->sum('amount');

        $this->additional_amount = $additionalSum;
        $this->total_payable = $this->base_amount + $this->additional_amount;
        $this->amount_due = $this->total_payable;
        $this->amount_paid = $paymentsSum;
        $this->amount_owed = $this->total_payable - $this->amount_paid;
        $this->balance = $this->amount_owed;

        if ($this->amount_paid <= 0) {
            $this->status = 'unpaid';
        } elseif ($this->amount_paid >= $this->total_payable) {
            $this->status = $this->amount_paid > $this->total_payable ? 'overpaid' : 'paid';
        } else {
            $this->status = 'partially_paid';
        }

        $this->save();
        return $this;
    }
}
