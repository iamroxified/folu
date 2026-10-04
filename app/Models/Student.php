<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_link',
        'admission_no',
        'student_number',
        'first_name',
        'last_name',
        'other_names',
        'email',
        'phone',
        'address',
        'home_address',
        'date_of_birth',
        'gender',
        'enrollment_date',
        'admission_date',
        'status',
        'admission_status',
        'category',
        'passport',
        'state_of_origin',
        'lga',
        'student_type',
        'blood_group',
        'genotype',
        'current_class_id',
        'current_session_id',
        'current_term_id',
        'class_link',
        'academic_session_link',
        'term_link',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'admission_date' => 'date',
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

    public function additionalCharges()
    {
        return $this->hasMany(AdditionalCharge::class);
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
