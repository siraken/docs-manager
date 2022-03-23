@extends('layouts/default')
@section('page')

<form method="post">
    @csrf
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('clients.index') }}" class="btn btn-light border">戻る</a>
            <button type="submit" class="btn btn-light border">保存する</button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="mb-3">
                <label for="formTitleInput" class="form-label">取引先名</label>
                <input type="text" class="form-control" id="formTitleInput" name="name" placeholder="取引先名" value="{{ old('name', $client->name) }}" required>
            </div>
        </div>
    </div>
</form>

@endsection
