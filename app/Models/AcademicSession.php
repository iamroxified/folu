<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = ['session_name', 'start_date', 'end_date', 'is_active'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class, 'session_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'current_session_id');
    }
}
