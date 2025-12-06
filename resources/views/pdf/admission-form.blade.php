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
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* border-bottom: 2px solid #777; */
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            width: 120px;
            height: 120px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 12px;
            padding-top: 45px;
            color: #999;
        }

        .title {
            text-align: center;
            flex: 1;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .photo {
            width: 120px;
            height: 150px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 12px;
            padding-top: 65px;
            color: #999;
        }

        .section-title {
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 8px;
            font-size: 16px;
            text-transform: uppercase;
            border-bottom: 1px solid #aaa;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .label {
            width: 30%;
            font-weight: bold;
            background: #f3f3f3;
            border: 1px solid #ddd;
        }

        .value {
            width: 70%;
            border: 1px solid #ddd;
        }
    .header-table {
        width: 100%;
        border-bottom: 2px solid #777;
        padding-bottom: 10px;
        margin-bottom: 20px;
        border-collapse: collapse;
    }

    .left-space {
        width: 33%;
    }

    .logo-cell {
        width: 33%;
        text-align: center;
        vertical-align: middle;
    }

    .photo-cell {
        width: 33%;
        text-align: right;
        vertical-align: middle;
    }

    .logo-img {
        width: 120px;
        height: auto;
    }

    .photo-img {
        width: 120px;
        height: 150px;
        object-fit: cover;
        border: 1px solid #ccc;
        padding: 3px;
    }

    .title-cell {
        text-align: center;
        font-weight: bold;
        font-size: 20px;
        text-transform: uppercase;
        padding-top: 10px;
    }
    .subtitle-cell {
        text-align: center;
        font-size: 16px;
        font-weight: normal;
    }
</style>

</head>
<body>

    <!-- ===== Header Section ===== -->
    <div class="header">
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
                    <img src="{{ $photoPath }}" style="width: 100px; height: 150px;" alt="profile photo">
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
    </div>


    <!-- ===== Applicant Details ===== -->
    <div class="section-title">Applicant Information</div>

    <table>
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

</body>
</html>
