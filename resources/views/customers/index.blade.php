@extends('layouts/default')
@section('page')

<div class="row">
    <div class="col-12">
        <a href="{{ route('customers.create') }}" class="btn btn-secondary">顧客の新規登録</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Name</th>
                        <th style="width: 30%;">Address</th>
                        <th style="width: 30%;">Email</th>
                        <th style="width: 10%;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $row)
                    <tr>
                        <td>{{ $row->name }} {{ $row->is_company ? "(C)" : "" }}</td>
                        <td>{{ $row->city . $row->state . $row->country }}</td>
                        <td>{{ $row->email }}</td>
                        <td>
                            <a class="btn btn-secondary btn-sm" href="{{ route('customers.edit', ['id' => $row->id]) }}">編集</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
