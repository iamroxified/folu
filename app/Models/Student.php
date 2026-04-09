<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'enrollment_date',
        'status',
        'admission_status',
        'category',
        'passport',
        'current_class_id',
        'current_session_id',
        'current_term_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
    ];

    public function currentClass()
    {
        return $this->belongsTo(SchoolClass::class, 'current_class_id');
    }

    public function currentSession()
    {
        return $this->belongsTo(AcademicSession::class, 'current_session_id');
    }

    public function currentTerm()
    {
        return $this->belongsTo(Term::class, 'current_term_id');
    }

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function grades()
    {
        return $this->hasMany(StudentGrade::class);
    }

    public function feeAssignments()
    {
        return $this->hasMany(StudentFeeAssignment::class);
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
}
