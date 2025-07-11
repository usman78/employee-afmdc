<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestUpdate extends Model
{
    protected $table = 'REQUEST_UPDATES';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID',
        'ASSIGNMENT_ID',
        'UPDATED_BY',
        'COMMENT',
        'PROGRESS_STATUS',
        'UPDATED_AT'
    ];

    public function assignment()
    {
        return $this->belongsTo(RequestAssignment::class, 'ASSIGNMENT_ID');
    }

    public function updater()
    {
        // Optional: Link to a User model if needed
        return $this->belongsTo(User::class, 'UPDATED_BY', 'id');
    }
}
