@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <div class="row mb-3">
        <div class="col-12">
            <a href="/tasks/" class="btn btn-light border">戻る</a>
            <button class="btn btn-light border" type="submit">保存する</button>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            @csrf

            <label class="form-label">タイトル<span class="badge bg-danger ms-1">必須</span></label>
            <input type="text" name="title" class="form-control" required>

            <label class="form-label">詳細</label>
            <textarea name="description" id="description" class="form-control" cols="30" rows="10"></textarea>

            <label class="form-label">ステータス</label>
            <input type="text" name="status" class="form-control">

            <label class="form-label">取引先ID</label>
            <input type="text" name="client_id" class="form-control">
        </div>
    </div>
</form>

@endsection
