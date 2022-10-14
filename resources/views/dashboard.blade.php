@extends('layouts/default')
@section('page')

<div class="row mb-3">
    <div class="col-12">
        <h1 class="h3">Welcome, {{ session('name') }}</h1>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="d-flex gap-3">
            <a href="{{ route('trips.index') }}" class="btn btn-secondary shadow-sm p-3" style="">出張申請</a>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary shadow-sm p-3" style="">出張旅費精算</a>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary shadow-sm p-3" style="">発注書作成</a>
        </div>
    </div>
</div>

@endsection
