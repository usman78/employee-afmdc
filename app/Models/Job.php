<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'online_job_mst';
    protected $primaryKey = 'app_no';

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'position_id', 'desg_code');
    }
    public function vacancy(){
        return $this->belongsTo(Vacancy::class, 'job_id' , 'job_id');
    }
}
