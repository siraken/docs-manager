@extends('layouts/default')
@section('page')

@php
    $fields = [
        ['name' => 'email', 'label' => 'Email'],
        ['name' => 'phone', 'label' => 'Phone'],
        ['name' => 'post_code', 'label' => 'Post code'],
        ['name' => 'address', 'label' => 'Address'],
        ['name' => 'city', 'label' => 'City'],
        ['name' => 'state', 'label' => 'State'],
        ['name' => 'country', 'label' => 'Country'],
    ];
@endphp

<form method="post" action="" autocomplete="off" id="MainForm">
    <x-page-header :title="$isNew ? '顧客の新規登録' : '顧客の編集'">
        <x-slot:actions>
            <x-button :href="route('customers.index')" icon="arrow-left">Back</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">Save</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="mb-6 max-w-2xl rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-2xl">
        <x-card class="space-y-5">
            @csrf

            <div>
                <x-label for="name" required>Name</x-label>
                <x-input id="name" name="name" value="{{ old('name', $customer->name) }}" required />
            </div>

            <x-toggle name="is_company" id="is-company-switch" label="This is a company"
                      :checked="(bool) old('is_company', $customer->isCompany)" />

            @foreach ($fields as $field)
                <div>
                    <x-label :for="$field['name']">{{ $field['label'] }}</x-label>
                    <x-input :id="$field['name']" :name="$field['name']"
                             value="{{ old($field['name'], $customer->formValue($field['name'])) }}" />
                </div>
            @endforeach

            <div>
                <x-label for="note">Note</x-label>
                <x-textarea id="note" name="note">{{ old('note', $customer->note) }}</x-textarea>
            </div>
        </x-card>
    </div>
</form>

@endsection
