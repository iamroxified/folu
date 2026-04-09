<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_name',
        'subject_code',
        'description',
        'grade_level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
