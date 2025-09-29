<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admissions extends Model
{
    protected $table = 'MIS.ONLINE_ADMISSION_MST';
    public $timestamps = false;
    protected $primaryKey = 'ADM_APPLICANT_ID';
    public $incrementing = false;
    public function user()
    {
        return $this->belongsTo(AdmissionUsers::class, 'user_id', 'id');
    }
}
