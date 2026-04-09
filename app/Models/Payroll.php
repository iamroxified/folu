<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = ['staff_id', 'amount', 'pay_date', 'month', 'year'];

    protected $dates = ['pay_date'];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
