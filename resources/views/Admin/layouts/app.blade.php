<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{asset('admin/assets/images/logo.ico')}}">
  <title>@yield('title', 'My Backyard')</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!--===============================================================================================-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!--===============================================================================================-->
  <!-- Vendor CSS Files -->
  <!-- Bootstrap CSS -->

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <link href="{{asset('admin/asset/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{asset('admin/asset/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('admin/asset/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{asset('admin/asset/vendor/quill/quill.snow.css')}}" rel="stylesheet">
  <link href="{{asset('admin/asset/vendor/quill/quill.bubble.css')}}" rel="stylesheet">
  <link href="{{asset('admin/asset/vendor/remixicon/remixicon.css')}}" rel="stylesheet">


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.6.0/dist/js/bootstrap.min.js"></script>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.6.0/dist/js/bootstrap.min.js"></script>

  <!-- Template Main CSS File -->
  <link href="{{asset('admin/asset/css/style.css')}}" rel="stylesheet">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
  <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>



  <link rel="stylesheet" type="text/css" href="{{asset('admin/assets/css/main.css')}}">


  <!--===============================================================================================-->

  <!-- Include SweetAlert CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">

  <!-- Include SweetAlert JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />



  @yield('style')
  <style>
    .footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      height: 50px;
      /* Change this value according to your needs */
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
    }

    .auto-resize-textarea {
      resize: none;
      overflow: hidden;
      width: 100%;

    }

    /* Move the search box to the right */
    .dataTables_wrapper .dataTables_filter {
      float: right;
      text-align: right;
    }

    /* Set a specific width and height for the images */
    .small-img {
      width: 50px;
      /* Adjust as needed */
      height: 50px;
      /* Adjust as needed */
    }

    .toolTip {
      padding: 20px;
      background: #ccc;
      border-radius: 10px;
      font-size: 18px;
      transition: all 0.25s;
    }

    .brand-link .brand-image {
      float: left;
      line-height: .8;
      margin-left: 0.8rem;
      margin-right: 0.5rem;
      /*margin-top: 6px;*/
      max-height: 40px;
      width: auto;
    }

    .nav-pills .nav-link:not(.active):hover {
      color: #f0f2f5;
    }

    .nav_active {
      background-color: #6c757d;
      color: white;
    }


    .nav-link {
      display: block;
      padding: 0.5rem 0.5rem;
    }


    .circle {
      display: inline-block;
      width: 24px;
      /* Adjust size as needed */
      height: 22px;
      /* Adjust size as needed */
      background-color: #0d6efd;
      /* Change color as needed */
      color: white;
      font-size: 14px;
      border-radius: 50%;
      text-align: center;
      line-height: 22px;
      /* Should match height for centering */
      position: relative;
      top: 0;
      right: 0;
      margin-left: 140px;
    }





    .circle3 {
      font-size: 12px;
      text-align: justify;
      width: 30px;
      border-radius: 50px;
      margin-left: 155px;
      width: 100px;
      height: 20px;
      padding-left: 5px;
      padding-right: 5px;
      background-color: #0d6efd;
      color: white;
    }

    #loader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(255, 255, 255, 0.7);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }


    /* CSS to handle the toggled sidebar */


    .sidebar.active {
      left: 0;
      /* Displayed */
    }


    table.dataTable thead tr th .sorting,
    table.dataTable thead tr th .sorting_asc,
    table.dataTable thead tr th .sorting_desc {
      background: none;
    }

    .sorting,
    .sorting_asc,
    .sorting_desc {
      background: none;
    }
  </style>


  <!-- Scripts -->

  @include('Admin.layouts.header')
  @include('Admin.layouts.sidebar')
  @yield('js')
  @stack('scripts')
  @yield('style')

</head>

<body>
  <div class="wrapper">
    <div class="content-wrapper">
      @yield('content')
    </div>
    <div style="height:50px"></div>

    @include('Admin.layouts.modal')
    @include('Admin.layouts.footer')

    <!--===============================================================================================-->
    <!-- Vendor JS Files -->
    <script src="{{asset('admin/asset/vendor/apexcharts/apexcharts.min.js')}}"></script>
    <script src="{{asset('admin/asset/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('admin/asset/vendor/chart.js/chart.umd.js')}}"></script>
    <script src="{{asset('admin/asset/vendor/echarts/echarts.min.js')}}"></script>
    <script src="{{asset('admin/asset/vendor/quill/quill.min.js')}}"></script>
    <script src="{{asset('admin/asset/vendor/simple-datatables/simple-datatables.js')}}"></script>
    <script src="{{asset('admin/asset/vendor/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('admin/asset/vendor/php-email-form/validate.js')}}"></script>

    <!-- Template Main JS File -->
    <script src="{{asset('admin/asset/js/main.js')}}"></script>

    <script>
      // JavaScript to toggle the sidebar
      document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.querySelector('.toggle-sidebar');

        toggleButton.addEventListener('click', function () {
          if (window.innerWidth <= 767) { // Adjust as needed for your layout
            sidebar.classList.toggle('active');
          }
        });

        // Keep sidebar expanded on larger screens
        window.addEventListener('resize', function () {
          if (window.innerWidth > 767) { // Adjust as needed for your layout
            sidebar.classList.add('active');
          } else {
            sidebar.classList.remove('active');
          }
        });
      });
      var btn = document.querySelector('.toggle');
      var btnst = true;
      btn.onclick = function () {
        if (btnst) {
          document.querySelector('.toggle span').classList.add('toggle');
          document.querySelector('.sidebar').classList.add('sidebarshow');
          btnst = false;
        } else {
          document.querySelector('.toggle span').classList.remove('toggle');
          document.querySelector('.sidebar').classList.remove('sidebarshow');
          btnst = true;
        }

      }
    </script>
</body>

</html>