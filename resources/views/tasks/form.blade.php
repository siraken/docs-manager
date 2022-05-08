@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('tasks.index') }}" class="btn btn-light border">戻る</a>
            <button class="btn btn-light border" type="submit">保存する</button>
        </div>
    </div>

    <div class="row">
        @csrf
        <div class="col-12">
            <label class="form-label">タイトル<span class="badge bg-danger ms-1">必須</span></label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $task->title) }}" required>
        </div>
        <div class="col-6">
            <label class="form-label">詳細</label>
            <textarea name="description" id="description" class="form-control"
                style="width: 100%; height: 480px;">{{ old('description', $task->description) }}</textarea>
        </div>
        <div class="col-6">
            <label class="form-label">プレビュー</label>
            <div id="preview" style="width: 100%; height: 480px; overflow-y: scroll;"></div>
        </div>
        <div class="col-12">
            <label class="form-label">ステータス</label>
            <input type="text" name="status" class="form-control" value="{{ old('status', $task->status) }}">

            <label class="form-label">取引先ID</label>
            <input type="text" name="client_id" class="form-control" value="{{ old('client_id', $task->client_id) }}">
        </div>
    </div>
</form>

@endsection
