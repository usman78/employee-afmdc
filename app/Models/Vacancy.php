<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $table = 'pay_create_vacancy';
    protected $primaryKey = 'job_id';
    public function jobs(){
        return $this->hasMany(Job::class, 'job_id', 'job_id');
    }
}
