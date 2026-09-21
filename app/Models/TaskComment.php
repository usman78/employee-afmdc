<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    protected $table = 'EMPLOYEE_TASK_COMMENTS';
    protected $primaryKey = 'ID';
    public $incrementing = false;

    protected $fillable = [
        'ID',
        'TASK_ID',
        'USER_ID',
        'COMMENT',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'emp_code');
    }
}
