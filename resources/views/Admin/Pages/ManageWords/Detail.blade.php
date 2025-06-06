@extends('Admin.layouts.app')
@section('title', 'Edit Manage Word')

@section('content')

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Manage Word Edit</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.manage_words') }}">Manage Words</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section profile">
            <div class="row">
                <div class="col-xl-12">

                    <div class="card mb-4">

                        <div class="card-body">
                            <div class="row my-1" style="margin-left:0.0em;">

                                @if (Session::has('success'))
                                    <div class="alert alert-success">{{ Session::get('success') }}</div>
                                    <script>
                                        setTimeout(function() {
                                            window.location.href = "{{ route('admin.manage_words') }}";
                                        }, 3000);
                                    </script>
                                @elseif(Session::has('error'))
                                    <div class="alert alert-danger">{{ Session::get('error') }}</div>
                                @endif
                                <div class="container mt-1">
                                    <!-- <h2>Add Motivational Qoute</h2> -->
                                    <div class="row">
                                        <div class="col-md-12">

                                            <form method="POST" action="{{ route('admin.manage_words.update') }}"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $word->id }}">
                                                <div class="form-group">
                                                    <label for="requested_by">Requested By</label>
                                                    <input disabled name="requested_by" class="form-control"
                                                        id="requested_by" required placeholder="Enter Word"
                                                        value="{{ $word->requestedBy->name }}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="category">Category</label>
                                                    <input disabled name="category" class="form-control" id="category"
                                                        required placeholder="Enter category"
                                                        value="{{ $word->wordCategory->category_name }}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="word">Word</label>
                                                    <textarea name="word" class="form-control auto-resize-textarea" id="word" required placeholder="Enter Word">{{ $word->word }}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="pronunciation">Pronunciation</label>
                                                    <input name="pronunciation" class="form-control auto-resize-textarea" id="pronunciation" required
                                                        placeholder="Enter pronunciation" value="{{ $word->pronunciation }}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="description">Description</label>
                                                    <textarea name="description" class="form-control auto-resize-textarea" id="description" required
                                                        placeholder="Enter description">{{ $word->description }}</textarea>
                                                </div>

                                                @if ($word->word_data->isEmpty())
                                                    <div class="form-group">
                                                        <label for="noun_text">Noun</label>
                                                        <input name="noun_text" class="form-control" id="noun_text" required
                                                            placeholder="Enter Noun">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="adjective_text">Adjective</label>
                                                        <input name="adjective_text" class="form-control"
                                                            id="adjective_text" required placeholder="Enter Adjective">
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
                                                @else
                                                    @foreach ($word->word_data as $wordData)
                                                        <div class="form-group">
                                                            <label
                                                                for="{{ $wordData->word_data_type }}_text">{{ ucfirst($wordData->word_data_type) }}</label>
                                                            <input name="{{ $wordData->word_data_type }}_text"
                                                                class="form-control"
                                                                value="{{ $wordData->word_data_text }}"
                                                                id="{{ $wordData->word_data_type }}_text" required
                                                                placeholder="Enter {{ ucfirst($wordData->word_data_type) }}">
                                                        </div>
                                                    @endforeach
                                                @endif

                                                <div class="form-group">
                                                    <label for="is_approved">Status</label>
                                                    <select name="is_approved" class="form-control" id="is_approved"
                                                        required>
                                                        <option value="1"
                                                            {{ $word->is_approved == 1 ? 'selected' : '' }}>Approved
                                                        </option>
                                                        <option value="0"
                                                            {{ $word->is_approved == 0 ? 'selected' : '' }}>Not Approved
                                                        </option>
                                                    </select>
                                                </div>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn login100-form-btn">Update</button>
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
        document.getElementById('imageUpload').addEventListener('change', function() {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').setAttribute('src', e.target.result);
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        });
        $(document).ready(function() {
            $('.auto-resize-textarea').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            $('#noun').click(function() {
                if ($.trim($(this).val()) === '') {
                    alert(this.val());
                    $(this).val('');
                }
            });

            $('#adjective').click(function() {
                if ($.trim($(this).val()) === '') {
                    alert(this.val());
                    $(this).val('');
                }
            });
        });
    </script>

@endsection
