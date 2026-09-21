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
    public function program()
    {
        return $this->belongsTo(AdmissionPrograms::class, 'program_id', 'program_id');
    }
    public function getAccomodationLabelAttribute()
    {
        return $this->accommodation === 'y' ? 'Yes' : 'No';
    }
    public function getGenderLabelAttribute()
    {
        return $this->gender === 'f' ? 'Female' : 'Male';
    }
    public function detail()
    {
        return $this->hasMany(AdmissionDetail::class, 'adm_applicant_id', 'adm_applicant_id');
    }
}
