<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
}
