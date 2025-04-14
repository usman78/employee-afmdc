<?php
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