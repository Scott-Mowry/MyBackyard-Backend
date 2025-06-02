@extends('Admin.layouts.app')
@section('title', 'Stock Images')

@section('content')

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.css" rel="stylesheet">
    <style>
        /* Custom CSS styles */
        #uploadArea {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 10px;
            text-align: center;
        }
        #uploadArea p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        #uploadButton {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        #uploadButton:hover {
            background-color: #45a049;
        }
    </style>
<style>
        .image-container {
            display: inline-block;
            width: 20%; /* Adjust width to fit 5 images per row */
            margin: 10px;
            text-align: center; /* Center image and button */
            position: relative; /* To position the button */
        }
        .image-container img {
            width: 100%; /* Make images fill the container */
            height: 100px; /* Maintain aspect ratio */
            display: block; /* Ensure images are centered */
        }
        .delete-button {
            position: absolute;
            top: 5px;
            right: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }
    </style>
    <style>
      main{
  max-width:1200px;
  margin: auto;
}
.fileuploader {
    position: relative;
    width: 60%;
    margin: auto;
    height: 400px;
    border: 4px dashed #ddd;
    background: #f6f6f6;
    margin-top: 85px;
}
.fileuploader #upload-label{
  background: rgba(231, 97, 92, 0);
  color: #fff;
  position: absolute;
  height: 115px;
  top: 20%;
  left: 0;
  right: 0;
  margin-right: auto;
  margin-left: auto;
  min-width: 20%;
  text-align: center;
  cursor: pointer;
}
.fileuploader.active{
  background: #fff;
}
.fileuploader.active #upload-label{
  background: #fff;
  color: #e7615c;
}

.fileuploader #upload-label i:hover {
    color: #444;
    font-size: 9.4rem;
    -webkit-transition: width 2s;
}

.fileuploader #upload-label span.title{
  font-size: 1em;
  font-weight: bold;
  display: block;
}

span.tittle {
    position: relative;
    top: 222px;
    color: #bdbdbd;
}

.fileuploader #upload-label i{
  text-align: center;
  display: block;
  color: #e7615c;
  height: 115px;
  font-size: 9.5rem;
  position: absolute;
  top: -12px;
  left: 0;
  right: 0;
  margin-right: auto;
  margin-left: auto;
}
/** Preview of collections of uploaded documents **/
.preview-container{
  position: relative;
  bottom: 0px;
  width: 35%;
  margin: auto;
  top: 25px;
  visibility: hidden;
}
.preview-container #previews{
  max-height: 400px;
  overflow: auto; 
}
.preview-container #previews .zdrop-info{
  width: 88%;
  margin-right: 2%;
}
.preview-container #previews.collection{
  margin: 0;
  box-shadow: none;
}

.preview-container #previews.collection .collection-item {
    background-color: #e0e0e0;
}

.preview-container #previews.collection .actions a{
  width: 1.5em;
  height: 1.5em;
  line-height: 1;
}
.preview-container #previews.collection .actions a i{
  font-size: 1em;
  line-height: 1.6;
}
.preview-container #previews.collection .dz-error-message{
  font-size: 0.8em;
  margin-top: -12px;
  color: #F44336;
}



/*media queries*/

@media only screen and (max-width: 601px){
  .fileuploader {
    width: 100%;
  }

 .preview-container {
    width: 100%;
  }
}
      </style>
</head>
<main id="main" class="main">

<div class="pagetitle">
  <h1>Stock Images</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.stock_images')}}">Stock Images</a></li>
      <li class="breadcrumb-item active">Add / View</li>
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
     <form action="{{ route('admin.stock_images.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="fallback">
                    <input name="images[]" type="file" accept="image/*" multiple  required/>
                </div>
                 <button id="submitImagesButton" type="submit" class="btn login100-form-btn mt-3" style="width:250px;">Submit Images</button>
            </form>
    
     

     <h6 class="mb-0 mt-3"><b>Uploaded Stock Images</b></h6>
     @foreach ($images as $image)
    <div class="image-container">
        <img src="{{asset(''.$image->image)}}" alt="">
        <form action="{{ route('admin.stock_image.destroy', $image->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit"  class="delete-button"><i class="fa fa-trash" style="color:white;"></i></button>
        </form>
    </div>
@endforeach
    
    </div>
                                      
</div>

                                      
   
     </div>
   </div>


    </div>

   
  </div>
</section>

</main><!-- End #main -->






@endsection






                    

                       