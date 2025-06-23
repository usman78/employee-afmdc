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

        return view('tasks.meeting', [
            'meetings' => $meetings,
        ]);
    }


    public function tasks()
    {
        $user = auth()->user()->emp_code;

        $uncompletedTasks = DB::table('meet.mm_meet_task as t')
            ->leftJoin('meet.mm_prog_mont as p', function ($join) {
                $join->on('t.meet_no', '=', 'p.meet_no')
                    ->on('t.task_no', '=', 'p.task_no')
                    ->on('t.cat', '=', 'p.cat');
            })
            ->join('meet.mm_meet_resp as r', function ($join) use ($user) {
                $join->on('t.meet_no', '=', 'r.meet_no')
                    ->on('t.task_no', '=', 'r.task_no')
                    ->on('t.cat', '=', 'r.cat')
                    ->where('r.resp_prsn', '=', $user);
            })
            ->where(function ($query) {
                $query->where('p.status', '=', 0)
                    ->orWhereNull('p.status');
            })
            ->select(
                't.*',
                'p.meet_no as p_meet_no',
                'p.cat as p_cat',
                'p.task_no as p_task_no',
                'p.prog_desc',
                'p.compl_date',
                'p.rprt_date',
                'p.status',
                'r.comp_code'
            )
            ->orderBy('t.targ_date', 'desc')
            ->paginate(5);

        //Get all tasks that are completed
        $completedTasks = DB::table('meet.mm_meet_task as t')
            ->join('meet.mm_prog_mont as p', function ($join) {
                $join->on('t.meet_no', '=', 'p.meet_no')
                    ->on('t.task_no', '=', 'p.task_no')
                    ->on('t.cat', '=', 'p.cat');
            })
            ->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('meet.mm_meet_resp as r')
                    ->whereColumn('r.meet_no', 't.meet_no')
                    ->whereColumn('r.task_no', 't.task_no')
                    ->whereColumn('r.cat', 't.cat')
                    ->where('r.resp_prsn', $user);
            })
            ->where('p.status', 1)
            ->select(
                't.meet_no as t_meet_no',
                't.cat as t_cat',
                't.task_no as t_task_no',
                't.task_desc',
                't.targ_date',
                'p.prog_desc',
                'p.compl_date',
                'p.rprt_date',
                'p.status'
            )
            ->orderBy('t.targ_date', 'desc')
            ->paginate(5);
        
        return view('tasks.tasks', [
            'uncompletedTasks' => $uncompletedTasks,
            'completedTasks' => $completedTasks,
        ]);
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
