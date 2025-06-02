@extends('Admin.layouts.app')
@section('title', 'Subscribed Users')
@section('content')

<main id="main" class="main">

    <div class="pagetitle">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-auto">
                <h1>Subscribed Users</h1>
            </div>
            <!-- <label class="d-flex align-items-center mb-0" style="width:180px">
                Filter:
                <div style="width:2%"></div>
                <select id="myDropdown" class="form-select form-select-sm" style="width:115px">
                    <option value="None">None</option>
                    <option value="Annual">Annual</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Free">Free</option>
                </select>
            </label> -->
        </div>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="cnol-lg-12">
                @if (Session::has('success'))
                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                @elseif(Session::has('error'))
                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                @endif
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th width="80px">S.No</th>
                            <th>Role</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Price</th>
                            <th>Type</th>
                            <th>Status</th>
                            <!-- <th width="105px">Action</th> -->
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
            ajax: "{{route('admin.subUsers') }}",
            columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                searchable: false
            },
            {
                data: 'role',
                name: 'role'
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'email',
                name: 'email'
            },
            {
                data: 'price',
                name: 'price'
            },
            {
                data: 'type',
                name: 'type'
            },
            {
                data: 'status',
                name: 'status',
                searchable: false
            },
                // {
                //     data: 'action',
                //     name: 'action',
                //     searchable: false
                // },
            ],
            columnDefs: [{
                targets: [0, 1, 2, 3, 4, 5, 6
                    // , 6
                ], // All columns from index 0 to 5
                orderable: false // Disable sorting for the specified columns
            }]
        });

        $('#myDropdown').change(function () {
            var selectedValue = $(this).val();
            table.column(4).search(selectedValue).draw();
        });

    });
</script>
<!-- @endsection