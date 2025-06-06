@extends('Admin.layouts.app')
@section('title', 'Add Word')

@section('content')

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Add Word </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{route('admin.manage_words')}}">Manage Words</a></li>
                <li class="breadcrumb-item active">Add</li>
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
                                <!-- <h2>Add Word</h2> -->
                                <div class="row">
                                    <div class="col-md-12">

                                        <form method="POST" action="{{ route('admin.manage_words.add') }}"
                                            enctype="multipart/form-data">
                                            @csrf

                                        
                                            <div class="form-group">
                                                <label for="category">Category</label>
                                                <select class="form-select" name="category_id" aria-label="Default select example" required>
                                                     <option value="">Select Category</option>
                                                    @foreach($categories as $cat){
                                                        <option value="{{ $cat->id}}">{{ $cat->category_name }}</option>
                                                     
                                                     @endforeach

                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="word">Word</label>
                                                <textarea name="word" class="form-control auto-resize-textarea"
                                                    id="word" required
                                                    placeholder="Enter Word"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="pronunciation">Pronunciation</label>
                                                <input name="pronunciation" class="form-control auto-resize-textarea"
                                                    id="pronunciation" required placeholder="Enter pronunciation"
                                                    value="">
                                            </div>
                                            <div class="form-group">
                                                <label for="description">Description</label>
                                                <textarea name="description" class="form-control auto-resize-textarea"
                                                    id="description" required
                                                    placeholder="Enter description"></textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="noun_text">Noun</label>
                                                <input name="noun_text" class="form-control" id="noun_text" required
                                                    placeholder="Enter Noun">
                                            </div>
                                            <div class="form-group">
                                                <label for="adjective_text">Adjective</label>
                                                <input name="adjective_text" class="form-control" id="adjective_text"
                                                    required placeholder="Enter Adjective">
                                            </div>
                                            <div class="form-group">
                                                <label for="pronoun_text">Pronoun</label>
                                                <input name="pronoun_text" class="form-control" id="pronoun_text"
                                                    required placeholder="Enter Pronoun">
                                            </div>
                                            <div class="form-group">
                                                <label for="verb_text">Verb</label>
                                                <input name="verb_text" class="form-control" id="verb_text" required
                                                    placeholder="Enter Verb">
                                            </div>
                                    </div>
                                </div>


                                <button type="submit" class="btn login100-form-btn">Add</button>
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
    document.getElementById('imageUpload').addEventListener('change', function () {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('imagePreview').setAttribute('src', e.target.result);
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(this.files[0]);
    });
    $(document).ready(function () {
        $('.auto-resize-textarea').on('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });

</script>

@endsection