<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $table = 'invent.inv_issue_sub';
    protected $primaryKey = 'doc_no';
    public $timestamps = false;
    protected $fillable = ['emp_code', 'item_code', 'qty', 'rate', 'value', 'doc_date', 'ackn_by_user', 'dated', 'remarks'];
    
    public function inventory()
    {
        return $this->belongsTo('App\Models\Inventory', 'item_code', 'item_code');
    }
    
    public function issueMaster()
    {
        return $this->belongsTo('App\Models\IssueMaster', 'doc_no', 'doc_no');
    }
}
