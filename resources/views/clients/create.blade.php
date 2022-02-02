@extends('layouts/default')
@section('page')

<form method="post">
    @csrf
    <div class="row mb-3">
        <div class="col-12">
            <a href="/clients" class="btn btn-light border">Back</a>
            <button type="submit" class="btn btn-light border">Save</button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="mb-3">
                <label for="formTitleInput" class="form-label">Name</label>
                <input type="text" class="form-control" id="formTitleInput" name="name" placeholder="Name" required>
            </div>
        </div>
    </div>
</form>

@endsection
