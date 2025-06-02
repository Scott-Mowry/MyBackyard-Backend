@extends('Admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <main id="main" class="main">

    <div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
      <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
      <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
    <div class="row">

      <!-- Left side columns -->
      <div class="col-lg-12">
      <div class="row">

        <!-- Sales Card -->
        <div class="col-xxl-4 col-md-6">
        <div class="card info-card sales-card">



          <div class="card-body">
          <h5 class="card-title">Total Active Users</h5>

          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
            <i class="bi bi-people"></i>
            </div>
            <div class="ps-3">
            <h6>{{$totalActiveUsers}}</h6>
            </div>
          </div>
          </div>

        </div>
        </div><!-- End Sales Card -->

        <!-- Revenue Card -->
        <div class="col-xxl-4 col-md-6">
        <div class="card info-card revenue-card">



          <div class="card-body">
          <h5 class="card-title">Total Inactive Users</h5>

          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
            <i class="bi bi-people"></i>
            </div>
            <div class="ps-3">
            <h6>{{$totalInactiveUsers}}</h6>


            </div>
          </div>
          </div>

        </div>
        </div><!-- End Revenue Card -->

        <!-- Customers Card -->
        <!-- <div class="col-xxl-4 col-xl-12">
      <div class="card info-card customers-card"> -->
        <div class="col-xxl-4 col-md-6">
        <div class="card info-card revenue-card">


          <div class="card-body">
          <h5 class="card-title">Total Users </h5>

          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
            <i class="bi bi-people"></i>
            </div>
            <div class="ps-3">
            <h6>{{$totalUsers}}</h6>


            </div>
          </div>

          </div>
        </div>

        </div><!-- End Customers Card -->

        <!-- Total Payment Card -->
        <!-- <div class="col-xxl-4 col-xl-12">
      <div class="card info-card customers-card"> -->
        <div class="col-xxl-4 col-md-6">
        <div class="card info-card revenue-card">

          <div class="card-body">
          <h5 class="card-title">Total Paying Customer </h5>

          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
            <i class="bi bi-cash"></i>
            </div>
            <div class="ps-3">
            <h6>{{$totalpayingCustomer}}</h6>
            </div>
          </div>

          </div>
        </div>

        </div><!-- End Total Payment Card -->







      </div>
      </div><!-- End Left side columns -->



    </div>
    </section>

  </main><!-- End #main -->


@endsection