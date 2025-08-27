<?php

namespace App\Http\Controllers;

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

class TimetableController extends Controller
{
    public function index()
    {
        return view('timetables.index');
    }

    public function newTimetable()
    {
        // $years = StudentClass::select('session_id')->distinct()->pluck('session_id');
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

        $validator->after(function ($validator) use ($request) {
            $from = Carbon::parse($request->from_date);
            $to = Carbon::parse($request->to_date);

            if ($from->diffInDays($to) > 7) {
                $validator->errors()->add('to_date', 'The date range should not exceed 7 days.');
            }
        });

        $validated = $validator->validate();

        $period = CarbonPeriod::create($validated['from_date'], $validated['to_date']);

        $dayMap = []; // e.g., '1-MONDAY' => '2025-08-04'

        foreach ($period as $date) {
            $weekday = strtoupper($date->format('l')); // e.g., MONDAY
            $dayNum = $date->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
            $key = $dayNum . '-' . $weekday;

            if (!isset($dayMap[$key])) {
                $dayMap[$key] = $date->format('d-m-Y'); // '2025-08-04'
            }
        }

        $sessionYear = getSessionYear($validated['year']);
        // dd($sessionYear);

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

        // dd($subjects);        
        
        return view('timetables.create', [
            'timetable' => $timetable,
            'classId' => $classId,
            'sessionYear' => $sessionYear,
            'subjects' => $subjects,
            'doctors' => $doctors,
            'dayMap' => $dayMap,
        ]);
    }
    public function getSubject(Request $request)
    {
        

        $subjectId = $request->input('subject_id');
        $subject = Subject::select('title')
            ->where('subject_id', $subjectId)
            ->first();    

            \Log::info('outgoing main_subject: ' . $subject->title);
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
        $userId = auth()->user()->emp_code;

        $count = count($subjectIds);

        // Track if all saves were successful
        $allSaved = true;
        $errors = [];

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

    public function getTimetables(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $yearId = $request->input('year_id', 'YEAR01');
        $periodType = $request->input('period_type');
        // dd($periodType);
        // $timetables = Timetable::with(['lectures' => function ($query) use ($startDate, $endDate) {
        //     $query->whereBetween('dated', [$startDate, $endDate]);
        // }])
        // ->where('year_id', $yearId)
        // ->get();
        $lectures = Lecture::whereBetween('dated', [$startDate, $endDate])
            ->get();
        $timetables = $lectures->load('timetable') // eager load
            ->pluck('timetable') // get timetables from each lecture
            ->filter(function ($timetable) use ($yearId) {
                return $timetable && $timetable->year_id === $yearId;
            })
            ->unique('doc_id') // prevent duplicates if same timetable used for many lectures
            ->values();
        // dd($timetables);    



        $events = [];

        foreach ($timetables as $timetable) {

            $parts = explode('-', $timetable->p_day);
            
            $weekdayName = strtoupper(trim($parts[1] ?? ''));
            
            if (!$weekdayName) {
                continue; // skip if invalid
            }

            $dates = collect();
            $date = $startDate->copy();
            
            while ($date <= $endDate) {
                // dd($date->format('l'), capitalizeWords($weekdayName));
                if ($date->format('l') === capitalizeWords($weekdayName)) {
                    logger("Comparing: " . strtoupper($date->format('l')) . " === $weekdayName");

                    $dates->push($date->copy());   
                }
                $date->addDay();
            }

            foreach ($dates as $lectureDate) {
                $statusEntry = $timetable->lectures->firstWhere('dated', $lectureDate->toDateString());
                $delivered = $statusEntry && $statusEntry->status == 1; // Assuming 1 means delivered

                $events[] = [
                    'title' => 'Subject ' . $timetable->subject_id . ($delivered ? ' (✔)' : ''),
                    'start' => $lectureDate->format('Y-m-d') . 'T' . $timetable->start_time,
                    'end' => $lectureDate->format('Y-m-d') . 'T' . $timetable->end_time,
                    'color' => $delivered ? '#28a745' : '#ffc107',
                ];
            }
        }
        // dd($events);
        return response()->json($events);
    }

}
