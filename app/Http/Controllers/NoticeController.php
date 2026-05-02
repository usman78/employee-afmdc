<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\User;
use App\Models\NoticeApproval;
use App\Notifications\NoticeApprovalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    /**
     * Display a listing of all notices with their approval details
     */
    public function index()
    {
        $notices = Notice::with(['creator', 'approvals.approver'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        return view('notices.index', compact('notices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('notices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $user = Auth::user();
        
        $attachmentPath = null;
        $attachmentName = null;

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('notices', 'public');
        }

        $notice = Notice::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'created_by' => $user->emp_code,
            'is_published' => true,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        // Find COO (Chief Operating Officer) by designation code or description
        $coo = User::whereHas('designation', function ($query) {
            $query->where('desg_code', '889')
                  ->orWhere('desg_desc', 'CHIEF OPERATING OFFICER');
        })->first();

        if ($coo) {
            // Create NoticeApproval record
            $approval = NoticeApproval::create([
                'notice_id' => $notice->id,
                'approver_id' => $coo->emp_code,
                'approval_status' => 'approved',
            ]);

            // Send notification to COO
            // $coo->notify(new NoticeApprovalNotification($notice));
        }

        return redirect()->route('notices.create')->with('success', 'Notice created successfully!');
    }

    /**
     * Display notice for approval
     */
    public function review($noticeId)
    {
        $notice = Notice::findOrFail($noticeId);
        $approval = NoticeApproval::where('notice_id', $noticeId)
                                //    ->where('approver_id', Auth::user()->emp_code)
                                   ->firstOrFail();
        // $notification = Auth::user()->notifications()
        //                     ->where('data->notice_id', $noticeId)
        //                     ->first();
        // $notification?->markAsRead();                                               

        return view('notices.review', compact('notice', 'approval'));
    }

    /**
     * Approve the notice
     */
    public function approve(Request $request, $noticeId)
    {
        $notice = Notice::findOrFail($noticeId);
        $approval = NoticeApproval::where('notice_id', $noticeId)
                                   ->where('approver_id', Auth::user()->emp_code)
                                   ->firstOrFail();

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        $approval->update([
            'approval_status' => 'approved',
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $notice->update(['is_published' => true]);

        return redirect()->route('notices.review', $noticeId)
                        ->with('success', 'Notice approved successfully!');
    }

    /**
     * Reject the notice
     */
    public function reject(Request $request, $noticeId)
    {
        $notice = Notice::findOrFail($noticeId);
        $approval = NoticeApproval::where('notice_id', $noticeId)
                                   ->where('approver_id', Auth::user()->emp_code)
                                   ->firstOrFail();

        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $approval->update([
            'approval_status' => 'rejected',
            'remarks' => $validated['remarks'],
        ]);

        return redirect()->route('notices.review', $noticeId)
                        ->with('success', 'Notice rejected.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
