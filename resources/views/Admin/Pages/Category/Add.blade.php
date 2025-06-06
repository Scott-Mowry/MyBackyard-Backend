@extends('Admin.layouts.app')
@section('title', 'Add Category')

@section('content')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Add Category </h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.categories')}}">Categories</a></li>
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
     <!-- <h2>Add Category</h2> -->
  <div class="row">
    <div class="col-md-4">

      <form method="POST" action="{{route('admin.category.add')}}"  accept-charset="UTF-8" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
          <label for="category_name">Category Name</label>
          <input type="text" required name="category_name" value="{{old('category_name')}}" class="form-control" maxlength="30" id="category_name" placeholder="Enter category name">
        </div>

        <div class="form-group">
            <label for="category_icon">Category Icon</label>
            <input type="file" name="category_icon" class="form-control" maxlength="30" id="category_icon" placeholder="Select category Icon" required>
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








