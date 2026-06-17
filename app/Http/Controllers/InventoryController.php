<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Issue;
use App\Models\Inventory;
use App\Models\IssueMaster;
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
            ->with('inventory')
            ->orderBy('doc_date', 'desc')
            ->get();
        
        // Get acknowledged items (regular issues)
        $acknowledged = Issue::where('emp_code', $emp_code)
            ->where('ackn_by_user', 'Y')
            ->with('inventory')
            ->orderBy('dated', 'desc')
            ->get();
        
        // Get acknowledged routine issues
        $acknowledgedRoutine = Issue::whereRaw('TRIM((SELECT receive_by FROM invent.inv_issues WHERE invent.inv_issues.doc_no = invent.inv_issue_sub.doc_no)) = ? AND ackn_by_user = ?', [$emp_code, 'Y'])
            ->with('inventory')
            ->orderBy('dated', 'desc')
            ->get();
        
        // Merge both acknowledged collections and remove duplicates
        $allAcknowledged = $acknowledged->concat($acknowledgedRoutine)
            ->unique(function($item) {
                return $item->doc_no . '-' . $item->item_code . '-' . $item->emp_code;
            })
            ->sortByDesc('dated');
        
        // Get routine issues requested by the user
        $routineIssues = Issue::whereNull('emp_code')->whereNull('ackn_by_user')->whereHas('issueMaster', function($query) use ($emp_code) {
            $query->whereRaw('TRIM(receive_by) = ?', [$emp_code]);
        })->orderBy('doc_date', 'desc')->get();

        return view('inventory', compact('unacknowledged', 'allAcknowledged', 'routineIssues', 'acknowledged'));
    }
    
    public function acknowledgeItem(Request $request, $item_code, $doc_no)
    {
        // Validate request
        $request->validate([
            'remarks' => 'nullable|string|max:255'
        ]);
        
        $authUser = Auth::user();
        
        // Find the issue item
        $issue = Issue::where('item_code', $item_code)->where('doc_no', $doc_no)->first();
        
        // Verify the item belongs to the logged-in user
        // if($issue->emp_code != $authUser->emp_code){
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }
        \Log::info('Acknowledging item for emp_code: ' . $authUser->emp_code . ' - item_code: ' . $item_code);
        // Update the issue with acknowledgment
        $issue->update([
            'ackn_by_user' => 'Y',
            'dated' => now(),
            'remarks' => $request->input('remarks', '')
        ]);
        
        \Log::info('Item acknowledged for emp_code: ' . $authUser->emp_code . ' - item_code: ' . $item_code);
        return response()->json(['success' => 'Item acknowledged successfully', 'message' => 'Item has been acknowledged']);
    }
}
