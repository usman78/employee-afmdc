<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpInventory extends Model
{
    protected $table = 'invent.inv_employee';
    protected $primaryKey = 'emp_code';
    public $incrementing = false;
}
