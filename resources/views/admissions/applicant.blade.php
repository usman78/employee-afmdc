@extends('layouts.app')
@push('cdn-styles')
    <link rel="stylesheet" href="{{ asset('css/photoviewer.css') }}">
    <link href="{{ asset('css/templatemo-first-portfolio-style.css')}}" rel="stylesheet">
@endpush
@push('styles')
    main {
        margin: 50px 0;
    }
    .profile-small-title {
        color: #294a70;
    }
    .services-thumb {
        border: 2px solid var(--border-color);
    }
    .alert.alert-warning {
        line-height: 3.3;
    }
@endpush
@section('content')
    {{-- {{ dd($filePaths) }} --}}
    <div class="container">
        <div class="row mt-5">
            <div class="col-lg-6">
                <div class="services-thumb">
                    <div class="d-flex flex-wrap align-items-center mb-4 pb-3">
                        <h3 class="mb-0">Admission Application Documents</h3>
                    </div>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Applicant Profile</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;">
                        @if($filesAvailable['profile'])
                            <a data-gallery="manual" class="custom-btn btn"
                            href="{{ route('download-admission-file', [
                                    'id' => $profile->adm_applicant_id,
                                    'fileName' => 'profile',
                                    'fileFormat' => $filesAvailable[$filePaths['profile']] ?? 'jpg'
                            ]) }}"
                            target="_blank">View Profile</a>
                        @else
                            <span class="alert alert-warning">Profile (Not Uploaded)</span>
                        @endif
                    </p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Applicant CNIC</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;">
                        @if($filesAvailable['cnic_front'])
                            <a data-gallery="manual" class="custom-btn btn"
                            href="{{ route('download-admission-file', [
                            'id' => $profile->adm_applicant_id, 
                            'fileName' => 'cnic_front', 
                            'fileFormat' => $filesAvailable[$filePaths['cnic_front']] ?? 'jpg'
                            ]) }}"
                            target="_blank">CNIC Front</a>
                        @else
                            <span class="alert alert-warning">CNIC Front (Not Uploaded)</span>
                        @endif
                    </p>
                    <p class="mb-1" style="display: inline-block;">
                        @if($filesAvailable['cnic_back'])
                            <a data-gallery="manual" class="custom-btn btn"
                            href="{{ route('download-admission-file', [
                            'id' => $profile->adm_applicant_id, 
                            'fileName' => 'cnic_back', 
                            'fileFormat' => $filesAvailable[$filePaths['cnic_back']] ?? 'jpg'
                            ]) }}"
                            target="_blank">CNIC Back</a>
                        @else
                            <span class="alert alert-warning">CNIC Back (Not Uploaded)</span>
                        @endif
                    </p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Father CNIC</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;">
                        @if($filesAvailable['fr_cnic_front'])
                            <a data-gallery="manual" class="custom-btn btn"
                            href="{{ route('download-admission-file', [
                            'id' => $profile->adm_applicant_id, 
                            'fileName' => 'fr_cnic_front', 
                            'fileFormat' => $filesAvailable[$filePaths['fr_cnic_front']] ?? 'jpg'
                            ]) }}"
                            target="_blank">CNIC Front</a>
                        @else
                            <span class="alert alert-warning">CNIC Front (Not Uploaded)</span>  
                        @endif
                    </p>
                    <p class="mb-1" style="display: inline-block;">
                        @if($filesAvailable['fr_cnic_back'])
                            <a data-gallery="manual" class="custom-btn btn"
                            href="{{ route('download-admission-file', [
                            'id' => $profile->adm_applicant_id, 
                            'fileName' => 'fr_cnic_back', 
                            'fileFormat' => $filesAvailable[$filePaths['fr_cnic_back']] ?? 'jpg'
                            ]) }}"
                            target="_blank">CNIC Back</a>
                        @else
                            <span class="alert alert-warning">CNIC Back (Not Uploaded)</span>
                        @endif
                    </p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Educational Documents</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;">
                        @if($filesAvailable['matric'])
                            <a data-gallery="manual" class="custom-btn btn" href="{{ route('download-admission-file', [
                            'id' => $profile->adm_applicant_id, 
                            'fileName' => 'matric', 
                            'fileFormat' => $filesAvailable[$filePaths['matric']] ?? 'jpg'
                            ]) }}" target="_blank">Matric Result Card</a>  
                        @else
                            <span class="alert alert-warning">Matric Result Card (Not Uploaded)</span>
                        @endif
                    </p>
                    <p class="mb-1" style="display: inline-block;">
                        @if($filesAvailable['fsc'])
                            <a data-gallery="manual" class="custom-btn btn" href="{{ route('download-admission-file', [
                            'id' => $profile->adm_applicant_id, 
                            'fileName' => 'fsc', 
                            'fileFormat' => $filesAvailable[$filePaths['fsc']] ?? 'jpg'
                            ]) }}" target="_blank">Inter Result Card</a>   
                        @else
                            <span class="alert alert-warning">Inter Result Card (Not Uploaded)</span>
                        @endif
                    </p> 
                    
                    @if($filesAvailable['mcat_result'])
                        <p class="mb-1" style="display: inline-block;"></p>
                            <a data-gallery="manual" class="custom-btn btn" href="{{ route('download-admission-file', [
                            'id' => $profile->adm_applicant_id, 
                            'fileName' => 'mcat_result', 
                            'fileFormat' => $filesAvailable[$filePaths['mcat_result']] ?? 'jpg'
                            ]) }}" target="_blank">MCAT Result</a>  
                        </p>
                    @endif
                    
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Other Documents</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;">
                        @if($filesAvailable['bank_receipt'])
                            <a data-gallery="manual" class="custom-btn btn" href="{{ route('download-admission-file', [
                            'id' => $profile->adm_applicant_id, 
                            'fileName' => 'bank_receipt', 
                            'fileFormat' => $filesAvailable[$filePaths['bank_receipt']] ?? $fileFormat
                            ]) }}" target="_blank">Bank Receipt</a>  
                        @else
                            <span class="alert alert-warning">Bank Receipt (Not Uploaded)</span>
                        @endif
                    </p>
                    <p class="mb-1" style="display: inline-block;">
                        @if($filesAvailable['domicel'])
                            <a data-gallery="manual" class="custom-btn btn" href="{{ route('download-admission-file', [
                            'id' => $profile->adm_applicant_id,
                            'fileName' => 'domicel', 
                            'fileFormat' => $filesAvailable[$filePaths['domicel']] ?? $fileFormat
                            ]) }}" target="_blank">Domicile</a>
                        @else
                            <span class="alert alert-warning">Domicile (Not Uploaded)</span>
                        @endif
                    </p>     
                </div>
            </div>
            <div class="col-lg-6">
                <div class="services-thumb">
                    <div class="d-flex flex-wrap align-items-center mb-4 pb-3">
                        <h3 class="mb-0">Admission Application Data</h3>
                    </div>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Applicant Name</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->user->name }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Program Applied</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->program->program_name }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">UHS ID</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->uhs_id ?? 'N/A' }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Applicant Father Name</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->father_name }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Date of Birth</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ dateFormat($profile->date_of_birth) }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Gender</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->gender_label }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">City</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->city }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Address</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->postal_address }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Student Mobile</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->st_mobile_phone }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Father Mobile</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->fr_mobile_phone }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Accomodation</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->accomodation_label }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Emergency Contact</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->emg_cont_pname }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Emergency Contact Number</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->emg_cont_mno }}</p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h5 class="mt-2 d-block">Relation</h5>
                    </div>
                    <p class="mb-1" style="display: inline-block;"> {{ $profile->relation }}</p>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-auto" style="margin: 0 auto;">
                <a href="{{ route('admissions') }}" class="custom-btn btn back-btn">Back To Admissions</a>
            </div>
        </div>
    </div>
