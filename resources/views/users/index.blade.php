@extends('layouts/default')
@section('page')

<x-page-header title="ユーザー管理">
    <x-slot:actions>
        <x-button :href="route('users.create')" variant="primary" icon="plus-lg">ユーザーの新規登録</x-button>
    </x-slot:actions>
</x-page-header>

@if (count($users) === 0)
    <x-empty-state>ユーザーがまだ登録されていません</x-empty-state>
@else
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3">名前</th>
            <th class="px-4 py-3">メールアドレス</th>
            <th class="px-4 py-3">変更日</th>
            <th class="px-4 py-3"><span class="sr-only">操作</span></th>
        </x-slot:head>

        @foreach ($users as $row)
            <tr class="transition hover:bg-slate-50">
                <td class="px-4 py-3 align-middle font-medium text-slate-900">{{ $row['name'] }}</td>
                <td class="px-4 py-3 align-middle text-slate-600">{{ $row['email'] }}</td>
                <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{{ $row['updated_at'] }}</td>
                <td class="px-4 py-3 text-right align-middle">
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('users.edit', ['id' => $row['id']])" size="sm" icon="pencil">編集</x-button>
                        <x-button size="sm" variant="danger" icon="trash"
                                  onclick="deleteItem({{ $row['id'] }});">削除</x-button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
@endif

@endsection
