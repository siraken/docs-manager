@extends('layouts/default')
@section('page')

<div class="row">
    <div class="col-12">
        <a href="{{ route('items.create') }}" class="btn btn-light border">
            <i class="bi-plus-circle me-2"></i>品目の新規登録
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 35%;">品番・品名</th>
                    <th style="width: 10%;">単位</th>
                    <th style="width: 20%;">単価</th>
                    <th style="width: 20%;">税区分</th>
                    <th style="width: 15%;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['unit'] }}</td>
                    <td>{{ number_format($row['cost']) }}円</td>
                    <td>
                        <?php
                            if ($row['tax'] == null || $row['tax'] == 0) {
                                $taxType = 0;
                            } else {
                                $taxType = $row['tax'];
                            }
                            switch ($taxType) {
                                case 0:  echo     '-'; break;
                                case 1:  echo   '10%'; break;
                                case 2:  echo '軽減8%'; break;
                                case 3:  echo    '8%'; break;
                                case 4:  echo '対象外'; break;
                                case 5:  echo    '5%'; break;
                                default: echo     '-'; break;
                            }
                        ?>
                    </td>
                    <td>
                        <a href="{{ route('items.edit', ['id' => $row['id']]) }}">編集</a>
                        <a href="javascript:void(0);" onclick="deleteItem({{ $row['id'] }});">削除</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
