<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    protected $table = 'mis.si_time_table_d';
    protected $primaryKey = 'pkey';
    public $timestamps = false;
    public function timetable()
    {
        return $this->belongsTo(Timetable::class, 'fk_doc_id', 'doc_id');
    }
}
