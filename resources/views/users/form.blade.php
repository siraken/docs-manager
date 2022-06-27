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
            <input type="text" name="name" class="form-control" value="{{ old('name', $user['name']) }}" required>

            <label class="form-label">ユーザー名</label>
            <input type="text" name="username" class="form-control" value="">

            <label class="form-label">メールアドレス<span class="badge bg-danger ms-1">必須</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user['email']) }}">

            <label class="form-label">パスワード<span class="badge bg-danger ms-1">必須</span></label>
            <input type="password" name="password" class="form-control" value="">

            <label class="form-label">NFC Card</label>
            <div class="input-group">
                <button type="button" class="btn btn-primary"
                    onclick="novalumo.setNfcNumber(document.getElementById('nfc_number'))">Scan</button>
                <input type="password" name="nfc_serial_number" id="nfc_number" class="form-control"
                    value="{{ old('nfc_serial_number', $user['nfc_serial_number']) }}">
            </div>

            <label class="form-label">NFC PIN</label>
            <input type="password" name="nfc_pin" class="form-control" value="{{ old('nfc_pin', $user['nfc_pin']) }}">
        </div>
    </div>
</form>

@endsection
