<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admissions;
use Illuminate\Support\Facades\Storage;

class AdmissionController extends Controller
{
    public function admissions() {
        $admissions = Admissions::with('user')
        ->where('image_name', '!=', null)
        ->orderByDesc('created_at')
        ->get();
        return view('admissions.admissions', ['admissions' => $admissions]);
    } 
    public function applicant($id) {
        $profile = Admissions::with('user')->where('ADM_APPLICANT_ID', $id)->first();
        $filePaths = [
            'profile' => "profile_{$id}",
            'cnic_front' => "cnic_front_{$id}",
            'cnic_back' => "cnic_back_{$id}",
            'fr_cnic_front' => "fr_cnic_front_{$id}",
            'fr_cnic_back' => "fr_cnic_back_{$id}",
            'domicel' => "domicel_{$id}",
            'matric' => "matric_document_{$id}",
            'fsc' => "fsc_document_{$id}",
            'bank_receipt' => "bank_receipt_{$id}",
        ];

    $filesAvailable = [];
    $fileFormat = null;
    foreach ($filePaths as $key => $path) {
        $fullPath = "admissions/{$id}/{$path}";

        if(Storage::exists($fullPath . '.jpeg')){
            $filesAvailable[$key] = true;
            $fileFormat = 'jpeg';
        } elseif (Storage::exists($fullPath . '.jpg')) {
            $filesAvailable[$key] = true;
            $fileFormat = 'jpg';
        } else {
            $filesAvailable[$key] = false;
        }
    }
        return view('admissions.applicant', ['profile' => $profile, 'filePaths' => $filePaths, 'filesAvailable' => $filesAvailable, 'fileFormat' => $fileFormat]);
    }
}
