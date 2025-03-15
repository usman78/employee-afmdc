<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Employee - AFMDC</title>
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

  <!-- Main CSS File -->
  <link href="{{asset('css/main.css')}}" rel="stylesheet">

  <style>
    .badge-success {
      background-color: #2196f3;
    }
    .badge-warning {
      background-color: #ff9800;
    }
    .table {
      border: 1px solid #ccc;
    } 
    .table>:not(caption)>*>* {
      padding: .5rem 2.5rem;
    }
    /* .paragraphs {
      margin-top: 50px;
    } */


    @stack('styles');
  </style>
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center light-background sticky-top">
    <div class="container-fluid position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center me-auto me-xl-0">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="assets/img/logo.png" alt=""> -->
        <h1 class="sitename">Employee AFMDC</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="{{route('home')}}">Home</a></li>
          <li><a id="attendance" class="active" href="{{route('attendance' , $attendance->emp_code)}}">Attendance</a></li>
          <li><a id="leaves" href="{{route('leaves', $attendance->emp_code)}}">Leaves</a></li>
          <li><a id="inventory" href="{{route('inventory')}}">Store Issue</a></li>
          <li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
          {{-- <li><a href="portfolio.html">Portfolio</a></li> --}}
          {{-- <li class="dropdown"><a href="#"><span>Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li><a href="#">Dropdown 1</a></li>
              <li class="dropdown"><a href="#"><span>Deep Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                <ul>
                  <li><a href="#">Deep Dropdown 1</a></li>
                  <li><a href="#">Deep Dropdown 2</a></li>
                  <li><a href="#">Deep Dropdown 3</a></li>
                  <li><a href="#">Deep Dropdown 4</a></li>
                  <li><a href="#">Deep Dropdown 5</a></li>
                </ul>
              </li>
              <li><a href="#">Dropdown 2</a></li>
              <li><a href="#">Dropdown 3</a></li>
              <li><a href="#">Dropdown 4</a></li>
            </ul>
          </li> --}}
          {{-- <li><a href="contact.html">Contact</a></li> --}}
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      {{-- <div class="header-social-links">
        <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
        <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
        <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
        <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
      </div> --}}
        {{-- <div class="social-links d-flex justify-content-center">
          <a href="">Logout</a>
        </div> --}}
        <div>

      </div>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="portfolio-details mt-5">
            <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
              <h3>Attendance information</h3>
              <ul>
                <li><strong>Employee Code: </strong>{{$attendance->emp_code}}</li>
                <li><strong>Employee Name: </strong>{{$attendance->emp_name}}</li>
                <li><strong>Last 30 attendance records.</li>
              </ul>
            </div>
          </div>
            <table class="table mt-5 mb-5">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendance as $att)
                        <tr>
                            <td>{{ date('d-m-Y',strtotime($att->at_date)) }}</td>
                            <td>{{ date('h:i A',strtotime($att->timein)) }}</td>
                            <td>
                              @if($att->timeout == null) <span class="badge badge-warning">Not Timed Out</span> @else {{date('h:i A',strtotime($att->timeout))}} @endif
                            </td>
                            <td>
                                @if($att->timein == null) <span class="badge badge-danger">Absent</span> @else <span class="badge badge-success">Present</span> @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
      </div>
    </div>

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

  <!-- Main JS File -->
  <script src="{{asset('js/main.js')}}"></script>

  <script>

    @stack('scripts');
  </script>


</body>

</html>




