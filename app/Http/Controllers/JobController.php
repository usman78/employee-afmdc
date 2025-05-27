<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\Education;
use App\Models\Designation;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::where('status', 'C')
            ->orWhere('status', null)
            ->orderBy('app_no', 'desc')->get();
        return view('jobs.jobs', ['jobs' => $jobs]);
    }

    public function summaryDashboard()
    {
        $vacancy_jobs = Vacancy::withCount('jobs')->get();
        $open_jobs = DB::select("SELECT p.desg_short, COUNT(j.job_id) AS application_count
            FROM online_job_mst j
            JOIN pay_desig p ON j.position_id = p.desg_code
            WHERE j.job_id = 9999
            GROUP BY p.desg_short");
        $total_jobs = Job::count();
        $total_open_jobs = Job::where('job_id', 9999)->count();
        $total_vacancy_jobs = $total_jobs - $total_open_jobs;
        $total_shortlisted_jobs = Job::where('status', 'S')->count();
            
        return view('jobs.dashboard', [
            'vacancy_jobs' => $vacancy_jobs, 
            'open_jobs' => $open_jobs, 
            'total_jobs' => $total_jobs, 
            'total_open_jobs' => $total_open_jobs, 
            'total_vacancy_jobs' => $total_vacancy_jobs, 
            'total_shortlisted_jobs' => $total_shortlisted_jobs
        ]);   
    }

    public function show($id)
    {
        $job = Job::find($id);
        $education = Education::where('app_no', $id)->get();
        if($education) {
            $job->education = $education;
        }

        $designation = Designation::find($job->position_id);
        if($designation) {
            $job->designation = $designation->desg_short;
        }
        else{
            $vacany = Vacancy::find($job->job_id);
            $job->designation = $vacany->job_id;
        }
        return view('jobs.profile', ['job' => $job]);
    }

    public function changeStatus(Request $request, $app_no){
        $status = $request->input('status');
        $application = Job::where('app_no', $app_no)->first();
        if($application){
            $application->status = $status;
            $application->save();
            return response()->json(['success' => 'Status of the application changed successfully']);
        }
        else {
            return response()->json(['error' => 'The application number no found!'], 404);
        }
    }

    public function shortlisted(){
        $jobs = Job::where('status', 'S')->get();
        return view('jobs.jobs', ['jobs' => $jobs]);
    }

    public function designationJobs($position){
        $position = str_replace('-', '/', $position);
        $job_id = Vacancy::select('job_id')->where('job_description', $position)->get();
        if($job_id->count() === 1){
            $jobs = Job::whereIn('job_id', $job_id)->get();
        }
        else {
            $job_id = Designation::select('desg_code')->where('desg_short', $position)->get();
            $jobs = Job::whereIn('position_id', $job_id)->get();
        }
        return view('jobs.jobs', ['jobs' => $jobs]);
    }

    public function debug()
    {

        $jobs = Job::where('job_id', 1004)->get();
        if($jobs) {
            return response()->json($jobs);
        }
    }

}
