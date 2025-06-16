<?php

namespace App\Http\Controllers;

use App\Models\Responsibility;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\Tasks;
use App\Models\Sop;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index()
    {
        $meeting = Meeting::where('app_emp', auth()->user()->emp_code)
            // ->where('status', '!=', 'C')
            // ->orderBy('meet_date', 'desc')
            ->get();
        return view('tasks.index' , [
            'meetings' => $meeting,
            
        ]);
    }

    public function meetings()
    {
        $empCode = auth()->user()->emp_code;
        $meetings = DB::select("
            SELECT m.*
            FROM meet.mm_meet_master m
            INNER JOIN meet.mm_meet_part mp ON m.meet_no = mp.meet_no and m.cat = mp.cat
            WHERE mp.prtcpnt_code = ?
        ", [$empCode]);

        // $participantMeetings = DB::table('meet.mm_meet_part')
        //     ->where('prtcpnt_code', $empCode)
        //     ->select('meet_no', 'cat')
        //     ->get();

        // $meetingKeys = $participantMeetings->map(function ($item) {
        //     return [$item->meet_no, $item->cat];
        // })->toArray();    

        //     // dd($meetingNos);

        // $meetings = DB::select("
        //     SELECT m.*, mp.name, mp.prtcpnt_code, mp.cat AS participant_cat
        //     FROM meet.mm_meet_master m
        //     INNER JOIN meet.mm_meet_part mp 
        //         ON m.meet_no = mp.meet_no AND m.cat = mp.cat
        //     WHERE EXISTS (
        //         SELECT 1 FROM meet.mm_meet_part p
        //         WHERE p.meet_no = m.meet_no AND p.cat = m.cat AND p.prtcpnt_code = ?
        //     )
        //     ORDER BY m.meet_no, m.cat
        // ", [$empCode]);

        // dd($meetings);

        return view('tasks.meeting', [
            'meetings' => $meetings,
        ]);
    }

    public function assignedTasks()
    {
        $responsibility = Responsibility::where('resp_prsn', auth()->user()->emp_code)->get();

        $assignedTasks = Tasks::whereIn('meet_no', $responsibility->pluck('meet_no'))
            ->whereIn('cat', $responsibility->pluck('cat'))
            ->whereIn('task_no', $responsibility->pluck('task_no'))
            ->orderBy('targ_date', 'desc')
            ->paginate(5);

        return view('tasks.tasks', [
            'assignedTasks' => $assignedTasks,
        ]);
    }

    public function sops()
    {
        $depts = Department::all();
        // $depts = $depts->sop()->whereNotNull('department_id')->get();

        // $sops->each(function ($sop) {
        //     $sop->department = $sop->department()->get();
        // });

        // dd($sops);
        
        return view('tasks.sops', [
            'sops' => $depts->map(function ($dept) {
                $sop = Sop::where('department_id', $dept->dept_code)->get();
                if ($sop->isEmpty()) {
                    return null; // Skip departments without Sops
                }
                return [
                    'department' => $dept->dept_desc,
                    'sops' => Sop::where('department_id', $dept->dept_code)->get(),
                ];
            }),
        ]);
    }

    public function createSop()
    {
        $sop = new \App\Models\Sop();
        $sop->document_path = '24.pdf';
        $sop->title = 'Protection Against Harassment of Women at Worke Place';
        $sop->department_id = 28;
        $sop->save();
        return view('404');
    }
}
