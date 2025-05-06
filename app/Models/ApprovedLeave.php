<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovedLeave extends Model
{
    protected $table = 'pay_leaves_tran';
    public $timestamps = false;
    protected $primaryKey = 'pre_leave_id';
    public $incrementing = false;
    public function leave()
    {
        return $this->belongsTo(Leave::class, 'leave_id');
    }
}
