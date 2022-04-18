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
            <table class="table">
                <thead>
                    <tr>
                        <th>Info</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                    <tr>
                        <td>
                            <p class="m-0"><span class="badge rounded-pill bg-primary">{{ $log->status }}</span></p>
                            <p class="m-0">
                                <span class="font-weight-bold">{{ $log->user_id }}</span>
                            </p>
                        </td>
                        <td>
                            <p class="m-0">
                                <span class="text-muted">{{ $log->access_date }}</span>
                            </p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
