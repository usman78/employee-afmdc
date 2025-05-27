<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Issue;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function inventory($emp_code)
    {
        // Check if the logged in user is the same as the user whose inventory is being viewed
        $authUser = Auth::user();
        if($authUser->emp_code != $emp_code){
            return redirect()->route('home');
        }
        // Get inventory for the user
        $inventory = Issue::where('emp_code', $emp_code)->orderBy('doc_date', 'desc')->get();
        $inventory->emp_code = $emp_code;
        return view('inventory', compact('inventory'))->with('emp_code', $emp_code);
    }
}
