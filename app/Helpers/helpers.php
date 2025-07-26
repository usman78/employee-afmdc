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