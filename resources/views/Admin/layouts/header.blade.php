<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

  <!-- Drawer Button -->
  <!-- <button class="btn btn-primary d-md-none toggle-sidebar" type="button" style="background-color:#69cc00;"></button> -->
  <button class="btn btn-primary toggle-sidebar-btn" type="button" style="background-color:#69cc00; margin-right: 2%">
    <i class="bi bi-list"></i>
  </button>
  <!-- Drawer Button End -->

  <div class="d-flex align-items-center justify-content-between">
    <a href="{{route('admin.dashboard')}}" class="logo d-flex align-items-center">
      <img src="{{asset('admin/assets/images/logo.png')}}" alt="">
    </a>

  </div><!-- End Logo -->


  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">






      <li class="nav-item pe-3">

        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#">
          <img src="{{asset('admin/asset/img/default.png')}}" alt="Profile" class="rounded-circle">
          <span class="d-none d-md-block  ps-2">{{Auth::user()->name}}</span>
        </a><!-- End Profile Iamge Icon -->


      </li><!-- End Profile Nav -->

    </ul>
  </nav><!-- End Icons Navigation -->

</header><!-- End Header -->


@section('js')
