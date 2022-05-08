@extends('layouts/default')
@section('page')

@csrf

<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('orders.index') }}" class="btn btn-light border">戻る</a>
        <a href="{{ route('orders.index') }}" class="btn btn-danger border">ごみ箱を空にする</a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <table class="table iv-table">
            <thead>
                <tr>
                    <th class="">ステータス</th>
                    <th class="">文書</th>
                    <th class="">発行日</th>
                    <th class="">有効期限</th>
                    <th class="">金額</th>
                    <th class=""></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $row): ?>
                <tr>
                    {{-- ステータス --}}
                    <td>
                        {{-- 発行状況 --}}
                        <?php
                        $issued_status = '';
                        $issued_status_class = ' ';
                        switch ($row['is_issued']) {
                            case 0:
                                $issued_status = '未発行';
                                $issued_status_class .= '';
                                break;
                            case 1:
                                $issued_status = '発行済み';
                                $issued_status_class .= 'set';
                                break;
                            default:
                                $issued_status = '不明';
                                $issued_status_class .= '';
                                break;
                        }
                        ?>
                        <span class="status{{ $issued_status_class }}"
                            onclick="slipSetter.status(this, 'issued', {{ $row['id'] }}, {{ empty($row['is_issued']) ? '0' : $row['is_issued'] }})">{!!
                            $row['is_issued'] === 1 ? '<i class="fa fa-fw fa-check"></i>' : '' !!}{{ $issued_status
                            }}</span>

                        {{-- 受注状況 --}}
                        <?php
                        $ordered_status = '';
                        $ordered_status_class = ' ';
                        switch ($row['is_ordered']) {
                            case 0:
                                $ordered_status = '未受注';
                                $ordered_status_class .= '';
                                break;
                            case 1:
                                $ordered_status = '受注済み';
                                $ordered_status_class .= 'set';
                                break;
                            case 2:
                                $ordered_status = '失注';
                                $ordered_status_class .= 'miss';
                                break;
                            default:
                                $ordered_status = '不明';
                                $ordered_status_class .= '';
                                break;
                        }
                        ?>
                        <span class="status{{ $ordered_status_class }}"
                            onclick="slipSetter.status(this, 'ordered', {{ $row['id'] }}, {{ empty($row['is_ordered']) ? '0' : $row['is_ordered'] }})">{!!
                            $row['is_ordered'] === 1 ? '<i class="fa fa-fw fa-check"></i>' : '' !!}{{ $ordered_status
                            }}</span>
                    </td>
                    {{-- 文書 --}}
                    <td>
                        <a href="{{ route('orders.edit', ['id' => $row['id']]) }}">
                            @if (!empty($row['title']))
                            {{ $row['title'] }}（{{ $row['destination'] }}）
                            @else
                            {{ $row['destination'] }}
                            @endif
                        </a><br>
                        <small style="color: #777;">#{{ $row['order_no'] }}</small>
                    </td>
                    {{-- 発行日 --}}
                    <td>
                        {{ date('Y/m/d', strtotime($row['issued_date'])) }}
                    </td>
                    {{-- 有効期限 --}}
                    <td>
                        {{ !empty($row['exp_date']) ? date('Y/m/d', strtotime($row['exp_date'])) : '-' }}
                    </td>
                    {{-- 金額 --}}
                    <td>
                        <b>{{ empty($row['total_price']) ? 0 : number_format($row['total_price']) }}円</b>
                    </td>
                    {{-- アクション --}}
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-light border dropdown-toggle" type="button" id="dropdownMenuButton1"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear-fill"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                @if (!empty($row->note))
                                <li><small class="dropdown-item disabled">{{ $row->note }}</small></li>
                                @endif
                                <li><a class="dropdown-item"
                                        href="{{ route('orders.restore', ['id' => $row['id']]) }}">ごみ箱から戻す</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        @if (empty($orders))
        <p style="text-align: center;">データがありません</p>
        @endif
    </div>
</div>

@endsection
