<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAuth extends Model
{
    protected $table = 'pre_leave_auth';
    public $timestamps = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_code_l', 'emp_code');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'emp_code', 'emp_code_l');
    }
}
