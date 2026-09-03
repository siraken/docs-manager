<?php
$jira_url = 'https://novalumo.atlassian.net/browse/';
?>

@extends('layouts.app')

@section('content')
    <div class="container my-5">

        <div class="row my-2">
            <div class="col">
                <a class="btn btn-info border shadow-sm" href="{{ route('contracts.create') }}" role="button"><i
                        class="fas fa-fw fa-plus"></i>
                    契約を追加</a>
            </div>
        </div>

        <div class="row">
            <div class="col table-responsive">
                <table class="table table-bordered table-hover shadow-sm text-center">
                    <thead class="bg-white">
                        <tr>
                            <th class="w-50">契約内容</th>
                            <th class="w-25">取引先</th>
                            <th class="w-25">契約期間</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php foreach($contracts as $contract): ?>
                        <tr class="clickable-row"
                            onclick="location.href='{{ route('contracts.edit', ['id' => $contract['id']]) }}';">
                            <td>{{ $contract['name'] }}</td>
                            <td><a href="{{ $jira_url . $contract['pid'] }}">{{ $contract['pid'] }}</a></td>
                            <td>{{ date('Y/m/d', strtotime($contract['start_date'])) }}</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
