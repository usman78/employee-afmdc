<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holidays extends Model
{
    protected $table = 'holidays';

    public $timestamps = false;

    protected $fillable = [
        'h_date',
        'holiday_desc',
    ];

    protected $casts = [
        'h_date' => 'date',
    ];
}
