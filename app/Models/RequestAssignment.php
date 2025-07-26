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
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function updates()
    {
        return $this->hasMany(RequestUpdate::class, 'assignment_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'emp_code');
    }

    public function approvals()
    {
        return $this->hasMany(RequestApproval::class, 'service_request_id', 'service_request_id');
    }
}
