<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $table = 'mis.si_time_table';
    protected $primaryKey = 'doc_id';
    public $timestamps = false;
    protected $fillable = [
        'doc_id',
        'subject_id',
        'year_id',
        'class_id',
        'group_title',
        'p_day',
        'emp_code',
        'period_type',
        'start_time',
        'end_time',
        'datedm',
        'user_id1',
        'timestamp_id1',
        'terminal_id1',
    ];
    public function lectures()
    {
        return $this->hasMany(Lecture::class, 'fk_doc_id', 'doc_id');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }
}
