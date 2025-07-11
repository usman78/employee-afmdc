<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestAssignment extends Model
{
    protected $table = 'REQUEST_ASSIGNMENTS';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID',
        'SERVICE_REQUEST_ID',
        'ASSIGNED_BY',
        'ASSIGNED_TO',
        'REMARKS',
        'ASSIGNED_AT',
        'EXPECTED_COMPLETION_DATE'
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'SERVICE_REQUEST_ID');
    }

    public function updates()
    {
        return $this->hasMany(RequestUpdate::class, 'ASSIGNMENT_ID');
    }

    public function assignee()
    {
        // Optional: Link to a User model if you have one
        return $this->belongsTo(User::class, 'ASSIGNED_TO', 'id');
    }
}
