@extends('layouts/default')
@section('page')

<div class="row">
    <div class="col-12">
        <a href="{{ route('projects.create') }}" class="btn btn-secondary">新規案件</a>
        <a href="{{ route('projects.analysis') }}" class="btn btn-secondary">分析</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>案件名</th>
                        <th>取引先</th>
                        <th>状態</th>
                        <th>開始日</th>
                        <th>終了日</th>
                        <th>支払日</th>
                        <th>請求金額</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $row)
                    <tr>
                        <td><a href="{{ route('projects.edit', ['id' => $row['id']]) }}">{{ $row['name'] }}</a></td>
                        <td>{{ $row['client_id'] }}</td>
                        <td>{{ $row['status'] }}</td>
                        <td>{{ date('Y/m/d', strtotime($row['start_date'])) }}</td>
                        <td>{{ date('Y/m/d', strtotime($row['end_date'])) }}</td>
                        <td>{{ date('Y/m/d', strtotime($row['payment_date'])) }}</td>
                        <td>￥{{ number_format($row['price']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
