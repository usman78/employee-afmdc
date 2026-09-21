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

    public function openJobs()
    {
        $open_jobs = DB::select("SELECT p.desg_short, COUNT(j.job_id) AS application_count
            FROM online_job_mst j
            JOIN pay_desig p ON j.position_id = p.desg_code
            WHERE j.job_id = 9999
            GROUP BY p.desg_short");
        $total_jobs = Job::count();
        $total_open_jobs = Job::where('job_id', 9999)->count();
        $total_vacancy_jobs = $total_jobs - $total_open_jobs;
        $total_shortlisted_jobs = Job::where('status', 'S')->count();
            
        return view('jobs.open-jobs', [
            'open_jobs' => $open_jobs, 
            'total_jobs' => $total_jobs, 
            'total_open_jobs' => $total_open_jobs, 
            'total_vacancy_jobs' => $total_vacancy_jobs, 
            'total_shortlisted_jobs' => $total_shortlisted_jobs
        ]); 
    }

    public function vacancyJobs()
    {
        $vacancy_jobs = Vacancy::withCount('jobs')->get();
        $total_jobs = Job::count();
        $total_open_jobs = Job::where('job_id', 9999)->count();
        $total_vacancy_jobs = $total_jobs - $total_open_jobs;
        $total_shortlisted_jobs = Job::where('status', 'S')->count();

        return view('jobs.vacancy-jobs', [
            'vacancy_jobs' => $vacancy_jobs, 
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
            // $job->designation = $vacany->job_id;
            $job->vacancy = $vacany->job_description;
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
    public function sendShortlistEmail($app_no)
    {
        $job = Job::where('app_no', $app_no)->first();
        if($job) {
            $name = $job->app_name;
            if($job->job_id == 9999) {
                $designation = Designation::find($job->position_id);
                $job_title = $designation->desg_short;
            }
            else {
                $vacany = Vacancy::find($job->job_id);
                $job_title = Designation::find($vacany->desg_code)->desg_short;
            }
            // dd('job title: ' .  $job_title . ' and name: ' . $name);
            \Mail::to($job->email)->send(new \App\Mail\JobShortlistMail($name, $job_title));

            return redirect()->back()->with('success', 'Shortlist email sent successfully to ' . $job->email);
        }
        else {
            return redirect()->back()->with('error', 'Application record not found!');
        }
    }
    public function jobSearch(Request $request)
    {
        $query = Job::query();

        if ($request->filled('position')) {
            $position = str_replace('-', '/', $request->input('position'));
            $designationIds = Designation::where('desg_short', 'like', '%' . $position . '%')->pluck('desg_code');
            $vacancyIds = Vacancy::where('job_description', 'like', '%' . $position . '%')->pluck('job_id');
            $query->where(function($q) use ($designationIds, $vacancyIds) {
                $q->whereIn('position_id', $designationIds)
                  ->orWhereIn('job_id', $vacancyIds);
            });
        }

        if ($request->filled('city')) {
            $city = $request->input('city');
            $query->where('city', 'like', '%' . $city . '%');
        }

        if ($request->filled('salary_min')) {
            $query->whereRaw("
                TO_NUMBER(
                    REPLACE(
                        SUBSTR(expt_sal, 1, INSTR(expt_sal, '-') - 1),
                        ',',
                        ''
                    )
                ) <= ?
            ", $request->salary_min);
        }

        if ($request->filled('salary_max')) {
            $query->whereRaw("
                TO_NUMBER(
                    REPLACE(
                        SUBSTR(expt_sal, INSTR(expt_sal, '-') + 1),
                        ',',
                        ''
                    )
                ) >= ?
            ", $request->salary_max);
        }

        $jobs = $query->get();

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
