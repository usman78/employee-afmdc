<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responsibility extends Model
{
    protected $table = 'meet.mm_meet_resp';
    protected $primaryKey = ['meet_no', 'cat', 'task_no', 'resp_prsn'];
    public $incrementing = false;
    public $timestamps = false;
    public function tasks()
    {
        return $this->belongsTo(Tasks::class, 'meet_no', 'meet_no')
                    ->where('cat', $this->cat)
                    ->where('task_no', $this->task_no);
    }
}
