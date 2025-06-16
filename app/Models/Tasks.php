<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    protected $table = 'meet.mm_meet_task';
    protected $primaryKey = ['meet_no', 'cat', 'task_no'];
    public $incrementing = false;
    public $timestamps = false;
    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meet_no', 'meet_no')
                    ->where('mm_meet_master.cat', $this->cat);
    }
    public function responsibility()
    {
        return $this->hasMany(Responsibility::class, 'meet_no', 'meet_no')
                    ->where('cat', $this->cat)
                    ->where('task_no', $this->task_no);
    }
}
