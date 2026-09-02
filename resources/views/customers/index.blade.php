@extends('layouts/default')
@section('page')

<x-page-header title="顧客管理">
    <x-slot:actions>
        <x-button :href="route('customers.create')" variant="primary" icon="plus-lg">顧客の新規登録</x-button>
    </x-slot:actions>
</x-page-header>

@if (count($customers) === 0)
    <x-empty-state>顧客がまだ登録されていません</x-empty-state>
@else
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Address</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3"><span class="sr-only">操作</span></th>
        </x-slot:head>

        @foreach ($customers as $row)
            <tr class="transition hover:bg-slate-50">
                <td class="px-4 py-3 align-middle">
                    <span class="font-medium text-slate-900">{{ $row->name }}</span>
                    @if ($row->is_company)
                        <x-badge color="brand" class="ml-1.5">法人</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3 align-middle text-slate-600">{{ $row->city . $row->state . $row->country }}</td>
                <td class="px-4 py-3 align-middle text-slate-600">{{ $row->email }}</td>
                <td class="px-4 py-3 text-right align-middle">
                    <x-button :href="route('customers.edit', ['id' => $row->id])" size="sm" icon="pencil">編集</x-button>
                </td>
            </tr>
        @endforeach
    </x-table>
@endif

@endsection
