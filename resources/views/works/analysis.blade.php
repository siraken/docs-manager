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

<div class="row">
    <div class="col-12">
        <a href="{{ route('works.index') }}" class="btn btn-light border">
            戻る
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h1>分析</h1>
        <div class="row">
            <div class="col-4">
                <div class="input-group mb-3">
                    <select id="" name="year" class="form-select" onchange="searchQuery('year', this.value)">
                    @for ($i = 2020; $i < (intval(date('Y', strtotime('+2 years')))); $i++)
                        <option value="{{ $i }}" {{ $i == $year ? 'selected' : '' }}>{{ $i }}年</option>
                    @endfor
                    </select>
                    <select id="" name="month" class="form-select" onchange="searchQuery('month', this.value)">
                    @for ($i = 1; $i < 13; $i++)
                        <option value="{{ $i }}" {{ $i == $month ? 'selected' : '' }}>{{ $i }}月</option>
                    @endfor
                    </select>
                  </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>案件名</th>
                        <th>請求金額</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($works as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td>￥{{ number_format($row['price']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p>Total: ￥{{ number_format($total_price) }}</p>
        </div>
    </div>
</div>

@endsection
