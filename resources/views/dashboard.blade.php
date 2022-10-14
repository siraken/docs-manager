@extends('layouts/default')
@section('page')

<div class="row mb-3">
    <div class="col-12">
        <h1>Welcome, {{ session('name') }}!</h1>
    </div>
</div>

@endsection
