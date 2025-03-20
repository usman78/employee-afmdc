<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inv_master';
    public function issue()
    {
        return $this->hasMany('App\Models\Issue', 'item_code', 'item_code');
    }
}
