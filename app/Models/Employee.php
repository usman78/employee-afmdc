<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'pay_pers';
    protected $primaryKey = 'emp_code';
    public $incrementing = false;

    public function designation()
    {
        return $this->hasOne(Designation::class, 'desg_code', 'desg_code');
    }
    public function department()
    {
        return $this->hasOne(Department::class, 'dept_code', 'dept_code');
    }
    // public function leavesBalance()
    // {
    //     return $this->hasMany(LeavesBalance::class, 'emp_code', 'emp_code');
    // }
    // public function attendance()
    // {
    //     return $this->hasMany(Attendance::class, 'emp_code', 'emp_code');
    // }
    public function leave()
    {
        return $this->hasMany(Leave::class, 'emp_code', 'emp_code');
    }
}
