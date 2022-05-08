@extends('layouts/default')
@section('page')

<div class="row mb-3">
	<div class="col-12">
        <h4 class="heading">旅費精算</h4>
		<a class="btn btn-light border" href="{{ route('expenses.create') }}"><i class="bi bi-plus-circle me-2"></i>旅費精算をする</a>
        {{-- CSVモーダル開く --}}
        <button type="button" class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#csvImportModal">
            <i class="bi bi-download me-2"></i>CSV取り込み
        </button>
        {{-- CSV取り込みモーダル --}}
        <div class="modal fade" id="csvImportModal" tabindex="-1" aria-labelledby="csvImportModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('expenses.import') }}" method="post" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="csvImportModalLabel">CSV取り込み</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @csrf
                            <div class="form-group">
                                <label for="csvFormFile" class="form-label">CSVを選択してください</label>
                                <input id="csvFormFile" name="csv" class="form-control" type="file" accept=".csv" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="form-check form-switch">
                                <input type="hidden" name="header" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="toggleCsvHeader" name="header" value="1" checked>
                                <label class="form-check-label" for="toggleCsvHeader">ヘッダーあり</label>
                            </div>
                            <button type="submit" class="btn btn-primary">取り込み</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
	</div>
</div>

<div class="row">

    <div class="col-12">
        <table class="table">
            <thead>
                <tr>
                    <th>申請日</th>
                    <th>出張先</th>
                    <th class="hide-on-small-only">目的</th>
                    <th class="hide-on-small-only">精算日</th>
                    <th>申請者</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenses as $expense)
                <tr>
                    <td class="align-middle">{{ $expense->apply_date }}</td>
                    <td class="align-middle">{{ $expense->dir }}</td>
                    <td class="align-middle">{{ $expense->purpose }}</td>
                    <td class="align-middle">{{ $expense->pay_date }}</td>
                    <td class="align-middle">{{ $expense->apply_person }}</td>
                    <td class="align-middle">
                        <a class="btn btn-light border" href="{{ route('expenses.pdf', $expense->id) }}"><i class="bi bi-eye me-2"></i>PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection
