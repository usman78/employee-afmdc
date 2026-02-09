<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExitInterview; 
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ExitInterviewController extends Controller
{
    public function create($emp_code)
    {
        // Check if employee already submitted to prevent duplicates
        $existing = ExitInterview::where('user_id', Auth::id())->first();
        
        if ($existing) {
            return view('exit.create-exit')->with('status', 'You have already submitted your exit interview.');
        }
        $user = auth()->user();

        return view('exit.create-exit', ['user' => $user]);
    }

    /**
     * Store the feedback form.
     */
    public function store(Request $request)
    {
        // Validation based on the document fields
        $validated = $request->validate([
            // Separation Details 
            'separation_type' => 'required|string',
            'date_of_leaving' => 'required|date',
            
            // Reasons (Checkboxes) 
            'reasons' => 'nullable|array',
            'prevented_departure' => 'nullable|string', // [cite: 3]
            
            // Open Ended [cite: 4]
            'liked_most' => 'nullable|string',
            'liked_least' => 'nullable|string',
            'suggestions' => 'nullable|string', // [cite: 12]
            
            // Scales 
            'workload' => 'required|string', 
            'recommend_friend' => 'required|string',
            
            // Matrix Ratings (Stored as JSON)
            'ro_ratings' => 'required|array', // 
            'company_ratings' => 'required|array', // 
            
            // Permission 
            'share_with_ro' => 'required|boolean',
        ]);

        $exitForm = new ExitInterview();
        $exitForm->user_id = Auth::id(); // Dynamic association
        // dd($exitForm->user_id);
        // Basic Fields
        $exitForm->leave_date = $request->date_of_leaving; // 
        $exitForm->separation_type = $request->separation_type;
        $exitForm->prevented_departure = $request->prevented_departure;
        $exitForm->liked_most = $request->liked_most;
        $exitForm->liked_least = $request->liked_least;
        $exitForm->suggestions = $request->suggestions;
        $exitForm->workload = $request->workload;
        $exitForm->recommend_friend = $request->recommend_friend;
        $exitForm->share_with_ro = $request->share_with_ro;

        // JSON Fields (Casting arrays to JSON)
        // Stores data from "Unsatisfactory working conditions" and "Voluntary Resignation" 
        $exitForm->reasons = json_encode($request->reasons);
        
        // Stores RO ratings (Fairness, Recognition, etc) 
        $exitForm->ro_ratings = json_encode($request->ro_ratings);
        
        // Stores Company ratings (Cooperation, Training, Pay, etc) 
        $exitForm->company_ratings = json_encode($request->company_ratings);

        $exitForm->save();

        return view('exit.create-exit')->with('success', 'Thank you. Your feedback has been recorded confidentially.');
    }

    /**
     * Admin Report View
     */
    public function report()
    {
        // Eager load the User model to display Name/Designation in the report
        $interviews = ExitInterview::with('user')->latest()->paginate(1);

        return view('exit.report', compact('interviews'));
    }
    public function show($id)
    {
        // Eager load the user relation to get Name, Designation, and Dept
        $interview = ExitInterview::with('user')->findOrFail($id);
        $reasons = json_decode($interview->reasons, true) ?? [];
        $roRatings = json_decode($interview->ro_ratings, true) ?? [];
        $companyRatings = json_decode($interview->company_ratings, true) ?? [];
        return view('exit.show', compact('interview','reasons', 'roRatings', 'companyRatings'));
    }
    public function downloadPDF($id)
    {
        $interview = ExitInterview::with('user')->findOrFail($id);
        
        $data = [
            'interview' => $interview,
            'reasons' => json_decode($interview->reasons, true) ?? [],
            'roRatings' => json_decode($interview->ro_ratings, true) ?? [],
            'companyRatings' => json_decode($interview->company_ratings, true) ?? []
        ];

        $pdf = Pdf::loadView('pdf.exit-report', $data);
        
        return $pdf->stream('Exit_Interview_'.$interview->user->name.'.pdf');
    }
}
