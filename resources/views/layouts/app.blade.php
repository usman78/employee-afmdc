<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <title>AFMDC Employee Portal</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta content="AFMDC Employee Portal" name="description">
        <meta content="AFMDC, Employee Portal, Employee Management" name="keywords">

        <!-- Favicons -->
        <link href="{{asset("/img/AFMDC-Logo.png")}}" rel="icon">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com" rel="preconnect">
        <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

        <!-- Vendor CSS Files -->
        <link href="{{asset('vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
        <link href="{{asset('vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link href="{{asset('css/main.css')}}" rel="stylesheet">
        @stack('cdn-styles')
            <!-- Main CSS File -->
            
            <style>
                .btn-primary {
                    --bs-btn-bg: #2196f3;
                }
                .navmenu .dropdown.d-xl-none {
                    display: inline-block;
                }
                .header .header-social-links .dropdown {
                    display: inline-block;
                }
                .header .header-social-links .dropdown a.btn.dropdown-toggle {
                    border: none;
                } 
                .navmenu a.dropdown-toggle {
                    padding: 10px 0;
                }
                .navmenu .dropdown .btn.dropdown-toggle {
                    border: none;
                }
                .navmenu .dropdown .dropdown-toggle::after {
                    display: none;
                }
                span.position-absolute.top-0.start-100.translate-middle.badge.rounded-pill.bg-danger
                {
                    font-size: x-small;
                    transform: translate(-175%, -20%) !important;
                }
                .thick-underline {
                    text-decoration-line: underline;
                    text-decoration-thickness: 2px; /* Can also use 'from-font', 'auto', or specific units */
                    text-decoration-color: #973594;
                    text-underline-offset: 4px;
                    color: #973594;
                    font-weight: 600;
                }
                .thick-underline:hover {
                    text-decoration-line: underline;
                    text-decoration-thickness: 2px; /* Can also use 'from-font', 'auto', or specific units */
                    text-decoration-color: #973594;
                    text-underline-offset: 4px;
                    color: #2196f3;
                    font-weight: 600;
                }
                table.table thead tr th {
                /* color: #2196F3; */
                }
                .table thead {
                    --bs-table-bg: #2196f3;
                    --bs-table-color: #fff;
                }
                .header {
                    background-color: #c0ddff;
                }
                .header::after {
                    content: "";
                    position: absolute;
                    height: 3%;
                    padding: 2px 0;
                    width: 100%;
                    background: #9C27B0;
                    left: 50%;
                    top: 0;
                    translate: -50% -50%;
                    z-index: -99999999999;
                }
                li.nav-item {
                    margin-bottom: 0;
                }
                .header .header-social-links a:hover {
                    color: #973594;
                }
                .header .header-social-links a
                {
                    font-size: 16px;
                } 
                .section-title h2:after
                {
                    background: #973594;
                }
                .accordion-body {
                    background-color: aliceblue;
                }
                @media (max-width: 768px) {
                    .table {
                    display: block;
                    width: 100%;
                    overflow-x: auto;
                    white-space: nowrap;
                    }
                    @media (max-width: 1199px) {
                        .navmenu a:hover, .navmenu .active, .navmenu .active:focus {
                            color: #973594;
                        }
                    }
                }
                @media (min-width: 1200px) {
                    .navmenu li:hover>a, .navmenu .active, .navmenu .active:focus {
                        color: #973594;
                    }
                    .navmenu>ul>li>a:before {
                    background-color: #973594;
                    }
                    .navmenu a:hover:before, .navmenu li:hover>a:before, .navmenu .active:before {
                    width: 100%;
                    }
                    .navmenu a, .navmenu a:focus {
                    color: #353636;
                    font-size: 15px;
                    font-weight: 700;
                    }
                }
            @stack('styles');
        </style>
    </head>
    <body id="page-top" class="index-page">
        <div id="wrapper">
            <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
                {{-- Brand --}}
                <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
                    <div class="sidebar-brand-icon">
                        <img style="width:40px;height:40px;" src="{{ asset('/img/AFMDC-Logo.png') }}" alt="AFMDC Logo">
                    </div>
                    <div class="sidebar-brand-text mx-1">AFMDC e-Portal</div>
                </a>
                <hr class="sidebar-divider my-0">
                {{-- Dashboard --}}
                <li @class(['nav-item', 'active' => request()->routeIs('home')])>
                    <a class="nav-link" href="{{ route('home') }}">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">
                    Employee
                </div>
                {{-- Attendance --}}
                <li @class(['nav-item', 'active' => request()->routeIs('attendance')])>
                    <a class="nav-link" href="{{ route('attendance', $emp_code) }}">
                        <i class="fas fa-fw fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                {{-- Leaves --}}
                <li @class([
                    'nav-item',
                    'active' => in_array(request()->route()->getName(), ['leaves', 'apply-leave-advance'])
                ])>
                    <a class="nav-link" href="{{ route('leaves', $emp_code) }}">
                        <i class="fas fa-fw fa-plane-departure"></i>
                        <span>Leaves</span>
                    </a>
                </li>
                {{-- Leave Approvals (Boss only) --}}
                @if(Auth::user()->isBoss())
                    <li @class(['nav-item', 'active' => request()->routeIs('leave-approvals')])>
                        <a class="nav-link" href="{{ route('leave-approvals', $emp_code) }}">
                            <i class="fas fa-fw fa-check-circle"></i>
                            <span>Leave Approvals</span>
                        </a>
                    </li>
                @endif
                {{-- Forms --}}
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseForms">
                        <i class="fas fa-fw fa-file-alt"></i>
                        <span>Forms</span>
                    </a>
                    <div id="collapseForms"
                        class="collapse {{ in_array(request()->route()->getName(), ['travel-forms','expense-forms','loan-forms']) ? 'show' : '' }}"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <a class="collapse-item {{ in_array(request()->route()->getName(), ['exit-interview.create']) ? 'active' : '' }}" href="{{ route('exit-interview.create', $emp_code) }}">Exit Interview Form</a>
                            {{-- <a class="collapse-item {{ in_array(request()->route()->getName(), ['expense-forms']) ? 'active' : '' }}" href="{{ route('expense-forms', $emp_code) }}">Expense Forms</a> --}}
                            {{-- <a class="collapse-item {{ in_array(request()->route()->getName(), ['loan-forms']) ? 'active' : '' }}" href="{{ route('loan-forms', $emp_code) }}">Loan Forms</a> --}}
                        </div>
                    </div>
                </li>
                {{-- Reports --}}
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReports">
                        <i class="fas fa-fw fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                    <div id="collapseReports"
                        class="collapse {{ in_array(request()->route()->getName(), ['attendance-report','leave-report', 'admissions', 'inventory']) ? 'show' : '' }}"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            @if (Auth::user()->isHR())
                                <a class="collapse-item {{ in_array(request()->route()->getName(), ['leave-report']) ? 'active' : '' }}" href="{{ route('leave-report') }}">Leave Report</a>
                                <a class="collapse-item {{ in_array(request()->route()->getName(), ['exit-interview.report']) ? 'active' : '' }}" href="{{ route('exit-interview.report') }}">Exit Interview Reports</a>
                            @endif
                            @if (Auth::user()->isAllowedToSeeAdmissions())
                                <a class="collapse-item {{ in_array(request()->route()->getName(), ['admissions']) ? 'active' : '' }}" href="{{ route('admissions') }}">Admissions Report</a>
                            @endif
                            <a class="collapse-item {{ in_array(request()->route()->getName(), ['inventory']) ? 'active' : '' }}" href="{{ route('inventory', $emp_code) }}">Store Issuance Report</a>
                            
                        </div>
                    </div>
                </li>
                {{-- Team (Boss only) --}}
                @if(Auth::user()->isBoss())
                    <li @class([
                        'nav-item',
                        'active' => in_array(request()->route()->getName(), ['team', 'attendance-filter'])
                    ])>
                        <a class="nav-link" href="{{ route('team', $emp_code) }}">
                            <i class="fas fa-fw fa-users"></i>
                            <span>Team</span>
                        </a>
                    </li>
                @endif
                <hr class="sidebar-divider">
                <div class="sidebar-heading">
                    Work
                </div>
                {{-- Tasks / SOPs / Meetings --}}
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTasks">
                        <i class="fas fa-fw fa-tasks"></i>
                        <span>Meeting & Tasks</span>
                    </a>
                    <div id="collapseTasks"
                        class="collapse {{ in_array(request()->route()->getName(), ['tasks','meetings','assigned-tasks','sops',]) ? 'show' : '' }}"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <a class="collapse-item {{ in_array(request()->route()->getName(), ['meetings']) ? 'active' : '' }}" href="{{ route('meetings') }}">Meetings</a>
                            <a class="collapse-item {{ in_array(request()->route()->getName(), ['tasks','assigned-tasks']) ? 'active' : '' }}" href="{{ route('assigned-tasks') }}">Assigned Tasks</a>
                            <a class="collapse-item {{ in_array(request()->route()->getName(), ['sops']) ? 'active' : '' }}" href="{{ route('sops') }}">SOPs</a>
                        </div>
                    </div>
                </li>
                {{-- Timetable --}}
                @if(Auth::user()->isStudentAffairs())
                    <li @class([
                        'nav-item',
                        'active' => in_array(request()->route()->getName(), [
                            'timetables.index',
                            'timetables.show',
                            'timetables.new-timetable',
                            'timetables.create'
                        ])
                    ])>
                        <a class="nav-link" href="{{ route('timetables.index') }}">
                            <i class="fas fa-fw fa-clock"></i>
                            <span>Timetable</span>
                        </a>
                    </li>
                @endif
                {{-- Service Requests --}}
                <li @class([
                    'nav-item',
                    'active' => in_array(request()->route()->getName(), [
                        'service-requests.index',
                        'service-requests.show',
                        'service-requests.assign'
                    ])
                ])>
                    <a class="nav-link" href="{{ route('service-requests.index') }}">
                        <i class="fas fa-fw fa-tools"></i>
                        <span>Service Requests</span>
                    </a>
                </li>
                {{-- Jobs Bank --}}
                @if(Auth::user()->isHR())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('job-dashboard', $emp_code) }}" target="_blank">
                            <i class="fas fa-fw fa-briefcase"></i>
                            <span>Jobs Bank</span>
                        </a>
                    </li>
                @endif
                <hr class="sidebar-divider d-none d-md-block">
                {{-- Sidebar Toggle --}}
                <div class="text-center d-none d-md-inline">
                    <button class="rounded-circle border-0" id="sidebarToggle"></button>
                </div>
            </ul>
            <!-- Content Wrapper -->
            <div id="content-wrapper" class="d-flex flex-column">
                <!-- Main Content -->
                <div id="content">
                    <!-- Topbar -->
                    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                        <!-- Sidebar Toggle (Topbar) -->
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>
                        <!-- Topbar Navbar -->
                        <ul class="navbar-nav ml-auto">
                            <!-- Nav Item - Alerts -->
                            <li class="nav-item dropdown no-arrow mx-1">
                                <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-bell fa-fw"></i>
                                    <!-- Counter - Alerts -->
                                    {{-- <span class="badge badge-danger badge-counter">3+</span> --}}
                                </a>
                                <!-- Dropdown - Alerts -->
                                <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                    aria-labelledby="alertsDropdown">
                                    <h6 class="dropdown-header">
                                        Alerts Center
                                    </h6>
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-primary">
                                                <i class="fas fa-file-alt text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="small text-gray-500">December 12, 2019</div>
                                            <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                        </div>
                                    </a>
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-success">
                                                <i class="fas fa-donate text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="small text-gray-500">December 7, 2019</div>
                                            $290.29 has been deposited into your account!
                                        </div>
                                    </a>
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-warning">
                                                <i class="fas fa-exclamation-triangle text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="small text-gray-500">December 2, 2019</div>
                                            Spending Alert: We've noticed unusually high spending for your account.
                                        </div>
                                    </a>
                                    <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                                </div>
                            </li>
                            <div class="topbar-divider d-none d-sm-block"></div>
                            <!-- Nav Item - User Information -->
                            <li class="nav-item dropdown no-arrow">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ capitalizeWords($user_name) }}</span>
                                    <img class="img-profile rounded-circle"
                                        src="{{ asset('pictures') . '/' . getProfilePicName($emp_code) }}">
                                </a>
                                <!-- Dropdown - User Information -->
                                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                    aria-labelledby="userDropdown">
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Profile
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('change-password') }}">
                                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Change Password
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    style="cursor: pointer;" data-toggle="modal" data-target="#logoutModal">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </nav>
                    <!-- End of Topbar -->
                    <!-- Begin Page Content -->
                    <div class="container-fluid">
                        <main class="main">
                            <!-- Main Content Section -->
                            @yield('content')
                        </main>  
                    </div>
                    <!-- /.container-fluid -->
                </div>
                <!-- End of Main Content -->
                <!-- Footer -->
                <footer class="sticky-footer bg-white">
                    <div class="container my-auto">
                        <div class="copyright text-center my-auto">
                            <span>Copyright &copy; AFMDC 2026</span>
                        </div>
                    </div>
                </footer>
                <!-- End of Footer -->
            </div>
            <!-- End of Content Wrapper -->
        </div>
        <!-- Scroll Top -->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
        <script src="{{asset('js/sweetalert2.all.min.js')}}"></script>
        <!-- SB Admin 2 JS Files -->
        <script src="{{ asset('sb/vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('sb/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <!-- Core plugin JavaScript-->
        <script src="{{ asset('sb/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
        <!-- Custom scripts for all pages-->
        <script src="{{ asset('sb/js/sb-admin-2.min.js') }}"></script>
        <!-- SB Admin 2 JS Files End-->
        <!-- Date Range Picker -->
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        @stack('cdn-scripts')
        <script>
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            })
            if ($(window).width() < 768) {
                $('#accordionSidebar').addClass('toggled');
            }
            @stack('scripts');
        </script>
    </body>
</html>