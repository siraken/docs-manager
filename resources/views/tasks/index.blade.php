@extends('layouts/default')
@section('page')

<div class="row">
    <div class="col-12">
        <a href="/tasks/create" class="btn btn-light border">
            <i class="bi-plus-circle me-2"></i>タスクの新規登録
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 35%;">タイトル</th>
                    <th style="width: 10%;">詳細</th>
                    <th style="width: 20%;">取引先</th>
                    <th style="width: 15%;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $row)
                <tr>
                    <td><a href="/tasks/view/{{ $row['id'] }}">{{ $row['title'] }}</a></td>
                    <td>{{ $row['description'] }}</td>
                    <td>{{ $row['client_id'] }}</td>
                    <td>
                        <a href="/tasks/edit/{{ $row['id'] }}">編集</a>
                        <a href="javascript:void(0);" onclick="deleteItem({{ $row['id'] }});">削除</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
