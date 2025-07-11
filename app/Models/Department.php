<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'pay_dept';
    protected $primaryKey = 'dept_code';
    public $incrementing = false;
    public function sop()
    {
        return $this->hasMany(Sop::class, 'department_id', 'dept_code');
    }
}
