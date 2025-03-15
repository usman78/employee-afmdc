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
    /* .border {
      border: var(--bs-border-width) var(--bs-border-style) #009688 !important;
    }  */

    img {
      max-width: 100px;
      border-radius: 50%;
      max-height: 100px;
      object-fit: cover;
      width: 100%;
      border: 5px solid #2196f3;
    }
    ul {
      list-style: none;
    }
    .resume .resume-item h4 {
      color: #2196f3;
      text-transform: none;
    }
    .resume .resume-item:not(:first-child) {
      margin-top: 20px;
    }
    strong {
      color: #150E56;
    }
    i.bi {
      color: #150E56;
    }
    .resume .resume-item::before {
      border: 2px solid #2196F3;
    }
    .resume .resume-item {
      border-left: 2px solid #2196F3;
    }
    .services .service-item p {
      font-size: 18px;
      color: #2196f3;
    }
    .services .service-item h3 {
      margin: 30px 0 15px 0;
    }
    .services .service-item {
      min-width: 300px;
    }
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
          <li><a href="{{route('home')}}" class="active">Home</a></li>
          <li><a id="attendance" href="{{route('attendance', $employee->emp_code)}}">Attendance</a></li>
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
      <div class="row gy-4 justify-content-center mt-5">
          <div class="services col-lg-4 justify-content-center d-flex">
            <div class="service-item item-cyan position-relative">
              <div class="icon">
                <img src="{{asset('pictures') . "/" . $employee->pic_name}}">
              </div>
              <a href="#" class="stretched-link">
                <h3>{{capitalizeWords($employee->name)}}</h3>
              </a>
              <p>{{capitalizeWords($employee->designation->desg_short)}}</p>
              {{-- <p style="visibility: hidden;"><em>from initial concept to final, polished deliverable.</em></p> --}}
  
            </div>
            {{-- <div class="service-item item-cyan position-relative">
              <div class="icon">
                <img src="{{asset('pictures') . "/" . $employee->pic_name}}" class="img-fluid" alt="">
              </div>
            </div> --}}
          </div>
          <div class="col-lg-8 content">
            {{-- <p class="fst-italic py-3">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
              magna aliqua.
            </p> --}}
            <div class="row resume">
              <div class="col-lg-6">
                <div class="resume-item pb-0">
                  <p>Name</p>
                  <h4>{{capitalizeWords($employee->name)}}</h4>
                  
                  {{-- <p><em>Innovative and deadline-driven Graphic Designer with 3+ years of experience designing and developing user-centered digital/print marketing material from initial concept to final, polished deliverable.</em></p> --}}
                  {{-- <ul>
                    <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Designation:</strong> <span>{{capitalizeWords($employee->designation->desg_short)}}</span></li>
                    <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Department:</strong> <span>{{capitalizeWords($employee->department->dept_desc)}}</span></li>
                    <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Phone:</strong> <span>{{$employee->phone}}</span></li>
                    <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>City:</strong> <span>{{capitalizeWords($employee->nic_iss_plc)}}</span></li>
                    <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Employee Code:</strong> <span>{{$employee->emp_code}}</span></li>
                    <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>CNIC:</strong> <span>{{$employee->nic_num}}</span></li>
                    <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>Email:</strong> <span>{{strtolower($employee->emp_email)}}</span></li>
                    <li class="mb-3"><i class="bi bi-chevron-right"></i> <strong>PMDC No.:</strong> <span>{{$employee->pmdc_no}}</span></li>
                  </ul> --}}
                </div>
                <div class="resume-item pb-0">
                  <p>Designation:</p>
                  <h4>{{capitalizeWords($employee->designation->desg_short)}}</h4>
                </div>
                <div class="resume-item pb-0">
                  <p>Employee Code</p>
                  <h4>{{$employee->emp_code}}</h4>
                </div>
                <div class="resume-item pb-0">
                  <p>Department:</p>
                  <h4>{{capitalizeWords($employee->department->dept_desc)}}</h4>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="resume-item pb-0">
                  <p>Mobile Number:</p>
                  <h4>{{capitalizeWords($employee->phone)}}</h4>
                </div>
                <div class="resume-item pb-0">
                  <p>Email Address:</p>
                  <h4>{{strtolower($employee->emp_email)}}</h4>
                </div>
                <div class="resume-item pb-0">
                  <p>CNIC Number:</p>
                  <h4>{{capitalizeWords($employee->nic_num)}}</h4>
                </div>
                <div class="resume-item pb-0">
                  <p>City:</p>
                  <h4>{{capitalizeWords($employee->nic_iss_plc)}}</h4>
                </div>
              </div>
            </div>
            {{-- <p class="py-3">
              Officiis eligendi itaque labore et dolorum mollitia officiis optio vero. Quisquam sunt adipisci omnis et ut. Nulla accusantium dolor incidunt officia tempore. Et eius omnis.
              Cupiditate ut dicta maxime officiis quidem quia. Sed et consectetur qui quia repellendus itaque neque.
            </p> --}}
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




