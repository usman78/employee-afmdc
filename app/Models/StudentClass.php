<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    protected $table = 'mis.si_class';
    public $timestamps = false;
    protected $primaryKey = 'class_id';
    public $incrementing = false;
}
