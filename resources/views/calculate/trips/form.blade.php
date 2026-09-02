@extends('layouts/default')
@section('page')

<form method="post">
    <x-page-header title="出張申請 / 申請">
        <x-slot:actions>
            <x-button :href="route('trips.index')" icon="arrow-left">戻る</x-button>
            <x-button type="submit" variant="primary" icon="check-lg">保存する</x-button>
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
                <x-label for="rel_id" required>管理ID</x-label>
                <x-input id="rel_id" name="rel_id" value="{{ old('rel_id', date('Ymd') . '-Num') }}" required />
            </div>

            <div>
                <x-label for="dir" required>出張先</x-label>
                <x-input id="dir" name="dir" value="{{ old('dir') }}" required />
            </div>

            <div>
                <x-label for="purpose" required>目的</x-label>
                <x-textarea id="purpose" name="purpose" rows="6" required>{{ old('purpose') }}</x-textarea>
            </div>

            <div>
                <x-label for="price">金額</x-label>
                <x-input type="number" id="price" name="price" value="{{ old('price') }}" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="date_from" required>出発日</x-label>
                    <x-input type="date" id="date_from" name="date_from"
                             value="{{ old('date_from', date('Y-m-d', strtotime('+1 day'))) }}" required />
                </div>
                <div>
                    <x-label for="date_to" required>帰着日</x-label>
                    <x-input type="date" id="date_to" name="date_to"
                             value="{{ old('date_to', date('Y-m-d', strtotime('+1 week'))) }}" required />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="apply_date" required>申請日</x-label>
                    <x-input type="date" id="apply_date" name="apply_date" value="{{ old('apply_date', date('Y-m-d')) }}" required />
                </div>
                <div>
                    <x-label for="apply_person" required>申請者氏名</x-label>
                    <x-input id="apply_person" name="apply_person" value="{{ old('apply_person', session('name')) }}" required />
                </div>
            </div>
        </x-card>
    </div>
</form>

@endsection
