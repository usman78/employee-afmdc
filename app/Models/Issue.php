<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $table = 'invent.inv_issue_sub';
    public function inventory()
    {
        return $this->belongsTo('App\Models\Inventory', 'item_code', 'item_code');
    }
}
