@php
    use App\Models\Employee;
@endphp
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>AFMDC Jobs</title>

        <!-- FONT FILES -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

        <link href="https://fonts.googleapis.com" rel="preconnect">

        <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>

        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

        <!-- CSS FILES -->

        <link href="{{asset('vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">

        <link href="{{asset('vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">

        <link href="{{asset('css/magnific-popup.css')}}" rel="stylesheet">

        <link href="{{ asset('css/templatemo-first-portfolio-style.css')}}" rel="stylesheet">

        <link rel="stylesheet" href="{{ asset('css/photoviewer.css') }}">

        <link href="{{ asset('css/sweetalert2.min.css')}}" rel="stylesheet">

        <link href="{{asset('css/main.css')}}" rel="stylesheet">

        <!-- SCRIPT FILES -->

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

        <!-- INLINE CSS -->

        <style>
            h1, h2 {
                letter-spacing: 0px;
            }
            .section-title h2:after,
            .stats .stats-item span:after {
                background: #973594;
            }
            .section-title-wrap {
                background-color: #294a70;
            }
            .avatar-image {
                background: floralwhite;
            }
            
            .section-padding {
                padding-top: 15px;
                padding-bottom: 120px;
            }

            .avatar-image {
                object-fit: contain;
            }

            .button {
                display: flex;
                justify-content: center;
                align-items: center;    
            }

            .dt-layout-row:first-child {
                display: flex;
                justify-content: flex-start;
                align-items: center;
                padding-top: 20px;
                padding-left: 10px;
            }

            .dt-layout-cell.dt-layout-start {
                display: none !important;
            }

            .dt-layout-cell.dt-layout-end {
                margin-bottom: 20px;
                margin-left: 20px;
                text-align: center;
            }

            button.dt-paging-button {
                border: 1px solid #294a70;
                margin: 0 5px;
                border-radius: 5px;
                color: #294a70;
                padding: 5px 15px;
            }

            button.dt-paging-button:hover {
                background-color: #294a70;
                color: #fff;
            }

            input#dt-search-0 {
                margin-left: 25px;
                border-radius: 5px;
                border: 1px solid #294a70;
                padding: 5px;
            }

            input#dt-search-0:focus-visible {
                outline: none;
            }

            .back-btn {
                border-color: var(--secondary-color);
                color: var(--secondary-color);
                background: transparent;
                border-width: 2px;
                padding: 8px 22px;
            }

            .back-btn:hover {
                background: var(--secondary-color);
                color: #fff;
            }
            
            #applicants-data_wrapper.dt-container {
                padding: 0 20px;
            }

            .profile-small-title {
                width: 250px;
            }

            .profile-title {
                background-color: white;
            }

            .profile-title h3 {
                color: 2196f3;
            }
               .btn-primary {
            --bs-btn-bg: #2196f3;
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
                color: #E91E63;
            }
            .header .header-social-links a
            {
                font-size: 20px;
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
                    color: #2196f3;
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

<body class="index-page">

  <header id="header" class="header d-flex align-items-center light-background sticky-top">
    <div class="container-fluid position-relative d-flex align-items-center justify-content-around">
      <div class="logo">
        <a href="/" class="logo d-flex align-items-center me-auto me-xl-0">
          <img src="{{asset('/img/AFMDC-Logo.png')}}" alt="">
          <h1 class="sitename">AFMDC Employee Portal</h1>
        </a>
      </div>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li class="nav-item"><a href="{{route('home')}}" @if (Route::currentRouteName() == 'home') class="active" @endif>Employee Portal</a></li>
          <li class="nav-item"><a href="{{route('job-dashboard')}}" @if(Route::currentRouteName() == 'job-dashboard') class="active" @endif>Job Dashboard</a></li>
          <li class="nav-item"><a href="{{route('job-bank')}}" @if(Route::currentRouteName() == 'job-bank' || Route::currentRouteName() == 'apply-leave-advance') class="active" @endif>Job Bank</a></li>
          <li class="nav-item"><a href="{{route('shortlisted')}}" @if(Route::currentRouteName() == 'shortlisted') class="active" @endif>Shortlisted</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
      <div class="header-social-links">
        <a href="#" class="facebook" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out"></i></a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
          @csrf
        </form>
      </div>
    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    @yield('content')

  </main>  

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
    <script src="{{asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('vendor/php-email-form/validate.js')}}"></script>
    <script src="{{asset('vendor/aos/aos.js')}}"></script>
    <script src="{{asset('vendor/waypoints/noframework.waypoints.js')}}"></script>
    <script src="{{asset('vendor/purecounter/purecounter_vanilla.js')}}"></script>
    <script src="{{asset('vendor/swiper/swiper-bundle.min.js')}}"></script>
    <script src="{{asset('vendor/glightbox/js/glightbox.min.js')}}"></script>
    <script src="{{asset('vendor/imagesloaded/imagesloaded.pkgd.min.js')}}"></script>
    <script src="{{asset('vendor/isotope-layout/isotope.pkgd.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/DataTables.js')}}"></script>
    <script src="{{asset('js/bootstrap.min.js')}}"></script>
    <script src="{{asset('js/jquery.sticky.js')}}"></script>
    <script src="{{asset('js/click-scroll.js')}}"></script>
    <script src="{{asset('js/jquery.magnific-popup.min.js')}}"></script>
    <script src="{{asset('js/magnific-popup-options.js')}}"></script>
    <script src="{{asset('js/custom.js')}}"></script>
    <script src="{{asset('js/photoviewer.js')}}"></script>
    <script src="{{asset('js/sweetalert2.all.min.js')}}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script> --}}

  <!-- Main JS File -->
  <script src="{{asset('js/main.js')}}"></script>

  <script>

    @stack('scripts');
  </script>


</body>

</html>      