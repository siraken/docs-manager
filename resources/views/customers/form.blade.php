@extends('layouts/default')
@section('page')

@php
    $fields = [
        ['name' => 'email', 'label' => 'Email', 'required' => true],
        ['name' => 'phone', 'label' => 'Phone', 'required' => true],
        ['name' => 'post_code', 'label' => 'Post code', 'required' => true],
        ['name' => 'address', 'label' => 'Address', 'required' => true],
        ['name' => 'city', 'label' => 'City', 'required' => true],
        ['name' => 'state', 'label' => 'State', 'required' => true],
        ['name' => 'country', 'label' => 'Country', 'required' => true],
    ];
@endphp

<form method="post" action="" autocomplete="off" id="MainForm">
    <x-page-header title="顧客の編集">
        <x-slot:actions>
            <x-button :href="route('customers.index')" icon="arrow-left">Back</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">Save</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-2xl">
        <x-card class="space-y-5">
            @csrf

            <div>
                <x-label for="name" required>Name</x-label>
                <x-input id="name" name="name" value="{{ old('name', $customer['name']) }}" required />
            </div>

            <x-toggle name="is_company" id="is-company-switch" label="This is a company"
                      :checked="(bool) old('is_company', $customer->is_company)" />

            @foreach ($fields as $field)
                <div>
                    <x-label :for="$field['name']" :required="$field['required']">{{ $field['label'] }}</x-label>
                    <x-input :id="$field['name']" :name="$field['name']"
                             value="{{ old($field['name'], $customer[$field['name']]) }}" required />
                </div>
            @endforeach

            <div>
                <x-label for="note">Note</x-label>
                <x-textarea id="note" name="note">{{ old('note', $customer['note']) }}</x-textarea>
            </div>
        </x-card>
    </div>
</form>

@endsection
