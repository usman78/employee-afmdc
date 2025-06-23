<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskProgress extends Model
{
    protected $table = 'meet.mm_prog_mont';
    protected $primaryKey = ['meet_no', 'cat', 'task_no', 'resp_prsn'];
    protected $fillable = [
        'meet_no',
        'cat',
        'task_no',
        'resp_prsn',
        'prog_desc',
        'compl_date',
        'status',
        'rprtr_code'
    ];
    public $incrementing = false;
    public $timestamps = false;
    public function tasks()
    {
        $this->belongsTo(Tasks::class);
    }
}