@endsection
@push('cdn-scripts')
    <script src="{{ asset('js/photoviewer.js') }}"></script>
@endpush
@push('scripts')
    $(document).ready(function () {
        $('[data-gallery=manual]').click(function (e) {
            e.preventDefault();

            var items = [];
            var clickedIndex = 0;

            $('[data-gallery=manual]').each(function (index) {
                let src = $(this).attr('href');
                items.push({
                    src: src
                });

                if ($(this).is(e.currentTarget)) {
                    clickedIndex = index;
                }
            });

            if (items.length === 0) {
                console.error('No items found for the gallery.');
                return;
            }

            if (clickedIndex < 0 || clickedIndex >= items.length) {
                console.error('Clicked index is out of bounds.');
                return;
            }

            let viewer = new PhotoViewer(items, {
                index: clickedIndex,
                footerToolbar: [
                    'zoomIn',
                    'zoomOut',
                    'prev',
                    'next',
                    'fullscreen',
                    'download',
                    'close'
                ],
                customButtons: {
                    download: {
                        text: '<i class="bi bi-download"></i>',
                        title: 'Download Image',
                        click: function (context) {
                            // Fallback to options.index if getIndex is not available
                            const currentIndex = context.index ?? context.options.index;
                            const currentItem = items[currentIndex];

                            if (!currentItem) {
                                console.error('Invalid item at index:', currentIndex);
                                return;
                            }

                            const imageUrl = currentItem.src;

                            const a = document.createElement('a');
                            a.href = imageUrl;
                            a.download = imageUrl.split('/').pop();
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                    }
                }
            });
        });
    });
    $('[data-cv=true]').click(function (e) {
        e.preventDefault();
        const pdfUrl = $(this).attr('href');
        window.open(pdfUrl);
    });
@endpush

