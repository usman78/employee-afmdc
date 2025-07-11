<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestApproval extends Model
{
    protected $table = 'REQUEST_APPROVALS';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID', 'SERVICE_REQUEST_ID', 'APPROVED_BY', 'ROLE',
        'REMARKS', 'APPROVAL_DATE', 'STATUS'
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'SERVICE_REQUEST_ID');
    }
}
