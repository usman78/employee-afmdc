<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueMaster extends Model
{
    protected $table = 'invent.inv_issues';
    protected $primaryKey = 'doc_no';
    public $timestamps = false;
    
    public function issues()
    {
        return $this->hasMany('App\Models\Issue', 'doc_no', 'doc_no');
    }
}
