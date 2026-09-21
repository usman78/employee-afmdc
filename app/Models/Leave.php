<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    public const CASUAL = 1;
    public const MEDICAL = 2;
    public const ANNUAL = 3;
    public const CPL = 4;
    public const WITHOUT_PAY = 5;
    public const SHORT = 8;
    public const OD = 12;

    public const BALANCE_BACKED_TYPES = [
        self::CASUAL,
        self::MEDICAL,
        self::ANNUAL,
        self::CPL,
    ];

    protected $table = 'pre_leave_tran';
    protected $primaryKey = 'leave_id';
    public $timestamps = false;
    public $incrementing = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_code', 'emp_code');
    }
    public function approvedLeave()
    {
        return $this->hasOne(ApprovedLeave::class, 'pre_leave_id');
    }
}
