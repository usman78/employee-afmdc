<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    protected $table = 'sop';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'dept_code');
    }
}
