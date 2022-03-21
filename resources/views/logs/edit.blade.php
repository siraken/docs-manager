@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('logs.index') }}" class="btn btn-light border">戻る</a>
            <button class="btn btn-light border" type="submit">保存する</button>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            @csrf

            <label class="form-label">品名・品番<span class="badge bg-danger ms-1">必須</span></label>
            <input type="text" name="name" class="form-control" value="{{ $log['name'] }}" required>

            <label class="form-label">単位</label>
            <input type="text" name="unit" class="form-control" value="{{ $log['unit'] }}">

        </div>
    </div>
</form>

@endsection
