<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'staff_id',
        'staff_type',
        'class_subject',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'deductions',
        'bonuses',
        'overtime_pay',
        'gross_pay',
        'net_pay',
        'pay_period_start',
        'pay_period_end',
        'pay_date',
        'status',
        'payment_method',
        'payment_reference',
        'remarks',
    ];

    protected $casts = [
        'pay_date' => 'date',
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function getFormattedStaffTypeAttribute(): string
    {
        return $this->staff_type === 'part_time' ? 'Part Time' : 'Full Time';
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'Paid',
            'not_paid', 'unpaid' => 'Not Paid',
            'pending' => 'Pending',
            'processed' => 'Processed',
            default => ucfirst($this->status),
        };
    }

    public function getPayPeriodFormattedAttribute(): string
    {
        if ($this->month && $this->year) {
            return "{$this->month} {$this->year}";
        } elseif ($this->month) {
            return $this->month;
        } elseif ($this->pay_date) {
            return $this->pay_date->format('F Y');
        }
        return 'N/A';
    }
}
