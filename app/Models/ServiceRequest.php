<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ServiceRequest extends Model
{
    protected $table = 'SERVICE_REQUESTS';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID', 'REQUESTER_ID', 'DEPARTMENT_ID', 'JOB_TYPE',
        'DESCRIPTION', 'PRIORITY', 'STATUS', 'CREATED_AT', 'UPDATED_AT'
    ];

    public function getJobTypeLabelAttribute(): string
    {
        return match ($this->job_type) {
            '1' => 'Data Updation / Correction',
            '2' => 'User Creation / Password reset',
            '3' => 'New System / Sub system development',
            '4' => 'Modification in existing data entry Form',
            '5' => 'Modification in existing Report',
            '6' => 'New data entry Form Development',
            '7' => 'New Report Development',
            '8' => 'New Email Account',
            '9' => 'Special Web Site Permission',
            '10' => 'Windows Installation',
            '11' => 'Other Software Service',
            '12' => 'Internet Access (Mobile)',
            '13' => 'Projector Deployment',
            '14' => 'Internet Access For Office',
            '15' => 'Printer Services / Installation',
            '16' => 'Windows Installation',
            '17' => 'Sharing & Mapping',
            '18' => 'Wireless Access Point Deployment',
            '19' => 'Hardware Maintenance',
            '20' => 'Other Hardware Service',
            default => 'Other Service',
        };
    }


    public function approvals()
    {
        return $this->hasMany(RequestApproval::class, 'service_request_id');
    }

    public function assignment()
    {
        return $this->hasOne(RequestAssignment::class, 'service_request_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'dept_code');
    }
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id', 'emp_code');
    }
}
