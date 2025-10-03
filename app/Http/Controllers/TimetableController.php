<?php

namespace App\Http\Controllers;

use App\Models\StudentLectureDuplicate;
use App\Models\TimetableDuplicate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Timetable;
use App\Models\Lecture;
use App\Models\Subject;
use App\Models\StudentClass;
use Illuminate\Support\Facades\Validator;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use App\Models\Year;
use Log;

class TimetableController extends Controller
{
    public function index()
    {
        return view('timetables.index');
    }

    public function newTimetable()
    {
        $years = Year::all();
        $programs = StudentClass::select('program_id')->distinct()->pluck('program_id');

        return view('timetables.new-timetable', ['years' => $years, 'programs' => $programs]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|string',
            'program' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $validated = $validator->validate();

        $period = CarbonPeriod::create($validated['from_date'], $validated['to_date']);

        $dayMap = []; // e.g., '1-MONDAY' => '2025-08-04'

        foreach ($period as $date) {
            $weekday = strtoupper($date->format('l')); // e.g., MONDAY
            $dayNum = $date->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
            $value = $dayNum . '-' . $weekday;

            if (!isset($dayMap[$date->format('d-m-Y')])) {
                $dayMap[$date->format('d-m-Y')] = $value; // '2025-08-04'
            }
        }

        $sessionYear = getSessionYear($validated['year']);

        $classId = StudentClass::where('program_id', $validated['program'])
            ->where('session_id', $sessionYear)
            ->pluck('class_id')
            ->first();

        $timetableAll = Timetable::with('subject')
            ->where('class_id', $classId)
            ->orderBy('timestamp_id1', 'desc') // Order by timestamp_id1
            ->get();


        // Group by p_day and get top 1 of each
        $timetable = $timetableAll->groupBy('p_day')->map(function ($group) {
            return $group->take(1);
        });   
        
        $subjects = Subject::select('subject_id')
            ->where('subject_program', 'LIKE', '%' . $validated['program'] . '%')
            ->distinct()
            ->pluck('subject_id');
        
        $doctors = allDoctors();       
        
        return view('timetables.create', [
            'timetable' => $timetable,
            'classId' => $classId,
            'sessionYear' => $sessionYear,
            'subjects' => $subjects,
            'doctors' => $doctors,
            'dayMap' => $dayMap,
            'to_date' => $validated['to_date'],
            'from_date' => $validated['from_date'],
        ]);
    }
    public function getSubject(Request $request)
    {
        $subjectId = $request->input('subject_id');
        $subject = Subject::select('title')
            ->where('subject_id', $subjectId)
            ->first();    
        return response()->json(['main_subject' => $subject->title]);
    }
    public function store(Request $request)
    {
        $dates = $request->input('date');
        $days = $request->input('day');
        $yearIds = $request->input('year_id');
        $classIds = $request->input('class_id');
        $groups = $request->input('group');
        $subjectIds = $request->input('subject_id');
        $topics = $request->input('topic'); 
        $periodTypes = $request->input('period_type');
        $hods = $request->input('hod');
        $startTimes = $request->input('start_time');
        $endTimes = $request->input('end_time');
        $toDate = $request->input('to_date');
        $userId = auth()->user()->emp_code;

        $count = count($subjectIds);

        // Track if all saves were successful
        $allSaved = true;
        $errors = [];
        $timeNumber = getIncrementedId('mis.si_time_table_exp', 'timet_no');
        for ($i = 0; $i < $count; $i++) {
            try {
                $oracleDate = Carbon::createFromFormat('D, d-M', $dates[$i])->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = "Invalid date format for row ".($i+1);
                $allSaved = false;
                continue;
            }

            try {
                $timetableDuplicate = new TimetableDuplicate();
                $timetableDuplicate->doc_id = getIncrementedId('mis.si_time_table_exp', 'doc_id');
                $timetableDuplicate->year_id = $yearIds[$i];
                $timetableDuplicate->class_id = $classIds[$i];
                $timetableDuplicate->group_title = $groups[$i];
                $timetableDuplicate->p_day = $days[$i];
                $timetableDuplicate->datedm = $oracleDate;
                $timetableDuplicate->subject_id = $subjectIds[$i];
                $timetableDuplicate->lec_top = $topics[$i] ?? null;
                $timetableDuplicate->period_type = $periodTypes[$i];
                $timetableDuplicate->emp_code = $hods[$i];
                $timetableDuplicate->start_time = $startTimes[$i];
                $timetableDuplicate->end_time = $endTimes[$i];
                $timetableDuplicate->user_id1 = $userId;
                $timetableDuplicate->timestamp_id1 = now();
                $timetableDuplicate->terminal_id1 = 'ONLINE';
                $timetableDuplicate->datedt = $toDate;
                $timetableDuplicate->timet_no = $timeNumber;

                $timetableDuplicate->save();
            } catch (\Exception $e) {
                $errors[] = "Error saving record at row ".($i+1)." → ".$e->getMessage();
                $allSaved = false;
            }
        }

        if ($allSaved) {
            return redirect()->route('timetables.new-timetable')
                ->with('success', 'All timetable records created successfully.');
        } else {
            return redirect()->route('timetables.new-timetable')
                ->with('error', 'Some records could not be saved.')
                ->with('details', $errors);
        }
    }
    public function show()
    {
        $empCode = auth()->user()->emp_code;
        $lectures = StudentLectureDuplicate::with('timetable')
            ->whereHas('timetable', function ($query) use ($empCode) {
                $query->where('emp_code', $empCode);
            })
            ->get();
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        return view('timetables.show', compact('startDate', 'endDate', 'lectures', 'empCode'));
    }
    public function getTimetables($year_id, $program_id, Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate   = Carbon::parse($request->input('end_date'));
        // $empCode   = $request->input('emp_code');

        // now you get values directly from URL
        $yearId    = $year_id;
        $programId = $program_id;
        $events = [];

        if($yearId == null || $programId == null){
            return response()->json($events);
        }
        // Get class ID based on year and program
        $classId = getClassId($yearId, $programId);

        $timetable = TimetableDuplicate::with('lecture')
            ->where('class_id', $classId)
            ->whereBetween('datedm', [$startDate, $endDate])
            ->get();
        
        foreach ($timetable as $lecture) {
            $hod = getEmployeeNameAndPicture($lecture->emp_code);
            $delivered = $lecture->lecture?->emp_code != null;
            if($delivered){
                $teacher = getEmployeeNameAndPicture($lecture->lecture->emp_code);
            }
            $events[] = [
                'doc_id' => $lecture->doc_id,
                'is_finalized' => $lecture->lecture->status == 1,
                'title' =>  ($lecture->lecture->topic ?? 'Not Assigned'),
                'start' => Carbon::parse($lecture->datedm)->toDateString() . 'T' . ($lecture->start_time ?? '00:00:00'),
                'end'   => Carbon::parse($lecture->datedm)->toDateString() . 'T' . ($lecture->end_time ?? '00:00:00'),
                'start_time' => $lecture->start_time ?? 'Not Available',
                'end_time' => $lecture->end_time ?? 'Not Available',
                'color' => $delivered ? '#28a745' : '#ffc107',
                'hod_name' => $hod->name,
                'hod_picture' => asset('pictures') . '/' . $hod->pic_name,
                'delivered' => $delivered,
                'delivered_by' => $delivered ? $teacher->name : null,
                'teacher_picture' => $delivered ? asset('pictures') . '/' . $teacher->pic_name : null,
            ];
        }
        Log::info('events data from controller: ', $events);
        return response()->json($events);
    }
    public function markFinalized(Request $request)
    {
        Log::info('Mark Finalized Request Data: ', $request->all());
        $docIds = $request->input('doc_ids');

        if(empty($docIds) || !is_array($docIds)) {
            return response()->json(['error' => 'No timetable entries are present.'], 400);
        }

        try {
            DB::beginTransaction();

            foreach ($docIds as $docId) {
                $timetable = StudentLectureDuplicate::where('fk_doc_id', $docId)->first();

                if ($timetable) {
                    $timetable->status = 1;
                    $timetable->save();
                }
                else {
                    Log::warning("Timetable entry with doc_id {$docId} not found.");
                }
            }

            DB::commit();
            return response()->json(['success' => 'Timetable marked as finalized.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error finalizing timetable: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while finalizing the timetable.'], 500);
        }
    }
}
