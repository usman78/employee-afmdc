<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeavesController extends Controller
{
    public function leaves()
    {
        return view('leaves');
    }
}
