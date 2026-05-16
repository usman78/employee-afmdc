<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $table = 'EMPLOYEE_TASKS';
    protected $primaryKey = 'ID';
    public $incrementing = false;

    protected $fillable = [
        'ID',
        'TITLE',
        'DESCRIPTION',
        'STATUS',
        'PRIORITY',
        'PROGRESS',
        'DUE_DATE',
        'CREATED_BY',
        'ASSIGNED_TO',
        'DEPARTMENT_ID',
        'CLOSED_AT',
        'CLOSED_BY',
    ];

    protected $casts = [
        'due_date' => 'date',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getKey()
    {
        return $this->getAttribute('ID') ?? $this->getAttribute('id');
    }

    public function getRouteKey()
    {
        return $this->getKey();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('ID', $value)->firstOrFail();
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public static function priorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_code');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'emp_code');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'dept_code');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_id', 'id')->latest('created_at');
    }

    public function activities()
    {
        return $this->hasMany(TaskActivity::class, 'task_id', 'id')->latest('created_at');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CLOSED], true);
    }
}
