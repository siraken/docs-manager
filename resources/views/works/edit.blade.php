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
        <div class="col-6">
            @csrf

            <label class="form-label">案件名<span class="badge bg-danger ms-1">必須</span></label>
            <input type="text" name="name" class="form-control" value="{{ $work['name'] }}" required>

            <label class="form-label">取引先</label>
            <select id="" name="client_id" class="form-select">
            @foreach ($clients as $client)
                <option value="{{ $client['id'] }}" {{ $client['id'] === $work['client_id'] ? 'selected' : '' }}>{{ $client['name'] }}</option>
            @endforeach
            </select>

            <label class="form-label">状態</label>
            <select id="" name="status" class="form-select">
                <option value="0" {{ $work['status'] === 0 ? 'selected' : '' }}>作業中</option>
                <option value="1" {{ $work['status'] === 1 ? 'selected' : '' }}>完了</option>
                <option value="2" {{ $work['status'] === 2 ? 'selected' : '' }}>連絡待ち</option>
                <option value="3" {{ $work['status'] === 3 ? 'selected' : '' }}>保留</option>
                <option value="4" {{ $work['status'] === 4 ? 'selected' : '' }}>打診中</option>
                <option value="5" {{ $work['status'] === 5 ? 'selected' : '' }}>メンテナンス</option>
                <option value="6" {{ $work['status'] === 6 ? 'selected' : '' }}>キャンセル</option>
                <option value="7" {{ $work['status'] === 7 ? 'selected' : '' }}>見積中</option>
            </select>

            <label class="form-label">開始日</label>
            <input type="date" name="start_date" class="form-control" value="{{ $work['start_date'] }}">

            <label class="form-label">終了日</label>
            <input type="date" name="end_date" class="form-control" value="{{ $work['end_date'] }}">

            <label class="form-label">支払日</label>
            <input type="date" name="payment_date" class="form-control" value="{{ $work['payment_date'] }}">

            <label class="form-label">請求金額</label>
            <input type="number" name="price" class="form-control" value="{{ $work['price'] }}">
        </div>
    </div>
</form>

@endsection
