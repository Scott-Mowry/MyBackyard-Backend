@extends('Admin.layouts.app')
@section('title', 'Add Promo Code')

@section('content')

  <main id="main" class="main">

    <div class="pagetitle">
    <h1>Add Promo Code</h1>
    <nav>
      <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.promocodes')}}">Promo Code</a></li>
      <li class="breadcrumb-item active">Add</li>
      </ol>
    </nav>
    </div><!-- End Page Title -->

    <section class="section profile">
    <div class="row">
      <div class="col-xl-12">

      <div class="card mb-4">

        <div class="card-body">
        <div class="row my-1" style="margin-left:0.0em;">

          @if(Session::has('success'))
        <div class="alert alert-success">{{Session::get('success')}}</div>
      @elseif(Session::has('error'))
        <div class="alert alert-danger">{{Session::get('error')}}</div>
      @endif
          <div class="container mt-1">
          <!-- <h2>Add Place</h2> -->
          <div class="row">
            <div class="col-md-4">

            <form method="POST" action="{{route('admin.promocodes.add')}}" accept-charset="UTF-8"
              enctype="multipart/form-data">
              @csrf

              <div class="form-group">
              <!-- Promo Code -->
              <div class="form-group mb-3">
                <label for="code">Promo Code</label>
                <input type="text" name="code" id="code" class="form-control" maxlength="255"
                value="{{ old('code') }}" placeholder="Enter promo code" required>
              </div>

              <!-- Subscription Duration -->
              <div class="form-group mb-3">
                <label for="sub_duration">Subscription Duration (in days)</label>
                <input type="number" name="sub_duration" id="sub_duration" class="form-control"
                value="{{ old('sub_duration') }}" placeholder="Enter duration in days" required>
              </div>

              <!-- Role Dropdown -->
              <div class="form-group mb-3">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-control" required>
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select a role</option>
                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                <option value="business" {{ old('role') == 'business' ? 'selected' : '' }}>Business</option>
                </select>
              </div>
              <button type="submit" class="btn login100-form-btn">Add</button>
            </form>
            </div>
          </div>
          </div>

        </div>



        </div>
      </div>


      </div>


    </div>
    </section>

  </main><!-- End #main -->



@endsection