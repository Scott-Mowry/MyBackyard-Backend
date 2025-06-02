@extends('Admin.layouts.app')
@section('title', 'Add Place')

@section('content')

<main id="main" class="main">

  <div class="pagetitle">
    <h1>Add Place</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.places')}}">Places</a></li>
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

                    <form method="POST" action="{{route('admin.places.add')}}" accept-charset="UTF-8"
                      enctype="multipart/form-data">
                      @csrf

                      <div class="form-group">
                        <label for="name">Place Name</label>
                        <input type="text" required name="name" value="{{old('name')}}" class="form-control"
                          maxlength="30" id="name" placeholder="Enter place name">
                      </div>
                      <div class="form-group">
                        <label for="top_left_latitude">Top Left Latitude</label>
                        <input type="text" required name="top_left_latitude" value="{{old('top_left_latitude')}}"
                          class="form-control" maxlength="30" id="top_left_latitude"
                          placeholder="Enter Top Left Latitude">
                      </div>
                      <div class="form-group">
                        <label for="top_left_longitude">Top Left Longitude</label>
                        <input type="text" required name="top_left_longitude" value="{{old('top_left_longitude')}}"
                          class="form-control" maxlength="30" id="top_left_longitude"
                          placeholder="Enter Top Left Longitude">
                      </div>
                      <div class="form-group">
                        <label for="bottom_right_latitude">Bottom Right Latitude</label>
                        <input type="text" required name="bottom_right_latitude"
                          value="{{old('bottom_right_latitude')}}" class="form-control" maxlength="30"
                          id="bottom_right_latitude" placeholder="Enter Bottom Right Latitude">
                      </div>
                      <div class="form-group">
                        <label for="bottom_right_longitude">Bottom Right Longitude</label>
                        <input type="text" required name="bottom_right_longitudee"
                          value="{{old('bottom_right_longitude')}}" class="form-control" maxlength="30"
                          id="bottom_right_longitude" placeholder="Enter Bottom Right Longitude">
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