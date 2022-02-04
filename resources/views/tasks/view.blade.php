@extends('layouts/default')
@section('page')

<div class="row">
    <div class="col-12">
        <a href="/tasks/" class="btn btn-light border">
            <i class="bi-plus-circle me-2"></i>戻る
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h1>{{ $task->title }}</h1>
        {!! $task->description !!}
    </div>
</div>

@endsection
