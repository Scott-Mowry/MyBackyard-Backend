@extends('Admin.layouts.app')
@section('title', 'View Category')

@section('content')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Details Files</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.upload-file')}}">Categories</a></li>
      <li class="breadcrumb-item active">Details</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section profile">
  <div class="row">
    <div class="col-xl-12">

    <div class="card mb-4">

     <div class="card-body">
     <div class="row my-1" style="margin-left:0.0em;">
     <!-- <h2>Edit Category</h2> -->
  <div class="row">
    <div class="col-md-12"> 




      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label>Image</label>
            <img src="{{ url($file->attachment) }}" alt="File" style="width: 30px !important; height: 30px !important;">
        </div>
        </div>

        <div class="col-md-3">
           
      <div class="form-group">
        <label>Name</label>
        <p>{{$file->name}}</p>
    </div>
        </div>


        <div class="col-md-3">
          
    <div class="form-group">
      <label>Size</label>
      <p>{{$file->size}}</p>
  </div>
        </div>

        <div class="col-md-3">
          
    <div class="form-group">
      <label>Type</label>
      <p>{{$file->type}}</p>
  </div>
        </div>
          
      </div>


     

      
     

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








