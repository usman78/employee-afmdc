<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeptInventory extends Model
{
    protected $table = 'invent.depts';
    protected $primaryKey = 'dept_code';
    public $incrementing = false;
}
