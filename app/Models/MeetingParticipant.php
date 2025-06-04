<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingParticipant extends Model
{
    protected $table = 'meet.mm_meet_part';
    protected $primaryKey = ['meet_no', 'cat', 'prtcpnt_code'];
    public $incrementing = false;
    public $timestamps = false;
    public function meeting()
    {
        return $this->belongsTo(Meeting::class, ['meet_no', 'cat'], ['meet_no', 'cat']);
    }
}
