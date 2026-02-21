<?php

namespace App\Http\Controllers;

use App\Models\Responsibility;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\TaskProgress;
use App\Models\Tasks;
use App\Models\Sop;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class TaskController extends Controller
{
    public function index()
    {
        return view('tasks.index');
    }

    public function meetings()
    {
        $empCode = auth()->user()->emp_code;

        $meetings = DB::table('meet.mm_meet_master as m')
            ->join('meet.mm_meet_part as mp', function ($join) {
                $join->on('m.meet_no', '=', 'mp.meet_no')
                    ->on('m.cat', '=', 'mp.cat');
            })
            ->where('mp.prtcpnt_code', $empCode)
            ->select('m.*') 
            ->orderBy('m.meet_date', 'desc')
            ->paginate(5);

        $meetingFilters = $meetings->map(function ($meeting) {
            return [
                'meet_no' => $meeting->meet_no,
                'cat' => $meeting->cat,
            ];
        });

        $tasksByMeeting = collect();
        if ($meetingFilters->isNotEmpty()) {
            $latestProgress = DB::table('meet.mm_prog_mont as p')
                ->select(
                    'p.meet_no',
                    'p.cat',
                    'p.task_no',
                    DB::raw('MAX(p.rprt_date) as max_rprt_date')
                )
                ->where('p.resp_prsn', $empCode)
                ->groupBy('p.meet_no', 'p.cat', 'p.task_no');

            $tasks = DB::table('meet.mm_meet_task as t')
                ->join('meet.mm_meet_resp as r', function ($join) use ($empCode) {
                    $join->on('t.meet_no', '=', 'r.meet_no')
                        ->on('t.task_no', '=', 'r.task_no')
                        ->on('t.cat', '=', 'r.cat')
                        ->where('r.resp_prsn', '=', $empCode);
                })
                ->leftJoinSub($latestProgress, 'lp', function ($join) {
                    $join->on('t.meet_no', '=', 'lp.meet_no')
                        ->on('t.task_no', '=', 'lp.task_no')
                        ->on('t.cat', '=', 'lp.cat');
                })
                ->leftJoin('meet.mm_prog_mont as p', function ($join) use ($empCode) {
                    $join->on('t.meet_no', '=', 'p.meet_no')
                        ->on('t.task_no', '=', 'p.task_no')
                        ->on('t.cat', '=', 'p.cat')
                        ->on('p.rprt_date', '=', 'lp.max_rprt_date')
                        ->where('p.resp_prsn', '=', $empCode);
                })
                ->where(function ($query) use ($meetingFilters) {
                    foreach ($meetingFilters as $filter) {
                        $query->orWhere(function ($innerQuery) use ($filter) {
                            $innerQuery->where('t.meet_no', $filter['meet_no'])
                                ->where('t.cat', $filter['cat']);
                        });
                    }
                })
                ->select(
                    't.meet_no',
                    't.cat',
                    't.task_no',
                    't.task_desc',
                    't.targ_date',
                    'r.comp_code',
                    'p.prog_desc',
                    'p.compl_date',
                    'p.rprt_date',
                    'p.status'
                )
                ->orderBy('t.targ_date', 'asc')
                ->get();

            $tasksByMeeting = $tasks->groupBy(function ($task) {
                return $task->meet_no . '|' . $task->cat;
            });
        }

        return view('tasks.meeting', [
            'meetings' => $meetings,
            'tasksByMeeting' => $tasksByMeeting,
        ]);
    }


    public function tasks()
    {
        return redirect()->route('meetings');
    }
    public function updateProgress(Request $request)
    {
        $compCode = $request->input('comp_code');
        $validator = Validator::make($request->all(), [
            'prog_desc' => 'required|string',
            'compl_date' => 'nullable|date',
            'status' => 'required|in:0,1',
            'meet_no' => 'required',
            'cat' => 'required',
            'task_no' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $validated = $validator->validated();

        DB::insert("
            INSERT INTO MEET.MM_PROG_MONT (
                MEET_NO,
                COMP_CODE,
                CAT,
                TASK_NO,
                RESP_PRSN,
                RPRT_DATE,
                RPRTR_CODE,
                PROG_DESC,
                COMPL_DATE,
                STATUS
            ) VALUES (
                :meet_no,
                :comp_code,
                :cat,
                :task_no,
                :resp_prsn,
                TRUNC(SYSDATE), 
                :rprtr_code,
                :prog_desc,
                :compl_date,
                :status
            )
        ", [
            'meet_no'    => $validated['meet_no'],
            'comp_code'  => $compCode ? $compCode : null,
            'cat'        => $validated['cat'],
            'task_no'    => $validated['task_no'],
            'resp_prsn'  => auth()->user()->emp_code,
            'rprtr_code' => auth()->user()->emp_code,
            'prog_desc'  => $validated['prog_desc'],
            'compl_date' => $validated['compl_date'],
            'status'     => $validated['status'],
        ]);

        return response()->json(['success' => true]);
    }

    public function sops()
    {
        $depts = Department::all();
        
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
        $sop = new Sop();
        $sop->document_path = '24.pdf';
        $sop->title = 'Protection Against Harassment of Women at Worke Place';
        $sop->department_id = 28;
        $sop->save();
        return view('404');
    }
}
