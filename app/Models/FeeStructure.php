<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'grade_level',
        'class_name',
        'amount',
        'frequency',
        'fee_type',
        'effective_from',
        'effective_to',
        'is_mandatory',
        'is_active',
        'additional_details',
        'category',
        'gender',
        'session_id',
        'term_id',
        'class_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'additional_details' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function assignments()
    {
        return $this->hasMany(StudentFeeAssignment::class);
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }
}
