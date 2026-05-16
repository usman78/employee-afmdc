<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    protected $table = 'notice_board';

    protected $fillable = [
        'title',
        'content',
        'created_by',
        'is_published',
        'publish_starts_at',
        'publish_ends_at',
        'attachment_path',
        'attachment_name',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'publish_starts_at' => 'datetime',
        'publish_ends_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the employee who created the notice
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by', 'emp_code');
    }

    /**
     * Get the approvals for this notice
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(NoticeApproval::class, 'notice_id');
    }

    /**
     * Check if notice is fully approved
     */
    public function isFullyApproved(): bool
    {
        return $this->approvals()->where('approval_status', 'approved')->count() > 0;
    }
}
