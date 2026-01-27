@php
    $baseDir = base_path('../online-admission/public/applications/' . $profile->adm_applicant_id . '/');
    $fileNameBase = 'profile_' . $profile->adm_applicant_id;

    $possibleExtensions = ['jpg', 'jpeg'];
    $photoPath = null;

    foreach ($possibleExtensions as $ext) {
        $fullPath = realpath($baseDir . $fileNameBase . '.' . $ext);
        if ($fullPath && file_exists($fullPath)) {
            // convert backslashes for DomPDF
            $photoPath = str_replace('\\', '/', $fullPath);
            break;
        }
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Application</title>
        <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            margin: 15px;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #777;
            padding-bottom: 10px;
        }

        .header-table td {
            padding: 10px;
            vertical-align: middle;
        }

        .left-space {
            width: 33%;
        }

        .logo-cell {
            width: 33%;
            text-align: center;
        }

        .photo-cell {
            width: 33%;
            text-align: right;
        }

        .logo-img {
            width: 100px;
            height: auto;
        }

        .photo-img {
            width: 100px;
            height: 130px;
            border: 1px solid #ccc;
            padding: 2px;
            object-fit: cover;
        }

        .title-cell {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            text-transform: uppercase;
            padding: 8px !important;
        }

        .subtitle-cell {
            text-align: center;
            font-size: 13px;
            padding: 5px !important;
        }

        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            page-break-after: avoid;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .data-table td {
            padding: 6px 8px;
            vertical-align: top;
            border: 1px solid #ddd;
        }

        .data-table .label {
            width: 35%;
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .data-table .value {
            width: 65%;
            background-color: #fff;
        }

        /* Education Details Table */
        .education-header {
            background-color: #e8e8e8;
            font-weight: bold;
            border: 1px solid #ddd;
            padding: 8px !important;
        }

        .education-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }

        .education-table td {
            padding: 5px 6px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .education-table .label {
            width: 20%;
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .education-table .value {
            width: 15%;
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
</style>

</head>
<body>

    <!-- ===== Header Section ===== -->
    <table class="header-table">
        <tr>
            <!-- Left Spacer -->
            <td class="left-space"></td>

            <!-- Center Logo -->
            <td class="logo-cell">
                <img src="{{ public_path('img/AFMDC-Logo.png') }}" class="logo-img">
            </td>

            <!-- Student Photo -->
            <td class="photo-cell">
                @if($photoPath)
                    <img src="{{ $photoPath }}" class="photo-img" alt="profile photo">
                @else
                    <div style="width: 100px; height: 130px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; background: #f5f5f5; font-size: 10px; color: #999;">No Photo</div>
                @endif
            </td>
        </tr>

        <!-- Title Row -->
        <tr>
            <td colspan="3" class="title-cell">
                ONLINE ADMISSION APPLICATION
            </td>
        </tr>
        <tr>
            <td colspan="3" class="subtitle-cell">
                Applied for: {{ $profile->program->program_name }}
            </td>
        </tr>
    </table>

    <!-- ===== Applicant Details ===== -->
    <div class="section-title">Applicant Information</div>

    <table class="data-table">
        <tr>
            <td class="label">Applicant Name</td>
            <td class="value">{{ $profile->user->name }}</td>
        </tr>

        <tr>
            <td class="label">Father Name</td>
            <td class="value">{{ $profile->father_name }}</td>
        </tr>

        <tr>
            <td class="label">Date of Birth</td>
            <td class="value">{{ dateFormat($profile->date_of_birth) }}</td>
        </tr>

        <tr>
            <td class="label">Gender</td>
            <td class="value">{{ $profile->gender_label }}</td>
        </tr>

        <tr>
            <td class="label">City</td>
            <td class="value">{{ $profile->city }}</td>
        </tr>

        <tr>
            <td class="label">Address</td>
            <td class="value">{{ $profile->postal_address }}</td>
        </tr>

        <tr>
            <td class="label">Student Mobile</td>
            <td class="value">{{ $profile->st_mobile_phone }}</td>
        </tr>

        <tr>
            <td class="label">Father Mobile</td>
            <td class="value">{{ $profile->fr_mobile_phone }}</td>
        </tr>

        <tr>
            <td class="label">Program Applied</td>
            <td class="value">{{ $profile->program->program_name }}</td>
        </tr>

        <tr>
            <td class="label">UHS ID</td>
            <td class="value">{{ $profile->uhs_id ?? 'N/A' }}</td>
        </tr>

        <tr>
            <td class="label">Accommodation Required</td>
            <td class="value">{{ $profile->accomodation_label }}</td>
        </tr>

        <tr>
            <td class="label">Emergency Contact</td>
            <td class="value">{{ $profile->emg_cont_pname }}</td>
        </tr>

        <tr>
            <td class="label">Emergency Contact Number</td>
            <td class="value">{{ $profile->emg_cont_mno }}</td>
        </tr>

        <tr>
            <td class="label">Relation</td>
            <td class="value">{{ $profile->relation }}</td>
        </tr>
    </table>

    <!-- ===== Education Details ===== -->
    <div class="section-title">Education Details</div>

    @php
        $weightSum = 0;
    @endphp
    @foreach ($profile->detail as $detail)
        @if($detail->sr_no == 1)
            <table class="education-table">
                <tr>
                    <td colspan="6" class="education-header">Matric</td>
                </tr>
                <tr>
                    <td class="label">Obtained Marks</td>
                    <td class="value">{{ $detail->obt_marks }}</td>
                    <td class="label">Total Marks</td>
                    <td class="value">{{ $detail->total_marks }}</td>
                    <td class="label">Percentage</td>
                    <td class="value">{{ round($detail->obt_marks / $detail->total_marks * 100) }}%</td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td class="label">Weightage (10%)</td>
                    <td class="value">{{ round((round($detail->obt_marks / $detail->total_marks * 100) * 0.1), 2) }}</td>
                </tr>
            </table>
            @php
                $weightSum += round($detail->obt_marks / $detail->total_marks * 100) * 0.1;
            @endphp
        @elseif($detail->sr_no == 2)
            <table class="education-table">
                <tr>
                    <td colspan="6" class="education-header">Intermediate</td>
                </tr>
                <tr>
                    <td class="label">Obtained Marks</td>
                    <td class="value">{{ $detail->obt_marks }}</td>
                    <td class="label">Total Marks</td>
                    <td class="value">{{ $detail->total_marks }}</td>
                    <td class="label">Percentage</td>
                    <td class="value">{{ round($detail->obt_marks / $detail->total_marks * 100) }}%</td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td class="label">Weightage (40%)</td>
                    <td class="value">{{ round((round($detail->obt_marks / $detail->total_marks * 100) * 0.4), 2) }}</td>
                </tr>
            </table>
            @php
                $weightSum += round($detail->obt_marks / $detail->total_marks * 100) * 0.4;
            @endphp
        @elseif($detail->sr_no == 3)
            <table class="education-table">
                <tr>
                    <td colspan="6" class="education-header">MCAT Results</td>
                </tr>
                <tr>
                    <td class="label">Obtained Marks</td>
                    <td class="value">{{ $detail->obt_marks }}</td>
                    <td class="label">Total Marks</td>
                    <td class="value">{{ $detail->total_marks }}</td>
                    <td class="label">Percentage</td>
                    <td class="value">{{ round($detail->obt_marks / $detail->total_marks * 100) }}%</td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td class="label">Weightage (50%)</td>
                    <td class="value">{{ round((round($detail->obt_marks / $detail->total_marks * 100) * 0.5), 2) }}</td>
                </tr>
            </table>
            @php
                $weightSum += round($detail->obt_marks / $detail->total_marks * 100) * 0.5;
            @endphp
        @endif
    @endforeach

    <!-- Total Weightage -->
    @if(count($profile->detail) > 0)
        <table class="education-table">
            <tr class="total-row">
                <td colspan="4"></td>
                <td class="label">Total Weightage</td>
                <td class="value">{{ round($weightSum, 2) }}</td>
            </tr>
        </table>
    @endif

</body>
</html>
