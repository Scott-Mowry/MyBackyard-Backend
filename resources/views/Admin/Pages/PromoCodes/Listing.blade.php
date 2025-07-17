@extends('Admin.layouts.app')
@section('title', 'Promo Codes')
@section('content')

  <main id="main" class="main">

    <div class="pagetitle">
    <div class="row justify-content-between align-items-center">
      <div class="col-auto">
      <h1>Promo Codes</h1>
      </div>
      <div class="col-auto">
      <a href="{{ route('admin.promocodes.addForm') }}" class="btn login100-form-btn"
        style="width:100px; height: 40px;">Add +</a>
      </div>
    </div>

    <div class="row justify-content-between align-items-center mb-4">
    </div>
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
          <th>Code</th>
          <th>Free Duration (days)</th>
          <th>Claimed By</th>
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
      ajax: "{{route('admin.promocodes')}}",
      columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false },
      { data: 'code', name: 'code' },
      { data: 'sub_duration', name: 'sub_duration', searchable: false },
      { data: 'claimed_by', name: 'sub_duration', searchable: false },
      { data: 'action', name: 'action', searchable: false },
      ],
      columnDefs: [
      {
        targets: [0, 1, 2, 3, 4],
        orderable: false
      }
      ]
    });

    });
  </script>
@endsection