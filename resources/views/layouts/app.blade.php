{{-- @php
    use App\Models\Employee;
@endphp --}}
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>AFMDC Employee Portal</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset('vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/aos/aos.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/swiper/swiper-bundle.min.css')}}" rel="stylesheet">
  <link href="{{asset('vendor/glightbox/css/glightbox.min.css')}}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- Date Range Picker -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <!-- Main CSS File -->
  <link href="{{asset('css/main.css')}}" rel="stylesheet">

  {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

  <style>
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
      color: #E91E63;
    }
    .header .header-social-links a
    {
      font-size: 20px;
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
          <li class="nav-item"><a href="{{route('home')}}" @if (Route::currentRouteName() == 'home') class="active" @endif>Home</a></li>
          <li class="nav-item"><a href="{{route('tasks')}}" @if(in_array(Route::currentRouteName(), ['tasks', 'meetings', 'sops', 'assigned-tasks'])) class="active" @endif>Tasks</a></li>
          <li class="nav-item"><a id="attendance" href="{{route('attendance', $emp_code)}}" @if(Route::currentRouteName() == 'attendance') class="active" @endif>Attendance</a></li>
          <li class="nav-item"><a id="leaves" href="{{route('leaves', $emp_code)}}" @if(in_array(Route::currentRouteName(), ['leaves' , 'apply-leave-advance'])) class="active" @endif>Leaves</a></li>
          @if (Auth::user()->isBoss())
            <li class="nav-item"><a href="{{route('leave-approvals', $emp_code)}}" @if(Route::currentRouteName() == 'leave-approvals') class="active" @endif>Leave Approvals</a></li>
          @endif
          <li class="nav-item"><a id="inventory" href="{{route('inventory', $emp_code)}}" @if(Route::currentRouteName() == 'inventory') class="active" @endif>Store Issue</a></li>
          @if (Auth::user()->isBoss())
            <li class="nav-item"><a href="{{route('team', $emp_code)}}" @if(in_array(Route::currentRouteName(), [ 'team', 'attendance-filter'])) class="active" @endif>Team</a></li>
          @endif
          @if (Auth::user()->isHR())
            <li class="nav-item"><a href="{{route('job-dashboard', $emp_code)}}" target="_blank">Jobs Bank</a></li>
          @endif
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
      <div class="header-social-links">
        <a href="#" data-toggle="tooltip" data-placement="right" title="Log Out" class="facebook" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out"></i></a>
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

  <!-- Main JS File -->
  <script src="{{asset('js/main.js')}}"></script>

  <script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
    @stack('scripts');
  </script>


</body>

</html>