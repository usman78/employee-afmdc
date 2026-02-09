<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExitInterview extends Model
{
    protected $table = 'exit_interviews';
    protected $casts = [
        'reasons' => 'array',
        'ro_ratings' => 'array',
        'company_ratings' => 'array',
        'share_with_ro' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'emp_code');
    }
}
