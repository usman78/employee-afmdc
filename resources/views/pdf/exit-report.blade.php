@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Exit Interview - {{ $interview->user->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; font-size: 18px; }
        .header p { margin: 5px 0; font-weight: bold; }
        
        .section-title { background-color: #f2f2f2; padding: 5px; font-weight: bold; border: 1px solid #ccc; margin-top: 15px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.info-table td { padding: 5px; border: 1px solid #eee; }
        
        .rating-table { border: 1px solid #000; }
        .rating-table th, .rating-table td { border: 1px solid #000; padding: 6px; text-align: left; font-size: 11px; }
        .rating-table th { background-color: #f9f9f9; }

        .checkbox-list { margin-top: 10px; }
        .checkbox-item { display: inline-block; width: 48%; margin-bottom: 5px; font-size: 11px; }
        .check-box { font-family: DejaVu Sans; display: inline-block; width: 12px; height: 12px; border: 1px solid #000; margin-right: 5px; text-align: center; line-height: 10px; }
        
        .feedback-box { border: 1px solid #ccc; padding: 8px; min-height: 40px; background-color: #fafafa; margin-top: 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; border-top: 1px solid #ccc; padding-top: 5px; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <div class="header">
        <h2>AZIZ FATIMAH MEDICAL & DENTAL COLLEGE FAISALABAD</h2>
        <p>EXIT INTERVIEW FORM (CONFIDENTIAL)</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><strong>Employee Code:</strong></td>
            <td width="30%">{{ $interview->user->emp_code }}</td>
            <td width="20%"><strong>Employee Name:</strong></td>
            <td width="30%">{{ $interview->user->name }}</td>
        </tr>
        <tr>
            <td><strong>Current Designation:</strong></td>
            <td>{{ $interview->user->designation->desg_short ?? 'N/A' }}</td>
            <td><strong>Designation at Joining:</strong></td>
            <td>{{ designationAtJoining($interview->user->emp_code) }}</td>
        </tr>
        <tr>
            <td><strong>Department:</strong></td>
            <td>{{ $interview->user->department->dept_desc ?? 'N/A' }}</td>
            <td><strong>Reporting Officer:</strong></td>
            <td>{{ employeeName($interview->user->head_no) }}</td>
        </tr>
        <tr>
            <td><strong>Placement of Job:</strong></td>
            <td>{{ jobPlacement($interview->user->loca_code) }}</td>
            <td><strong>Date of Joining:</strong></td>
            <td>{{ dateFormat($interview->user->join_date) }}</td>
        </tr>
        <tr>
            <td><strong>Date of Leaving:</strong></td>
            <td>{{ dateFormat($interview->leave_date) }}</td>
            <td><strong>Total Period of Association:</strong></td>
            <td>
                @php
                    $diff = Carbon::parse($interview->user->join_date)->diff(Carbon::parse($interview->leave_date));
                @endphp
                {{ $diff->y }} years, {{ $diff->m }} months, {{ $diff->d }} days
            </td>
        </tr>
        <tr>
            <td><strong>Separation Type:</strong></td>
            <td>{{ $interview->separation_type }}</td>
            <td><strong>Date Filed:</strong></td>
            <td>{{ $interview->created_at->format('d-M-Y') }}</td>
        </tr>
    </table>

    <div class="section-title">Reasons for Leaving</div>
    <div class="checkbox-list">
        
        @foreach($reasons as $reason)
            <div class="checkbox-item">
                <span class="check-box">&#10004;</span> {{ $reason }}
            </div>
        @endforeach
    </div>

    <div class="section-title">Detailed Feedback</div>
    <p><strong>Circumstances that would have prevented departure:</strong></p>
    <div class="feedback-box">{{ $interview->prevented_departure ?: 'No response provided.' }}</div>

    <table style="margin-top: 10px;">
        <tr>
            <td width="50%" style="padding-right: 10px;">
                <strong>Liked Most:</strong>
                <div class="feedback-box">{{ $interview->liked_most ?: 'N/A' }}</div>
            </td>
            <td width="50%">
                <strong>Liked Least:</strong>
                <div class="feedback-box">{{ $interview->liked_least ?: 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Workload & Recommendation</div>
    <table class="info-table">
        <tr>
            <td><strong>Workload Rating:</strong></td>
            <td>{{ $interview->workload }}</td>
            <td><strong>Recommend Friend:</strong></td>
            <td>{{ $interview->recommend_friend }}</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <div class="section-title">Reporting Officer Evaluation</div>
    <table class="rating-table">
        <thead>
            <tr>
                <th width="60%">Attribute</th>
                <th>Rating</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roRatings as $attr => $val)
            <tr>
                <td>{{ $attr }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $val }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Company & Department Factors</div>
    <table class="rating-table">
        <thead>
            <tr>
                <th width="60%">Factor</th>
                <th>Rating</th>
            </tr>
        </thead>
        <tbody>
            @foreach($companyRatings as $factor => $val)
            <tr>
                <td>{{ $factor }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $val }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Suggestions for the Institution</div>
    <div class="feedback-box" style="min-height: 80px;">
        {{ $interview->suggestions ?: 'No suggestions provided.' }}
    </div>

    <div style="margin-top: 20px; padding: 10px; border: 1px dashed #000;">
        <strong>Permission to show to Reporting Officer:</strong> 
        {{ $interview->share_with_ro ? 'YES' : 'NO' }}
    </div>

    <div style="margin-top: 50px;">
        <table width="100%">
            <tr>
                <td width="40%" style="border-top: 1px solid #000; text-align: center;">Employee Signature</td>
                <td width="20%"></td>
                <td width="40%" style="border-top: 1px solid #000; text-align: center;">HR Manager Signature</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Generated by Employee Portal System | Aziz Fatimah Medical & Dental College 
    </div>

</body>
</html>