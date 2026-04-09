<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    protected $fillable = ['student_id', 'fee_structure_id', 'amount_due', 'amount_paid', 'balance', 'due_date', 'status', 'academic_year', 'semester', 'notes'];

    protected $dates = ['due_date'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
