<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'staff_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'position',
        'department',
        'hire_date',
        'salary',
        'status',
        'staff_type',
        'assigned_class_id',
        'assigned_subject_id',
        'class_or_subject_custom',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'date_of_birth' => 'date',
        'salary' => 'decimal:2',
    ];

    public function assignedClass()
    {
        return $this->belongsTo(SchoolClass::class, 'assigned_class_id');
    }

    public function assignedSubject()
    {
        return $this->belongsTo(Subject::class, 'assigned_subject_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'staff_id');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getClassOrSubjectAttribute(): string
    {
        if (!empty($this->class_or_subject_custom)) {
            return $this->class_or_subject_custom;
        }

        $parts = [];
        if ($this->assignedClass) {
            $parts[] = 'Class: ' . $this->assignedClass->class_name;
        }
        if ($this->assignedSubject) {
            $parts[] = 'Subject: ' . $this->assignedSubject->subject_name;
        }

        if (!empty($parts)) {
            return implode(' | ', $parts);
        }

        return $this->position ?? 'N/A';
    }

    public function getFormattedStaffTypeAttribute(): string
    {
        return $this->staff_type === 'part_time' ? 'Part Time' : 'Full Time';
    }
}
