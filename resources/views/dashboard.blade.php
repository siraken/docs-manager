@extends('layouts/default')
@section('page')

<div class="row mb-3">
    <div class="col-12">
        <h1>Welcome, {{ session('name') }}!</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="border shadow-sm p-3">
            <h2>Hello</h2>
        </div>
    </div>
    <div class="col-md-6">
        <div class="border shadow-sm p-3">
            <h2>ログイン履歴</h2>
            <ul>
            @foreach ($logs as $log)
                <li>{{ $log->status }}: {{ $log->user_id }} | {{ $log->access_date }}</li>
            @endforeach
            </ul>
        </div>
    </div>
</div>

@endsection
