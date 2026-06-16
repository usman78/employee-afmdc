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
        
        // Get unacknowledged items
        $unacknowledged = Issue::where('emp_code', $emp_code)
            ->where(function($query) {
                $query->whereNull('ackn_by_user')
                      ->orWhere('ackn_by_user', 'N');
            })
            ->orderBy('doc_date', 'desc')
            ->get();
        
        // Get acknowledged items
        $acknowledged = Issue::where('emp_code', $emp_code)
            ->where('ackn_by_user', 'Y')
            ->orderBy('dated', 'desc')
            ->get();
        
        return view('inventory', compact('unacknowledged', 'acknowledged'));
    }
    
    public function acknowledgeItem(Request $request, $doc_no)
    {
        // Validate request
        $request->validate([
            'remarks' => 'nullable|string|max:255'
        ]);
        
        $authUser = Auth::user();
        
        // Find the issue item
        $issue = Issue::where('doc_no', $doc_no)->first();
        
        // Verify the item belongs to the logged-in user
        if($issue->emp_code != $authUser->emp_code){
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Update the issue with acknowledgment
        $issue->update([
            'ackn_by_user' => 'Y',
            'dated' => now(),
            'remarks' => $request->input('remarks', '')
        ]);
        
        return response()->json(['success' => 'Item acknowledged successfully', 'message' => 'Item has been acknowledged']);
    }
}
