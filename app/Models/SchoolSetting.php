<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'school_logo',
        'school_address',
        'school_phone',
        'school_email',
        'school_motto',
        'currency',
        'timezone',
        'is_installed',
        'additional_settings',
    ];

    protected $casts = [
        'additional_settings' => 'array',
        'is_installed' => 'boolean',
    ];
}
