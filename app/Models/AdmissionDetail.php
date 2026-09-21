<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionDetail extends Model
{
    protected $table = 'mis.online_admission_dtl';
    public $timestamps = false;
    protected $primaryKey = 'dtl_id';
    public $incrementing = false;
    public function admission()
    {
        return $this->belongsTo(Admissions::class, 'adm_applicant_id', 'adm_applicant_id');
    }
}
