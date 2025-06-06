@extends('Admin.layouts.app')
@section('title', 'Categories')
@section('content')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Categories</h1>
            <div class="row justify-content-end">

                <div class="col-auto">
                    <a href="{{ route('admin.category.addForm') }}" class="btn login100-form-btn" style="width:250px;">Add
                        Category</a>
                </div>
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
                                <th>S.No</th>
                                <th>Name</th>
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
        $(function() {

            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.categories') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        category_name: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'category_name',
                        category_name: 'category_name'
                    },
                    {
                        data: 'category_icon',
                        name: 'category_icon',
                        render: function(data, type, full, meta) {
                            return '<img src="{{ url('/') }}'+data+'" alt="Category Icon" style="width: 30px !important; height: 30px !important;">';
                        }
                    },
                    {
                        data: 'action',
                        category_name: 'action',
                        searchable: false
                    },
                ],
                columnDefs: [{
                    targets: [0, 1, 2, 3],
                    orderable: false
                }]
            });

        });
    </script>
@endsection
