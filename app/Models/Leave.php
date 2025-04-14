<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $table = 'pre_leave_tran';
    protected $primaryKey = 'leave_id';
    public $timestamps = false;
    public $incrementing = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_code', 'emp_code');
    }
}
