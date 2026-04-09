<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'student_id', 'complaint_type', 'subject', 'message', 'status', 'admin_response', 'responded_at'
    ];

    protected $dates = ['responded_at'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
