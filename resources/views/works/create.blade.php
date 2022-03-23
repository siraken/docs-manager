@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('works.index') }}" class="btn btn-light border">戻る</a>
            <button class="btn btn-light border" type="submit">保存する</button>
        </div>
    </div>

    <div class="row">
        @csrf
        <div class="col-12">
            <label class="form-label">案件名<span class="badge bg-danger ms-1">必須</span></label>
            <input type="text" name="name" class="form-control" required>

            <label class="form-label">取引先</label>
            <select id="" name="client_id" class="form-select">
            @foreach ($clients as $client)
                <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
            @endforeach
            </select>

            <label class="form-label">状態</label>
            <select id="" name="status" class="form-select">
                <option value="0">作業中</option>
                <option value="1">完了</option>
                <option value="2">連絡待ち</option>
                <option value="3">保留</option>
                <option value="4">打診中</option>
                <option value="5">メンテナンス</option>
                <option value="6">キャンセル</option>
                <option value="7">見積中</option>
            </select>

            <label class="form-label">開始日</label>
            <input type="date" name="start_date" class="form-control">

            <label class="form-label">終了日</label>
            <input type="date" name="end_date" class="form-control">

            <label class="form-label">支払日</label>
            <input type="date" name="payment_date" class="form-control">

            <label class="form-label">請求金額</label>
            <input type="number" name="price" class="form-control">
        </div>
        <div class="col-6">
            <label class="form-label">詳細</label>
            <textarea name="description" id="description" class="form-control" style="width: 100%; height: 480px;"></textarea>
        </div>
        <div class="col-6">
            <label class="form-label">プレビュー</label>
            <div id="preview" style="width: 100%; height: 480px; overflow-y: scroll;"></div>
        </div>
    </div>
</form>

@endsection
