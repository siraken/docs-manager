@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm" class="">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('works.index') }}" class="btn btn-light border">戻る</a>
            <button class="btn btn-light border" type="button" data-bs-toggle="modal"
                data-bs-target="#exampleModal">保存する</button>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            @csrf

            <label class="form-label">案件名<span class="badge bg-danger ms-1">必須</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $work['name']) }}" required>

            <label class="form-label">取引先</label>
            <input type="text" name="client_id" class="form-control" value="{{ old('client_id', $work['client_id']) }}" required>

            <label class="form-label">状態</label>
            <select id="" name="status" class="form-select">
                <option value="0" {{ old('status', $work['status'])===0 ? 'selected' : '' }}>作業中</option>
                <option value="1" {{ old('status', $work['status'])===1 ? 'selected' : '' }}>完了</option>
                <option value="2" {{ old('status', $work['status'])===2 ? 'selected' : '' }}>連絡待ち</option>
                <option value="3" {{ old('status', $work['status'])===3 ? 'selected' : '' }}>保留</option>
                <option value="4" {{ old('status', $work['status'])===4 ? 'selected' : '' }}>打診中</option>
                <option value="5" {{ old('status', $work['status'])===5 ? 'selected' : '' }}>メンテナンス</option>
                <option value="6" {{ old('status', $work['status'])===6 ? 'selected' : '' }}>キャンセル</option>
                <option value="7" {{ old('status', $work['status'])===7 ? 'selected' : '' }}>見積中</option>
            </select>

            <label class="form-label">開始日</label>
            <input type="date" name="start_date" class="form-control"
                value="{{ old('start_date', $work['start_date']) }}">

            <label class="form-label">終了日</label>
            <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $work['end_date']) }}">

            <label class="form-label">支払日</label>
            <input type="date" name="payment_date" class="form-control"
                value="{{ old('payment_date', $work['payment_date']) }}">

            <label class="form-label">請求金額</label>
            <div class="input-group">
                <span class="input-group-text">¥</span>
                <input type="number" name="price" class="form-control" value="{{ old('price', $work['price']) }}">
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="works-modal"></div>

</form>

@endsection
