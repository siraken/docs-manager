@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('users.index') }}" class="btn btn-light border">戻る</a>
            <button class="btn btn-light border" type="submit">保存する</button>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            @csrf

            <label class="form-label">名前<span class="badge bg-danger ms-1">必須</span></label>
            <input type="text" name="name" class="form-control" required>

            <label class="form-label">ユーザー名</label>
            <input type="text" name="username" class="form-control">

            <label class="form-label">メールアドレス<span class="badge bg-danger ms-1">必須</span></label>
            <input type="email" name="email" class="form-control">

            <label class="form-label">パスワード<span class="badge bg-danger ms-1">必須</span></label>
            <input type="password" name="password" class="form-control">

        </div>
    </div>
</form>

@endsection
