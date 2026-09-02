@extends('layouts/default')
@section('page')

<form method="post">
    <x-page-header title="出張申請 / 申請">
        <x-slot:actions>
            <x-button :href="route('trips.index')" icon="arrow-left">戻る</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">保存する</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-2xl">
        <x-card class="space-y-5">
            @csrf

            <div>
                <x-label for="rel_id">管理ID</x-label>
                <x-input id="rel_id" name="rel_id" value="{{ date('Ymd') . '-Num' }}" />
            </div>

            <div>
                <x-label for="dir">出張先</x-label>
                <x-input id="dir" name="dir" />
            </div>

            <div>
                <x-label for="purpose">目的</x-label>
                <x-textarea id="purpose" name="purpose" rows="6" />
            </div>

            <div>
                <x-label for="price">金額</x-label>
                <x-input type="number" id="price" name="price" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="date_from">出発日</x-label>
                    <x-input type="date" id="date_from" name="date_from" value="{{ date('Y-m-d', strtotime('+1 day')) }}" />
                </div>
                <div>
                    <x-label for="date_to">帰着日</x-label>
                    <x-input type="date" id="date_to" name="date_to" value="{{ date('Y-m-d', strtotime('+1 week')) }}" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="apply_date">申請日</x-label>
                    <x-input type="date" id="apply_date" name="apply_date" value="{{ date('Y-m-d') }}" />
                </div>
                <div>
                    <x-label for="apply_person">申請者氏名</x-label>
                    <x-input id="apply_person" name="apply_person" />
                </div>
            </div>
        </x-card>
    </div>
</form>

@endsection
