<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeApplication extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_HOD_APPROVED = 'HOD_approved';
    public const STATUS_HOD_REJECTED = 'HOD_rejected';
    public const STATUS_HR_APPROVED = 'HR approved';
    public const STATUS_HR_REJECTED = 'HR rejected';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_FINANCE_REJECTED = 'Finance rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'OVERTIME_APPLICATIONS';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'emp_code',
        'salary_month',
        'overtime_date',
        'gross_salary',
        'overtime_minutes',
        'hourly_rate',
        'calculated_amount',
        'sanctioned_minutes',
        'sanctioned_amount',
        'shift_start',
        'shift_end',
        'time_in',
        'time_out',
        'is_holiday',
        'is_security',
        'remarks',
        'status',
        'hod_approved_by',
        'hod_approved_at',
        'hod_remarks',
        'hr_approved_by',
        'hr_approved_at',
        'hr_remarks',
        'finance_approved_by',
        'finance_approved_at',
        'finance_remarks',
        'applied_at',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'gross_salary' => 'decimal:2',
        'overtime_minutes' => 'integer',
        'hourly_rate' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
        'sanctioned_minutes' => 'integer',
        'sanctioned_amount' => 'decimal:2',
        'shift_start' => 'datetime',
        'shift_end' => 'datetime',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'is_holiday' => 'boolean',
        'is_security' => 'boolean',
        'hod_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'finance_approved_at' => 'datetime',
        'applied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_HOD_APPROVED,
            self::STATUS_HR_APPROVED,
            self::STATUS_APPROVED,
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_code', 'emp_code');
    }

    public function hodApprover()
    {
        return $this->belongsTo(User::class, 'hod_approved_by', 'emp_code');
    }

    public function hrApprover()
    {
        return $this->belongsTo(User::class, 'hr_approved_by', 'emp_code');
    }

    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_approved_by', 'emp_code');
    }
}
