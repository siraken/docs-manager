@extends('layouts/default')
@section('page')

<div class="row">
    <div class="col-12">
        <a href="{{ route('users.create') }}" class="btn btn-light border">
            <i class="bi-plus-circle me-2"></i>ユーザーの新規登録
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 30%;">名前</th>
                        <th style="width: 10%;">ユーザー名</th>
                        <th style="width: 20%;">メールアドレス</th>
                        <th style="width: 15%;">作成日</th>
                        <th style="width: 15%;">変更日</th>
                        <th style="width: 10%;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['username'] }}</td>
                        <td>{{ $row['email'] }}</td>
                        <td>{{ $row['created_at'] }}</td>
                        <td>{{ $row['updated_at'] }}</td>
                        <td>
                            <a href="{{ route('users.edit', ['id' => $row['id']]) }}">編集</a>
                            <a href="javascript:void(0);" onclick="deleteItem({{ $row['id'] }});">削除</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
