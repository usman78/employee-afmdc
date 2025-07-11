<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Notifications\NewServiceRequestNotification;
use App\Models\User;


class ServiceRequestController extends Controller
{
    public function create()
    {
        $user = auth()->user()->emp_code;
        $dept = auth()->user()->dept_code;
        
        return view('service_requests.create', [
            'user' => $user,
            'dept' => $dept,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'JOB_TYPE' => 'required|integer',
            'DESCRIPTION' => 'required|string|max:1000',
            'PRIORITY' => 'required|in:NORMAL,URGENT',
        ]);

        $serviceRequest = new ServiceRequest();
        $serviceRequest->ID = getIncrementedId('SERVICE_REQUESTS', 'ID');
        $serviceRequest->REQUESTER_ID = $request['REQUESTER_ID'];
        $serviceRequest->DEPARTMENT_ID = $request['DEPARTMENT_ID'];
        $serviceRequest->JOB_TYPE = $validatedData['JOB_TYPE'];
        $serviceRequest->DESCRIPTION = $validatedData['DESCRIPTION'];
        $serviceRequest->PRIORITY = $validatedData['PRIORITY'];
        $serviceRequest->STATUS = 'PENDING_HOD_APPROVAL';
        $serviceRequest->CREATED_AT = now();
        $serviceRequest->UPDATED_AT = now();
        $serviceRequest->save();

        // Notify the HOD or relevant personnel about the new service request
        $boss = hisBoss($request['REQUESTER_ID']);
        if ($boss) {
            $bossUser = User::where('emp_code', $boss)->first();
            if ($bossUser) {
                $bossUser->notify(new NewServiceRequestNotification($serviceRequest));
            }
        }

        return redirect()->route('service-requests.index')->with('success', 'Service request created successfully.');
    }

    public function index()
    {
        $requests = ServiceRequest::where('requester_id', auth()->user()->emp_code)->get();

        return view('service_requests.index', compact('requests'));
    }

    public function hodApprovals(){
        // $requests = ServiceRequest::where('status', 'pending')->get();
        return view('service_requests.hod_approvals'); 
    }

    public function show($id)
    {
        $request = ServiceRequest::with('department')->findOrFail($id);
        // dd($request);
        if ($request) {
            $requester_name = User::select('name')->where('emp_code',$request->requester_id)->first();
            $request->requester_name = $requester_name->name; 
        }
        return view('service_requests.show', compact('request'));
    }
}
