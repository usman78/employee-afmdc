<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $table = 'pay_desig';
    protected $primaryKey = 'desg_code';
    public $incrementing = false;

    public function employee()
    {
        return $this->hasMany(Employee::class, 'desg_code', 'desg_code');
    }
}
