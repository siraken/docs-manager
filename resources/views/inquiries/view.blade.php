@extends('layouts/default')
@section('page')

@csrf

<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('inquiries.index') }}" class="btn btn-light border">戻る</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <h1>{{ $inquiry->title }}</h1>
        <hr>
        <p class="lead">
            {!! nl2br($inquiry->body) !!}
        </p>
    </div>
</div>

@endsection
