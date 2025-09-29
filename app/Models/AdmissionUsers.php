<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionUsers extends Model
{
    protected $table = 'mis.online_admission_users';
    public $timestamps = false;
    public $incrementing = false;
    public function admission()
    {
        return $this->belongsTo(Admissions::class, 'id', 'user_id');
    }
}
