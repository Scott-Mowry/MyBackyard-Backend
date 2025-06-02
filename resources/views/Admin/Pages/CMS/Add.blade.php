@extends('Admin.layouts.app')
@section('title', 'CMS')
@section('content')

<style>
    .purple-underline {
        border-bottom: 3px solid #69cc00;

    }
</style>
<main id="main" class="main">



    <section class="section profile">
        <div class="row">
            <div class="col-xl-12">

                <div class="card mb-4">

                    <div class="card-body">
                        @if(Session::has('success'))
                            <div class="alert alert-success">
                                {{Session::get('success')}}
                            </div>
                        @endif
                        <form method="post" action="{{ route('admin.cms.add') }}">
                            @csrf
                            <div class="container mt-5">
                                <div class="row">
                                    <div class="col-lg-4 text-center"> <!-- Center aligning the heading -->
                                        <h1 style="margin-bottom: 20px; font-size: 28px; font-family: 'Arial', sans-serif; font-weight: bold; color: #333;"
                                            class="purple-underline">Terms And Conditions</h1>
                                        <div class="form-group">
                                            <textarea name="terms" class="form-control" rows="13" cols="70"
                                                required>{{$data->detail ?? ''}}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 text-center"> <!-- Center aligning the heading -->
                                        <h1 style="margin-bottom: 20px; font-size: 28px; font-family: 'Arial', sans-serif; font-weight: bold; color: #333;"
                                            class="purple-underline">Privacy Policy</h1>
                                        <div class="form-group">
                                            <textarea name="policy" class="form-control" rows="13" cols="70"
                                                required>{{$data2->detail ?? ''}}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 text-center"> <!-- Center aligning the heading -->
                                        <h1 style="margin-bottom: 20px; font-size: 28px; font-family: 'Arial', sans-serif; font-weight: bold; color: #333;"
                                            class="purple-underline">About Us</h1>
                                        <div class="form-group">
                                            <textarea name="policy" class="form-control" rows="13" cols="70"
                                                required>{{$data3->detail ?? ''}}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-lg-6 mt-3 text-center">
                                    <div class="form-group">
                                        <button class="btn  btn-lg login100-form-btn btn-itz"
                                            style="width:250px;margin-left:130px;" type="submit">Save CMS</button>
                                    </div>
                                </div>
                            </div>
                        </form>





                    </div>
                </div>


            </div>


        </div>
    </section>

</main><!-- End #main -->


@endsection