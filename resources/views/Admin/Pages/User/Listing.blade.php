@extends('Admin.layouts.app')
@section('title', 'Users')
@section('content')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Users</h1>
  
    
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      @if(Session::has('success'))
      <div class="alert alert-success">{{Session::get('success')}}</div>
      @elseif(Session::has('error'))
      <div class="alert alert-danger">{{Session::get('error')}}</div>
      @endif
    <table class="table table-bordered data-table">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th width="105px">Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    </div>
          </div>

    
     
    </section>

  </main><!-- End #main -->

 

<script type="text/javascript">
  $(function () {
      
    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.users') }}",
        columns: [
          {data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action', searchable: false},
        ],
        columnDefs: [
        {
          targets: [0,1,2,3,4,5], 
            orderable: false
        }
      ]
    });
      
  });
</script>
@endsection