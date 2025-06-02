@extends('Admin.layouts.app')
@section('title', 'Manage Words')
@section('content')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Manage Words</h1>
            <div class="row justify-content-end">

                <div class="col-auto">
                    <a href="{{ route('admin.manage_words.addForm') }}" class="btn login100-form-btn" style="width:250px;">Add
                        Word</a>
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
                                <th>Word</th>
                                <th>Pronunciation</th>
                                <th>Description</th>
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
                ajax: "{{ route('admin.manage_words') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false
                    },
                    {
                        data: 'word',
                        name: 'word'
                    },
                    {
                        data: 'pronunciation',
                        name: 'pronunciation'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'is_approved',
                        name: 'is_approved',
                        render: function(data) {
                            return data ;
                        }
                    },

                    {
                        data: 'action',
                        name: 'action',
                        searchable: false
                    },
                ],
                columnDefs: [{
                    targets: [0, 1, 2, 3], // All columns from index 0 to 3
                    orderable: false // Disable sorting for the specified columns
                }]
            });

        });
    </script>
@endsection
