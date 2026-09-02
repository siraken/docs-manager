@extends('layouts/default')
@section('page')

{{-- 状態の選択肢はドメインの ProjectStatus が唯一の定義。
     移行前はこのビューに 8 種類、一覧のコントローラに 3 種類という
     食い違った定義が別々に書かれていた。 --}}

<form method="post" action="" autocomplete="off" id="MainForm">
    <x-page-header :title="$isNew ? '案件の新規登録' : '案件の編集'">
        <x-slot:actions>
            <x-button :href="route('projects.index')" icon="arrow-left">戻る</x-button>
            {{-- 受注前確認モーダル (Svelte) を開く。Bootstrap の data-bs-toggle の置き換え --}}
            <x-button variant="primary" icon="check-lg"
                      onclick="window.dispatchEvent(new CustomEvent('open-projects-modal'))">保存する</x-button>
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
                <x-label for="name" required>案件名</x-label>
                <x-input id="name" name="name" value="{{ old('name', $project->name) }}" required />
            </div>

            <div>
                <x-label for="client_id">取引先</x-label>
                <x-input id="client_id" name="client_id" value="{{ old('client_id', $project->clientId) }}" />
            </div>

            <div>
                <x-label for="status">状態</x-label>
                <x-select id="status" name="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected((int) old('status', $project->statusValue) === $value)>{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <x-label for="start_date">開始日</x-label>
                    <x-input type="date" id="start_date" name="start_date" value="{{ old('start_date', $project->startDate) }}" />
                </div>
                <div>
                    <x-label for="end_date">終了日</x-label>
                    <x-input type="date" id="end_date" name="end_date" value="{{ old('end_date', $project->endDate) }}" />
                </div>
                <div>
                    <x-label for="payment_date">支払日</x-label>
                    <x-input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', $project->paymentDate) }}" />
                </div>
            </div>

            <div>
                <x-label for="price">請求金額</x-label>
                <div class="flex">
                    <span class="inline-flex items-center rounded-l-lg bg-slate-100 px-3 text-sm text-slate-500 ring-1 ring-inset ring-slate-300">¥</span>
                    <x-input type="number" id="price" name="price" value="{{ old('price', $project->price) }}" class="rounded-l-none" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- 受注前確認モーダル。app.ts の ISLANDS が ProjectsModal.svelte をここにマウントする --}}
    <div id="projects-modal"></div>
</form>

@endsection
