<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Issue;
use App\Models\Inventory;
use App\Models\IssueMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;

class InventoryController extends Controller
{
    public function inventory($emp_code)
    {
        // Check if the logged in user is the same as the user whose inventory is being viewed
        $authUser = Auth::user();
        if($authUser->emp_code != $emp_code){
            return redirect()->route('home');
        }
        
        // Get unacknowledged items with pagination
        $unacknowledged = Issue::where('emp_code', $emp_code)
            ->where(function($query) {
                $query->whereNull('ackn_by_user')
                      ->orWhere('ackn_by_user', 'N');
            })
            ->with('inventory')
            ->orderBy('doc_date', 'desc')
            ->paginate(20, ['*'], 'unack_page');
        
        // Get acknowledged items (regular issues)
        $acknowledged = Issue::where('emp_code', $emp_code)
            ->where('ackn_by_user', 'Y')
            ->with('inventory')
            ->orderBy('dated', 'desc')
            ->get();
        
        // Get acknowledged routine issues
        // $acknowledgedRoutine = Issue::whereRaw('TRIM((SELECT receive_by FROM invent.inv_issues WHERE invent.inv_issues.doc_no = invent.inv_issue_sub.doc_no)) = ? AND ackn_by_user = ?', [$emp_code, 'Y'])
        //     ->with('inventory')
        //     ->orderBy('dated', 'desc')
        //     ->get();
        $acknowledgedRoutine = Issue::whereRaw(
            "TRIM((
                SELECT receive_by
                FROM invent.inv_issues
                WHERE invent.inv_issues.doc_no = invent.inv_issue_sub.doc_no
                AND TO_NUMBER(invent.inv_issues.yr_code) = TO_NUMBER(TO_CHAR(SYSDATE, 'YYYY'))
            )) = ?
            AND ackn_by_user = ?",
            [$emp_code, 'Y']
        )
        ->with('inventory')
        ->orderBy('dated', 'desc')
        ->get();

        // Merge both acknowledged collections and remove duplicates
        $mergedAcknowledged = $acknowledged->concat($acknowledgedRoutine)
            ->unique(function($item) {
                return $item->doc_no . '-' . $item->item_code . '-' . $item->emp_code;
            })
            ->sortByDesc('dated')
            ->values();
        
        // Manual pagination for merged acknowledged items
        $ackPage = request()->get('ack_page', 1);
        $ackPerPage = 20;
        $ackOffset = ($ackPage - 1) * $ackPerPage;
        $allAcknowledged = new Paginator(
            $mergedAcknowledged->slice($ackOffset, $ackPerPage),
            $ackPerPage,
            $ackPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'ack_page',
            ]
        );
        
        // Get routine issues requested by the user with pagination
        $routineIssues = Issue::whereNull('emp_code')->whereNull('ackn_by_user')->whereHas('issueMaster', function($query) use ($emp_code) {
            $query->whereRaw('TRIM(receive_by) = ?', [$emp_code]);
        })->orderBy('doc_date', 'desc')->paginate(20, ['*'], 'routine_page');

        return view('inventory', compact('unacknowledged', 'allAcknowledged', 'routineIssues'));
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
        
        // Update the issue with acknowledgment
        $issue->update([
            'ackn_by_user' => 'Y',
            'dated' => now(),
            'remarks' => $request->input('remarks', '')
        ]);
        
        return response()->json(['success' => 'Item acknowledged successfully', 'message' => 'Item has been acknowledged']);
    }
}
