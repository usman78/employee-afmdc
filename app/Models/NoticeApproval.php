<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeApproval extends Model
{
    protected $table = 'notice_board_approval';

    protected $fillable = [
        'notice_id',
        'approver_id',
        'approval_status',
        'remarks',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the notice being approved
     */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'notice_id');
    }

    /**
     * Get the approver (employee)
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id', 'emp_code');
    }
}
