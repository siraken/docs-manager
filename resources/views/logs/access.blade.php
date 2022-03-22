@extends('layouts/default')
@section('page')

<div class="row">
    <div class="col-12">
        <a href="{{ route('logs.access') }}" class="btn btn-light border">
            {{-- <i class="bi-plus-circle me-2"></i>品目の新規登録 --}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 10%;">ユーザー</th>
                    <th style="width: 35%;">ステータス</th>
                    <th style="width: 15%;">日付</th>
                    <th style="width: 30%;">IP</th>
                    <th style="width: 10%;">承認</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $row)
                <tr>
                    <td>{{ $row['user_id'] || '不明' }}</td>
                    <td><a href="{{ route('logs.access', ['id' => $row['id']]) }}">{{ $row['status'] }}</a></td>
                    <td>{{ $row['access_date'] }}</td>
                    <td>{!! $row['ip_address'] !!}</td>
                    <td>{{ $row['is_suspicious'] ? 'No' : 'Yes' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
