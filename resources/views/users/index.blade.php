@extends('layouts/default')
@section('page')

<div class="row">
    <div class="col-12">
        <a href="{{ route('users.create') }}" class="btn btn-secondary">ユーザーの新規登録</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 30%;">名前</th>
                        <th style="width: 30%;">メールアドレス</th>
                        <th style="width: 30%;">変更日</th>
                        <th style="width: 10%;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['email'] }}</td>
                        <td>{{ $row['updated_at'] }}</td>
                        <td>
                            <a class="btn btn-secondary btn-sm" href="{{ route('users.edit', ['id' => $row['id']]) }}">編集</a>
                            <a class="btn btn-secondary btn-sm" href="javascript:void(0);" onclick="deleteItem({{ $row['id'] }});">削除</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
