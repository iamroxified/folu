<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = ['term_name', 'term_number'];

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'current_term_id');
    }
}
