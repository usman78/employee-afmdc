<?php
use Carbon\Carbon;
if (!function_exists('capitalizeWords')) {
    function capitalizeWords($string) {
        return ucwords(strtolower($string));
    }
}

if (!function_exists('capitalizeAbbreviation')) {
    function capitalizeAbbreviation($string,  $abbreviations = ['IT', 'HOD', 'HR', 'CEO', 'COO', 'VC', 'PROF' ]) {
        return preg_replace_callback('/\b[a-zA-Z]{2,}\b/', function ($match) use ($abbreviations) {
            return in_array(strtoupper($match[0]), $abbreviations) ? strtoupper($match[0]) : ucfirst($match[0]);
        }, $string);
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat($string) {
        return date('d-m-Y', strtotime($string));
    }
}
// Format date as e.g. 25-Dec-2023
if (!function_exists('dateDayMonthFormat')) {
    function dateDayMonthFormat($string) {
        return date('d-M-Y', strtotime($string));
    }
}

if (!function_exists('dateAndTimeFormat')) {
    function dateAndTimeFormat($string) {
        return date('d-m-Y H:i', strtotime($string));
    }
}

function parseDateRange(string $range): array
{
    list($from, $to) = explode(' - ', $range);

    $fromDate = Carbon::createFromFormat('d-m-Y', date('d-m-Y', strtotime($from)));
    $toDate = Carbon::createFromFormat('d-m-Y', date('d-m-Y', strtotime($to)));
    $numberOfDays = $fromDate->diffInDays($toDate) + 1;

    return [
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'numberOfDays' => $numberOfDays,
    ];
}

function parseDateToRange(string $range): array
{
    list($from, $to) = explode('_to_', $range);

    $fromDate = Carbon::createFromFormat('d-m-Y', date('d-m-Y', strtotime($from)));
    $toDate = Carbon::createFromFormat('d-m-Y', date('d-m-Y', strtotime($to)));
    $numberOfDays = $fromDate->diffInDays($toDate) + 1;

    return [
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'numberOfDays' => $numberOfDays,
    ];
}

function numberOfLeaveDays($fromDate, $toDate)
{
    $fromDate = Carbon::createFromFormat('d-m-Y', date('d-m-Y', strtotime($fromDate)));
    $toDate = Carbon::createFromFormat('d-m-Y', date('d-m-Y', strtotime($toDate)));
    $numberOfDays = $fromDate->diffInDays($toDate) + 1;
    return $numberOfDays;
}

function hisBoss($emp_code)
{
    $boss = \DB::table('pre_leave_auth')
        ->where('emp_code_l', $emp_code)
        ->where('type', 'A')
        ->value('emp_code_a');

    return $boss;    
}

function employeeName($emp_code)
{
    $name = \DB::table('pay_pers')
        ->where('emp_code', $emp_code)
        ->value('name');

    return $name;    
}

function jobPlacement($loca_code)
{
    if($loca_code == 1){
        return 'Aziz Fatimah Medical & Dental College, Faisalabad';
    } else if ($loca_code == 2){
        return 'Aziz Fatimah Hospital, Faisalabad';
    } else {
        return 'N/A';
    }    
}

function designationAtJoining($emp_code)
{
    $designation = \DB::table('pis_expr')
        ->where('emp_code', $emp_code)
        ->where('rcrd_num', 1)
        ->value('desig');

    return $designation;    
}

function getItManagerCode()
{
    $itManager = \DB::table('pay_pers')
        ->where('desg_code', 44)
        ->where('quit_stat', null)
        ->value('emp_code');
    
    return $itManager;    
}

function getIncrementedId($tableName, $columnName)
{
    $max = DB::table($tableName)->max($columnName);
    return $max + 1;
}

function checkFullLeaveExists($emp_code, $date)
{
    $leave = \DB::table('pre_leave_tran')
        ->where('emp_code', $emp_code)
        ->whereIn('leave_code', [1,2,3])
        ->where('from_date', '<=', $date)
        ->where('to_date', '>=', $date)
        ->first();

    return $leave ? true : false;
}
function ifLeaveExists($emp_code, $date)
{
    return \DB::table('pre_leave_tran')
        ->where('emp_code', $emp_code)
        ->where(function ($query) use ($date) {
            $query->whereRaw("TRUNC(from_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$date])
                  ->whereRaw("TRUNC(to_date) >= TO_DATE(?, 'YYYY-MM-DD')", [$date]);
        })
        ->exists();
}
function allDoctors()
{
    $doctors = \DB::table('pay_pers')
        ->select('emp_code', 'name')
        ->where('catg_code', 2)
        ->where('quit_stat', null) // Assuming 1 is the designation code for doctors
        ->get();

    return $doctors;
}
function getSessionYear($classYear)
{
    $currentYear = (int) date('Y');       // ensures it's an integer
    $classYear   = (int) $classYear;      // converts input to integer
    $sessionYear = $currentYear - $classYear;
    return $sessionYear;
}

function employeeStatus($emp_code)
{
    $status = \DB::table('pay_pers')
        ->where('emp_code', $emp_code)
        ->value('stat_code');

    switch ($status) {
        case 1:
            $status = "Permanent";
            break;
        case 2:
            $status = "Temporary"; 
            break;
        case 3:
            $status = "Probational"; 
            break;  
        case 4:
            $status = "Contract"; 
            break;
        case 5:
            $status = "Training"; 
            break;          
        
        default:
            $status = 'N/A';
            break;
    }    
    return $status;
}

function getClassId($yearId, $programId)
{
    $currentYear = (int) date('Y'); // ensures it's an integer
    $sessionId = $currentYear - (int)$yearId; // calculates session ID based on current year and yearId
    $classId = \DB::table('mis.si_class')
        ->where('program_id', $programId)
        ->where('session_id', $sessionId)
        ->value('class_id');

    return $classId;
}
function getSubjectTitle($subjectId)
{
    $title = \DB::table('mis.si_subject')
        ->where('subject_id', $subjectId)
        ->value('title');

    return $title;
}
function getProgramName($programId)
{
    switch ($programId) {
        case 1:
            $programId = "DPT";
            break;
        case 2:
            $programId = "MIT";
            break;
        case 3:
            $programId = "MLT";
            break;
        case 4:
            $programId = "OTT";
            break;
        case 5:
            $programId = "BS Nutrition";
            break;
        case 6:
            $programId = "MBBS";
            break;
        default:
            $programId = "N/A";
            break;
    }

    return $programId;
}
function getEmployeeNameAndPicture($emp_code)
{
    $nameAndPic = \DB::table('pay_pers')
        ->select('pic_name', 'name')
        ->where('emp_code', $emp_code)
        ->first();

    return $nameAndPic;
}
function checkTimetableAccess($emp_code)
{
    $access = [851, 199, 883, 856, 1045, 1171];
    return in_array($emp_code, $access);
}
function checkMultipleLeaves($emp_code, $fromDate, $toDate)
{
    $query = \DB::table('pre_leave_tran')
        ->where('emp_code', $emp_code)
        ->where(function ($q) use ($fromDate, $toDate) {
            $q->whereRaw("TRUNC(from_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$fromDate])
              ->whereRaw("TRUNC(to_date) >= TO_DATE(?, 'YYYY-MM-DD')", [$fromDate]);
            $q->orWhereRaw("TRUNC(from_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$toDate])
              ->whereRaw("TRUNC(to_date) >= TO_DATE(?, 'YYYY-MM-DD')", [$toDate]);
            $q->orWhereRaw("TRUNC(from_date) >= TO_DATE(?, 'YYYY-MM-DD')", [$fromDate])
              ->whereRaw("TRUNC(to_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$toDate]);
        })
        ->where('status','!=', 9);
    $leave = $query->first();
    return $leave ? true : false;
}
function getProfilePicName($emp_code)
{
    $picName = $emp_code . '.jpg';
    return $picName;
}
function minutesWorked($timein, $timeout) {
    if ($timein && $timeout) {
        $in = Carbon::parse($timein);
        $out = Carbon::parse($timeout);
        $workedMinutes = round($in->diffInMinutes($out), 1);
        return $workedMinutes;
    }
    return 0;
}