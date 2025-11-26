<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionPrograms extends Model
{
    protected $table = 'mis.online_admission_programs';
    public $timestamps = false;
    protected $primaryKey = 'program_id';
    public $incrementing = false;
    public function admissions()
    {
        return $this->hasMany(Admissions::class, 'program_id', 'program_id');
    }
}
