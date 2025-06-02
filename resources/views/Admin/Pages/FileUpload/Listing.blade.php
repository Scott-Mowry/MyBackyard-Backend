@extends('Admin.layouts.app')
@section('title', 'Categories')
@section('content')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Files</h1>
            <div class="row justify-content-end">

                <div class="col-auto">
                    <a href="{{ route('admin.upload-file.create') }}" class="btn login100-form-btn" style="width:250px;">Upload Files</a>
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
                                <th>Hash</th>
                                <th>Name</th>
                                <th>Attachment</th>
                                <th>Size</th>
                                <th>Type</th>
                                <th>Created at</th>
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
                ajax: "{{ route('admin.upload-file') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        category_name: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'hash',
                        category_name: 'hash'
                    },
                    {
                        data: 'name',
                        category_name: 'name'
                    },
                    {
                        data: 'attachment',
                        category_name: 'attachment',
                        render: function(data, type, full, meta) {
                            return '<i class="bi bi-file-pdf"></i>';
                        }
                    },
                    {
                        data: 'size',
                        category_name: 'size'
                    },
                    {
                        data: 'type',
                        category_name: 'type'
                    },
                    {
                        data: 'created_at',
                        category_name: 'created_at'
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
