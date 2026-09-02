@extends('layouts/default')
@section('page')

<script>
    const url = new URL(location);

    function searchQuery(name, value) {
        const params = new URLSearchParams(url.search);
        params.set(name, value);
        url.search = params.toString();
        location.href = url.toString();
    }
</script>

<x-page-header title="売上分析">
    <x-slot:actions>
        <x-button :href="route('projects.index')" icon="arrow-left">戻る</x-button>
    </x-slot:actions>
</x-page-header>

<x-card class="mb-6">
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <x-label for="search-column">検索条件</x-label>
            {{-- 集計対象にできる日付は SalesAnalysisCriteria::DATE_FIELDS が唯一の定義。
                 ここに無い値はユースケース側で弾かれる。 --}}
            <x-select id="search-column" name="type" onchange="searchQuery('type', this.value)">
                @foreach ($columns as $field => $label)
                    <option value="{{ $field }}" @selected($field === $search_column)>{{ $label }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <x-label for="search-year">年</x-label>
            <x-select id="search-year" name="year" onchange="searchQuery('year', this.value)">
                @for ($i = 2020; $i < intval(date('Y', strtotime('+2 years'))); $i++)
                    <option value="{{ $i }}" @selected($i === $year)>{{ $i }}年</option>
                @endfor
            </x-select>
        </div>
        <div>
            <x-label for="search-month">月</x-label>
            <x-select id="search-month" name="month" onchange="searchQuery('month', this.value)">
                @for ($i = 1; $i < 13; $i++)
                    <option value="{{ $i }}" @selected($i === $month)>{{ $i }}月</option>
                @endfor
            </x-select>
        </div>
    </div>
</x-card>

<x-table>
    <x-slot:head>
        <th class="px-4 py-3">案件名</th>
        <th class="px-4 py-3 text-right">請求金額</th>
    </x-slot:head>

    @foreach ($projects as $row)
        <tr class="transition hover:bg-slate-50">
            <td class="px-4 py-3 text-slate-700">{{ $row->name }}</td>
            <td class="px-4 py-3 text-right font-medium whitespace-nowrap text-slate-900 tabular">￥{{ $row->priceLabel }}</td>
        </tr>
    @endforeach

    <tr class="bg-slate-50 font-semibold">
        <td class="px-4 py-3 text-slate-700">合計</td>
        <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">￥{{ number_format($total_price) }}</td>
    </tr>
</x-table>

@endsection
