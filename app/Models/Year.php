<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    protected $table = 'mis.si_year';
    public $timestamps = false;
    protected $primaryKey = 'year_id';
    public $incrementing = false;
}
