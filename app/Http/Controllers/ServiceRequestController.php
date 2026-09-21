<?php

namespace App\Http\Controllers;

use App\Models\RequestUpdate;
use App\Notifications\ServiceRequestAssignedToIt;
use App\Notifications\ServiceRequestRejectionByIt;
use App\Notifications\ServiceRequestToItNotification;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Notifications\NewServiceRequestNotification;
use App\Models\User;
use App\Models\RequestApproval;
use App\Notifications\RequestApprovalNotification;
use App\Notifications\NewServiceRequestAssigned;
use App\Models\RequestAssignment;
use App\Notifications\ServiceUpdateFromIt;


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

    public function show($id)
    {
        $request = ServiceRequest::with('department', 'assignment')->findOrFail($id);
        
        if($request) {
            $requester_name = User::select('name')->where('emp_code',$request->requester_id)->first();
        }
        return view('service_requests.show', compact('request', 'requester_name'));
    }
    public function approve(Request $request, $id)
    {
        $validatedData = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string|max:1000',
        ]);
        $serviceRequest = ServiceRequest::findOrFail($id);
        if(!$serviceRequest) {
            return redirect()->back()->with('error', 'Service request not found.');
        }
        $requestApproval = new RequestApproval;
        $requestApproval->id = getIncrementedId('REQUEST_APPROVALS', 'id');
        $requestApproval->service_request_id = $id;
        $requestApproval->approved_by = auth()->user()->emp_code;
        $requestApproval->status = $validatedData['decision'];
        $requestApproval->remarks = $validatedData['remarks'];
        $requestApproval->role = 'HOD';
        $requestApproval->approval_date = now();
        $requestApproval->save();
        if($validatedData['decision'] === 'approved') {
            $serviceRequest->status = 'APPROVED_BY_HOD';
        } else {
            $serviceRequest->status = 'REJECTED_BY_HOD';
        }
        $serviceRequest->updated_at = now();
        $serviceRequest->save();

        // Notify the requester about the approval decision
        $requester = User::where('emp_code', $serviceRequest->requester_id)->first();
        if ($requester) {
            $requester->notify(new RequestApprovalNotification($serviceRequest));
        }

        $itManager = getItManagerCode();
        if ($itManager) {
            $itManagerUser = User::where('emp_code', $itManager)->first();
            if ($itManagerUser) {
                $itManagerUser->notify(new ServiceRequestToItNotification($serviceRequest));
            }
        }

        return redirect()->route('service-requests.show', $id)->with('success', 'Service request updated successfully.');
    }

    public function assignment($id)
    {
        if(auth()->user()->emp_code === getItManagerCode()){
            $request = ServiceRequest::with('approvals')->findOrFail($id);
            if(!$request) {
                return redirect()->back()->with('error', 'Service request not found.');
            }
            $approval = $request->approvals->first();
            $team = auth()->user()->teamMembers->pluck('emp_code_l');
            $users = User::select('emp_code', 'name')
                ->whereIn('emp_code', $team)
                ->whereNull('quit_stat')
                ->get(); 
        }
        else {
            return redirect()->route('service-requests.index')->with('error', 'You are not authorized to view this assignment.');
        }

        return view('service_requests.assignment', compact('request', 'approval', 'users'));
    }

    public function approveAssignment(Request $request, $id)
    {
        $validatedData = $request->validate([
            'team_emp_code' => 'required|integer',
            'approval_remarks' => 'required|string|max:1000',
            'expected_completion_date' => 'required|date|after_or_equal:today',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($id);
        if(!$serviceRequest) {
            return redirect()->back()->with('error', 'Service request not found.');
        }

        // Create a new request approval for IT Manager
        $requestApproval = new RequestApproval;
        $requestApproval->id = getIncrementedId('REQUEST_APPROVALS', 'id');
        $requestApproval->service_request_id = $id;
        $requestApproval->approved_by = auth()->user()->emp_code;
        $requestApproval->status = 'approved';
        $requestApproval->remarks = $validatedData['approval_remarks'];
        $requestApproval->role = 'IT_HOD';
        $requestApproval->approval_date = now();
        $requestApproval->save();

        // Update the service request status
        $serviceRequest->status = 'ASSIGNED_TO_IT';
        $serviceRequest->updated_at = now();
        $serviceRequest->save();

        // Create a new request assignment
        $requestAssignment = new RequestAssignment;
        $requestAssignment->id = getIncrementedId('REQUEST_ASSIGNMENTS', 'id');
        $requestAssignment->service_request_id = $id;
        $requestAssignment->assigned_to = $validatedData['team_emp_code'];
        $requestAssignment->assigned_by = auth()->user()->emp_code;
        $requestAssignment->remarks = $validatedData['approval_remarks'];
        $requestAssignment->expected_completion_date = $validatedData['expected_completion_date'];
        $requestAssignment->assigned_at = now();
        $requestAssignment->save();

        // Notify the assigned user about the assignment
        $assignedUser = User::where('emp_code', $validatedData['team_emp_code'])->first();
        if ($assignedUser) {
            $assignedUser->notify(new NewServiceRequestAssigned($serviceRequest));
        }
        // Notify the requester & his HOD about the assignment
        $hodApproval = RequestApproval::where('service_request_id', $id)
            ->where('role', 'HOD')
            ->first();
        if ($hodApproval) {
            $hod = User::where('emp_code', $hodApproval->approved_by)->first();
            if ($hod) {
                $hod->notify(new ServiceRequestAssignedToIt($serviceRequest));
            }
            $requster = User::where('emp_code', $serviceRequest->requester_id)->first();
            if ($requster) {
                $requster->notify(new ServiceRequestAssignedToIt($serviceRequest));
            }
        }

        return redirect()->route('service-requests.show', $id)->with('success', 'Service request assigned successfully.');
    }

    public function rejectAssignment(Request $request, $id)
    {
        $validatedData = $request->validate([
            'rejection_remarks' => 'required|string|max:1000',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($id);
        if(!$serviceRequest) {
            return redirect()->back()->with('error', 'Service request not found.');
        }

        // Create a new request approval for IT Manager
        $requestApproval = new RequestApproval;
        $requestApproval->id = getIncrementedId('REQUEST_APPROVALS', 'id');
        $requestApproval->service_request_id = $id;
        $requestApproval->approved_by = auth()->user()->emp_code;
        $requestApproval->status = 'rejected';
        $requestApproval->remarks = $validatedData['rejection_remarks'];
        $requestApproval->role = 'IT_HOD';
        $requestApproval->approval_date = now();
        $requestApproval->save();

        // Update the service request status
        $serviceRequest->status = 'REJECTED_BY_IT_HOD';
        $serviceRequest->updated_at = now();
        $serviceRequest->save();

        // Notify the requester & his HOD about the rejection
        $hodApproval = RequestApproval::where('service_request_id', $id)
            ->where('role', 'HOD')
            ->first();
        if ($hodApproval) {
            $hod = User::where('emp_code', $hodApproval->approved_by)->first();
            if ($hod) {
                $hod->notify(new ServiceRequestRejectionByIt($serviceRequest));
            }
            $requster = User::where('emp_code', $serviceRequest->requester_id)->first();
            if ($requster) {
                $requster->notify(new ServiceRequestRejectionByIt($serviceRequest));
            }
        }

        return redirect()->route('service-requests.show', $id)->with('success', 'Service request rejected successfully.');
    }

    public function assignmentDetails($requestId)
    {
        $assignment = RequestAssignment::with('request', 'approvals')
            ->where('service_request_id', $requestId)
            ->first();
        $requesterName = $this->requesterName($assignment->request);
        $assignment->request->requester_name = $requesterName;
        $departmentName = $this->departmentName($assignment->request);
        $assignment->request->department_name = $departmentName;   
        if(!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }
        return view('service_requests.assignment-details', compact('assignment'));
    }

    public function requesterName($request)
    {
        $requester_name = User::select('name')->where('emp_code',$request->requester_id)->first();
        return $requester_name ? $requester_name->name : 'Unknown Requester';
    }

    public function departmentName($request)
    {
        $departmentName = $request->department->dept_desc;
        return $departmentName ? $departmentName : 'Unknown Department';
    }

    public function addUpdate(Request $request, $id)
    {
        $validatedData = $request->validate([
            'progress_status' => 'required|string|in:hold,completed,in-progress',
            'comments' => 'required|string|max:1000',
        ]);

        $assignment = RequestAssignment::findOrFail($id);
        if(!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        $update = new RequestUpdate;
        $update->comments = $validatedData['comments'];
        $update->progress_status = $validatedData['progress_status'];
        $update->id = getIncrementedId('REQUEST_UPDATES', 'id');
        $update->assignment_id = $id;
        $update->updated_by = auth()->user()->emp_code;
        $update->updated_at = now();
        $update->save();

        if(!$update) {
            return redirect()->back()->with('error', 'Failed to add update.');
        }
        // Notify the IT HOD about the update OF TEAM MEMBER

        $itHod = User::where('emp_code', $assignment->assigned_by)->first();
        if ($itHod) {
            $itHod->notify(new ServiceUpdateFromIt($assignment->request));
        }

        // Notify the requester user about the update
        $requester = User::where('emp_code', $assignment->request->requester_id)->first();
        if ($requester) {
            $requester->notify(new ServiceUpdateFromIt($assignment->request));
        }

        // Notify the requester's HOD about the update
        $hod = User::where('emp_code', hisBoss($assignment->request->requester_id))->first();
        if ($hod) {
            $hod->notify(new ServiceUpdateFromIt($assignment->request));
        }

        return redirect()->route('service-requests.assignment-details', ['requestId' => $assignment->service_request_id])
            ->with('success', 'Assignment updated successfully.');
    }
}
