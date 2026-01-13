<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FilesController extends Controller
{
    public function download($id, $fileName)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        if(!Auth::user()->isHR()){
            abort(403, 'Unauthorized');
        }

        $path = "applications/{$id}/{$fileName}";

        if (!Storage::exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->file(Storage::path($path));;
    }
    public function getAdmissionFiles($admission, $file)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $path = "admissions/{$admission}/{$file}";

        if (!Storage::exists($path)) {
            Log::error("File not found: " . $path);
            abort(404, 'File not found.');
        }

        return response()->file(Storage::path($path));
    }
    public function downloadAdmissionFile($id, $fileName, $fileFormat)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $filePaths[] = [
            'profile' => 'profile_' . $id . '.' . $fileFormat,
            'cnic_front' => 'cnic_front_' . $id . '.' . $fileFormat,
            'cnic_back' => 'cnic_back_' . $id . '.' . $fileFormat,
            'fr_cnic_front' => 'fr_cnic_front_' . $id . '.' . $fileFormat,
            'fr_cnic_back' => 'fr_cnic_back_' . $id . '.' . $fileFormat,
            'domicel' => 'domicel_' . $id . '.' . $fileFormat,
            'matric' => 'matric_document_' . $id . '.' . $fileFormat,
            'fsc' => 'fsc_document_' . $id . '.' . $fileFormat,
            'bank_receipt' => 'bank_receipt_' . $id . '.' . $fileFormat,
        ];

        $path = "admissions/{$id}/{$filePaths[0][$fileName]}";
        
        if (!Storage::exists($path)) {
            abort(404, 'File not found.');
        }
        return response()->file(Storage::path($path));
    }
}
