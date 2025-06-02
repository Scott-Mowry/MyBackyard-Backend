@extends('Admin.layouts.app')
@section('title', 'View User')

@section('content')

<main id="main" class="main">

<div class="pagetitle">
  <h1>User Details</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.users')}}">Users</a></li>
      <li class="breadcrumb-item active">Details</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section profile">
  <div class="row">
    <div class="col-xl-12">

    <div class="card mb-4">
    <div class="d-flex align-items-center m-3">
    @if($user->profile_image !== null)
    <div>
    
        <img src="{{ asset(''.$user->profile_image) }}" alt="Profile Image" class="img-fluid rounded-circle" style="height:150px;width:150px;margin-top:15px;">
    </div>
    @else
    <img src="{{ asset('admin/asset/images/user_img.png') }}" alt="Profile Image" class="img-fluid rounded-circle" style="height:150px;width:150px;margin-top:15px;">
@endif
        <div class="col-md-9">
            <div class="d-flex align-items-center">
                <h6 class="mb-0 mr-3"><b>Full Name</b></h6>
                <p class="text-18-black">{{$user->name ?? ''}} {{$user->last_name ?? ''}}</p>
            </div>
       
        </div>
        
    </div>
     <div class="card-body">
     <div class="row my-1" style="margin-left:0.0em;">
   
       
                                      
                                         <div class="col-lg-6">
                                           <h6 class="mb-0"><b>Email</b></h6>
                                           <p class="text-18-black">{{$user->email ?? '---'}}</p>
                                       </div>
                                        
                                       <div class="col-lg-6">
                                           <h6 class="mb-0"><b>Phone</b></h6>
                                           <p class="text-18-black">{{$user->phone ?? '---'}}</p>
                                       </div>
                                      
                                    
                                       <div class="col-lg-5">
                                           <h6 class="mb-0"><b>Status</b></h6>
                                           @if($user->is_blocked==1)
                                           <p class="text-18-black text-danger">Block</p>
                                           @else
                                           <p class="text-18-black text-success">Active</p>
                                           @endif
                                       </div>
                                      
                                      
</div>

                                      
   
     </div>
   </div>


    </div>

   
  </div>
</section>

</main><!-- End #main -->



@endsection






                    

                       