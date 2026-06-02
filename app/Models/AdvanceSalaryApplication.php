<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceSalaryApplication extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_HOD_APPROVED = 'HOD_approved';
    public const STATUS_HOD_REJECTED = 'HOD_rejected';
    public const STATUS_HR_APPROVED = 'HR approved';
    public const STATUS_HR_REJECTED = 'HR rejected';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ACCOUNTS_REJECTED = 'Accounts rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'ADVANCE_SALARY_APPLICATIONS';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'emp_code',
        'salary_month',
        'gross_salary',
        'max_amount',
        'requested_amount',
        'sanctioned_amount',
        'eligible_days',
        'reason',
        'status',
        'hod_approved_by',
        'hod_approved_at',
        'hod_remarks',
        'hr_approved_by',
        'hr_approved_at',
        'hr_remarks',
        'accounts_approved_by',
        'accounts_approved_at',
        'accounts_remarks',
        'applied_at',
    ];

    protected $casts = [
        'gross_salary' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'requested_amount' => 'decimal:2',
        'sanctioned_amount' => 'decimal:2',
        'eligible_days' => 'integer',
        'hod_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'accounts_approved_at' => 'datetime',
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

    public function accountsApprover()
    {
        return $this->belongsTo(User::class, 'accounts_approved_by', 'emp_code');
    }
}
