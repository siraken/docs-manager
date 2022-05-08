@extends('layouts/default')
@section('page')


@csrf

<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('clients.create') }}" class="btn btn-light border">取引先を新規作成</a>
        <a href="{{ route('clients.truncate') }}" class="btn btn-light border">Truncate</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>取引先名</th>
                </tr>
            </thead>
            <tbody>
                @if ($clients)
                @foreach ($clients as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td><a href="{{ route('clients.edit', ['id' => $row['id']]) }}">{{ $row->name }}</a></td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="2">No data</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@endsection
