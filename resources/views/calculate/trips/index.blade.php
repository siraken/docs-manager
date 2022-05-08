@extends('layouts/default')
@section('page')

<div class="row mb-3">
    <div class="col-12">
        <h4 class="heading">出張申請</h4>
        <a class="btn btn-light border" href="{{ route('trips.create') }}"><i
                class="bi bi-plus-circle me-2"></i>出張申請をする</a>
        {{-- CSVモーダル開く --}}
        <button type="button" class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#csvImportModal">
            <i class="bi bi-download me-2"></i>CSV取り込み
        </button>
        {{-- CSV取り込みモーダル --}}
        <div class="modal fade" id="csvImportModal" tabindex="-1" aria-labelledby="csvImportModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('trips.import') }}" method="post" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="csvImportModalLabel">CSV取り込み</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @csrf
                            <div class="form-group">
                                <label for="csvFormFile" class="form-label">CSVを選択してください</label>
                                <input id="csvFormFile" name="csv" class="form-control" type="file" accept=".csv"
                                    required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="form-check form-switch">
                                <input type="hidden" name="header" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="toggleCsvHeader"
                                    name="header" value="1" checked>
                                <label class="form-check-label" for="toggleCsvHeader">ヘッダーあり</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Import</button>
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
                    <th class="hide-on-small-only">出発日</th>
                    <th>申請者</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($trips as $row)
                <tr>
                    <td class="align-middle">
                        <?= date('Y/m/d', strtotime($row->apply_date)) ?>
                    </td>
                    <td class="align-middle">
                        <?= ($row->dir) ?>
                    </td>
                    <td class="align-middle" class="hide-on-small-only">
                        <?= mb_strimwidth($row->purpose, 0, 30, "...") ?>
                    </td>
                    <td class="align-middle" class="hide-on-small-only">
                        <?= date('Y/m/d', strtotime($row->date_from)) ?>
                    </td>
                    <td class="align-middle">
                        <?= ($row->apply_person) ?>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-light border dropdown-toggle" type="button" id="dropdownMenuButton1"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear-fill"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <li><a class="dropdown-item"
                                        href="{{ route('trips.pdf', ['id' => $row['id']]) }}">PDF</a></li>
                                <li><a class="dropdown-item"
                                        href="{{ route('orders.delete', ['id' => $row['id']]) }}">ごみ箱に入れる</a></li>
                            </ul>
                        </div>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

</div>

@endsection
