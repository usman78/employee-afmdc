<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskActivity extends Model
{
    protected $table = 'EMPLOYEE_TASK_ACTIVITIES';
    protected $primaryKey = 'ID';
    public $incrementing = false;

    protected $fillable = [
        'ID',
        'TASK_ID',
        'ACTOR_ID',
        'ACTION',
        'FROM_STATUS',
        'TO_STATUS',
        'DESCRIPTION',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'emp_code');
    }
}
