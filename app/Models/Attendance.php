<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'daily_attnd';
    public function user()
    {
        return $this->belongsTo(User::class, 'emp_code', 'emp_code');
    }
}
