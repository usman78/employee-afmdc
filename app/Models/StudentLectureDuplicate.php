<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLectureDuplicate extends Model
{
    protected $table = 'mis.si_time_table_d_exp';
    public $timestamps = false;
    protected $primaryKey = null;  
    public $incrementing = false;
    public function timetable()
    {
        return $this->belongsTo(TimetableDuplicate::class, 'fk_doc_id', 'doc_id');
    }
}
