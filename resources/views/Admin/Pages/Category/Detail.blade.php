@extends('Admin.layouts.app')
@section('title', 'View Category')

@section('content')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Edit Category</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.categories')}}">Categories</a></li>
      <li class="breadcrumb-item active">Edit</li>
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
     <!-- <h2>Edit Category</h2> -->
  <div class="row">
    <div class="col-md-4">

      <form method="POST" action="{{route('admin.category.update')}}" accept-charset="UTF-8" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="category_id" value="{{$category->id}}"/>
        <div class="form-group">
          <label for="categoryName">Category Name</label>
          <input type="text" name="category_name" value="{{$category->category_name}}" class="form-control" maxlength="30" id="category_name" placeholder="Enter category name" required>
        </div>
        <div class="col-lg-12">
            <div class="row mb-3">
            @if($category->category_icon !== null)
                    <div class="col-md-12 mt-3"> <!-- Assuming Bootstrap grid system with 6 columns per row -->
                        <img src="{{ url('/').$category->category_icon }}" id="imagePreview" alt="Category Icon" class="img-fluid"  style="max-height:300px;">
                    </div>
                    @else
                    <p>No Category Icon found.</p>
             @endif
            </div>
        </div>

        <div class="form-group">
            <label for="categoryName">Category Icon</label>
            <input type="file" name="category_icon" value="{{$category->category_icon}}" class="form-control" maxlength="30" id="category_icon" placeholder="Select category icon" required>
          </div>
        <button type="submit" class="btn login100-form-btn">Update</button>
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

<script>
    document.getElementById('imageUpload').addEventListener('change', function() {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').setAttribute('src', e.target.result);
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(this.files[0]);
    });
    $(document).ready(function() {
        $('.auto-resize-textarea').on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
</script>

@endsection








