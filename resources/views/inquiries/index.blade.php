@extends('layouts/default')
@section('page')


@csrf

<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('inquiries.create') }}" class="btn btn-light border">新規問い合わせ</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>タイトル</th>
                </tr>
            </thead>
            <tbody>
                @if ($inquiries)
                @foreach ($inquiries as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td><a href="{{ route('inquiries.view', ['id' => $row['id']]) }}">{{ $row->title }}</a></td>
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
