@extends('layouts/default')
@section('page')

<form method="post">
    @csrf
    <div class="row mb-3">
        <div class="col-12">
            <a href="./" class="btn btn-primary"><i class="fa fa-chevron-left me-2"></i>Back</a>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save me-2"></i>Save</button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="mb-3">
                <label for="formTitleInput" class="form-label">Title</label>
                <input type="text" class="form-control" id="formTitleInput" name="title" placeholder="Inquiry Title" required>
            </div>
            <div class="mb-3">
                <label for="formBodyTextArea" class="form-label">Body</label>
                <textarea class="form-control" id="formBodyTextArea" name="body" rows="3" placeholder="Description here." required></textarea>
            </div>
        </div>
    </div>
</form>

@endsection
