@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <div class="row mb-3">
        <div class="col-12">
            <a href="/items/" class="btn btn-light border">戻る</a>
            <button class="btn btn-light border" type="submit">保存する</button>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            @csrf

            <label class="form-label">品名・品番<span class="badge bg-danger ms-1">必須</span></label>
            <input type="text" name="name" class="form-control" required>

            <label class="form-label">単位</label>
            <input type="text" name="unit" class="form-control">

            <label class="form-label">単価</label>
            <div class="input-group">
                <input type="text" name="cost" class="form-control">
                <span class="input-group-text">円</span>
            </div>

            <label class="form-label">税率</label>
            <select class="form-select" name="tax">
                <option value="0" selected disabled>選択してください</option>
                <option value="1">10%</option>
                <option value="2">軽減8%</option>
                <option value="3">8%</option>
                <option value="4">対象外</option>
                <option value="5">5%</option>
            </select>
        </div>
    </div>
</form>

@endsection
