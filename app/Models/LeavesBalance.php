<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeavesBalance extends Model
{
    protected $table = 'pay_leave_bal';
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_code', 'emp_code');
    }
}
