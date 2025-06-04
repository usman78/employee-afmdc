<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $table = 'meet.mm_meet_master';
    protected $primaryKey = ['meet_no', 'cat'];
    public $incrementing = false;
    public $timestamps = false;
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'app_emp', 'emp_code');
    }
    public function chairedBy()
    {
        return $this->belongsTo(Employee::class, 'chair', 'emp_code');
    }
}
