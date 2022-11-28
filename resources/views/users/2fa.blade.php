@extends('layouts/default')
@section('page')

<form method="post" action="" autocomplete="off" id="MainForm">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('users.edit', ["id" => $user->id]) }}" class="btn btn-secondary">戻る</a>
            <button class="btn btn-secondary" type="submit">保存する</button>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            @csrf

            <div class="mb-3">
                <label class="form-label">QR Code</label>
                <img src="{{ $qrCodeUrl }}" alt="">
            </div>

            <div class="mb-3">
                <label class="form-label">OTP</label>
                <input type="text" name="code" class="form-control" value="" required>
            </div>
        </div>
    </div>
</form>

@endsection
