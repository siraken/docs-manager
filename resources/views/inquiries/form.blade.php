@extends('layouts/default')
@section('page')

<form method="post">
    @csrf
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('inquiries.index') }}" class="btn btn-light border">戻る</a>
            <button type="submit" class="btn btn-light border">保存する</button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="mb-3">
                <label for="formTitleInput" class="form-label">タイトル</label>
                <input type="text" class="form-control" id="formTitleInput" name="title" placeholder="タイトル"
                    value="{{ old('title', $inquiry->title) }}" required>
            </div>
            <div class="mb-3">
                <label for="formBodyTextArea" class="form-label">本文</label>
                <textarea class="form-control" id="formBodyTextArea" name="body" rows="3"
                    placeholder="Description here." required>{{ old('body', $inquiry->body) }}</textarea>
            </div>
        </div>
    </div>
</form>

@endsection
