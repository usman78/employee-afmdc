<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tasks;

class Meeting extends Model
{
    protected $table = 'meet.mm_meet_master';
    protected $primaryKey = ['meet_no', 'cat'];
    public $incrementing = false;
    public $timestamps = false;
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'app_emp', 'emp_code');
    }
    public function chairedBy()
    {
        return $this->belongsTo(Employee::class, 'chair', 'emp_code');
    }
    public function participants()
    {
        return $this->hasMany(MeetingParticipant::class, 'meet_no', 'meet_no');
                    // ->where( 'mm_meet_part.cat',$this->cat );
    }

    public function getParticipantsAttribute()
    {
        return MeetingParticipant::where('meet_no', $this->meet_no)
                                ->where('cat', $this->cat)
                                ->get();
    }

    public function getTasksAttribute()
    {
        return $this->hasMany(Tasks::class, 'meet_no', 'meet_no')
                    ->where('cat', $this->cat);
    }

    public function getResponsibilityAttribute()
    {
        return Responsibility::where('meet_no', $this->meet_no)
                                ->where('cat', $this->cat)
                                // ->where('resp_prsn', $emp_code)
                                ->get();
    }

}
