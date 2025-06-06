@extends('Admin.layouts.app')
@if($type == 'create')
@section('title', 'Upload files')
@else
@section('title', 'Edit files')
@endif

@section('content')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Upload Files </h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{route('admin.upload-file')}}">Upload Files</a></li>
      @if($type == 'create')
      <li class="breadcrumb-item active">Add</li>
@else
<li class="breadcrumb-item active">Edit</li>
@endif
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
    <div class="col-md-12">

      {{-- <div class="row">
        <div class="col-md-4">
          <button type="button" class="btn btn-md btn-success btn-add-field">+</button>
        </div>
      </div> --}}

        @if($type == 'create')
          @php $action =  route('admin.upload-file.store'); @endphp
        @else
          @php $action =  route('admin.upload-file.update', $files[0]->hash); @endphp
        @endif

      

      <form method="POST" action="{{$action}}"  accept-charset="UTF-8" enctype="multipart/form-data">
        @csrf

        <div class="cont-fields">
          
          @if($type == 'create')
          {{-- <div class="row row-fields">
            <div class="col-md-4">
              <div class="form-group">
                <label>Name</label>
                <input type="text" name="name[]"  required class="form-control name" />
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>File</label>
                <input type="file" name="file[]" class="form-control file" accept="application/pdf" required/>
                <input type="file" name="pic" id="pic" accept="image/gif, image/jpeg" />
                <input type="hidden" name="prev_file[]" />
                <input type="hidden" name="size[]" />
                <input type="hidden" name="type[]" />
              </div>
            </div>
            <div class="col-md-2 invisible">
              <label>btn</label>
              <button type="button" class="btn btn-md btn-danger btn-remove-field">-</button>
          </div>
        </div> --}}


        <div class="row row-fields">
          <div class="col-md-4">
            <div class="form-group">
              <label>File</label>
              <input type="file" name="file[]" multiple required class="form-control file" />
              <label style="padding-top: 10px;">(Max Limit: 20)</label>
            </div>
          </div>
          
        </div>
       



        @else
            {{-- @foreach ($files as $key => $file)
            <div class="row row-fields">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Name</label>
                  <input type="text" name="name[]" value="{{$file->name}}" class="form-control name" />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>File</label>
                  <input type="file" name="file[]" class="form-control file" accept="application/pdf" required />
                  <input type="hidden" name="prev_file[]"  value="{{$file->attachment}}"/>
                  <input type="hidden" name="size[]" value="{{$file->size}}" />
                  <input type="hidden" name="type[]" value="{{$file->type}}" />
                </div>
              </div>

              <div class="col-md-2 {{ $key > 0 ? '' : 'invisible'}} ">
                <label class="invisible">-</label>
                <button type="button" class="btn btn-md btn-danger btn-remove-field">-</button>
            </div>
            <div class="col-md-2">
              <label class="invisible">-</label>

              <img src="{{ url($file->attachment) }}" alt="File" style="width: 30px !important; height: 30px !important;" /> --}}

          </div>


          </div>
            {{-- @endforeach --}}
          @endif
          
        </div>
        

        
        @if($type == 'create')
          @php $btn_text =  "Upload"; @endphp
        @else
        @php $btn_text =  "Update"; @endphp
        @endif



        <div class="row">
          <div class="col-md-4">
            <button type="submit" class="btn btn-md login100-form-btn">{{$btn_text}}</button>
          </div>
        </div>
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

    $(document).ready(function() {


      // $(document).on('click', '.btn-remove-field', function(){
      //   const ind =  $(this).index('.btn-remove-field');
      //   $('.row-fields').eq(ind).remove();
      // })

      //   $('.btn-add-field').click(function(){
      //       let field;

      //       field = `
      //          <div class="row row-fields">
      //           <div class="col-md-4">
      //             <div class="form-group">
      //               <label>Name</label>
      //               <input type="text" name="name[]" required class="form-control name" />
      //             </div>
      //           </div>
      //           <div class="col-md-4">
      //             <div class="form-group">
      //               <label>File</label>
      //               <input type="file" name="file[]" required class="form-control file" />
      //               <input type="hidden" name="prev_file[]" />
      //               <input type="hidden" name="size[]" />
      //               <input type="hidden" name="type[]" />
      //             </div>
      //           </div>
      //           <div class="col-md-2">
      //               <label class="invisible">btn</label>
      //               <button type="button" class="btn btn-md btn-danger btn-remove-field">-</button>
      //           </div>
      //       </div>
      //       `;

      //       $('.cont-fields').append(field)
      //   });
    });
</script>

@endsection








