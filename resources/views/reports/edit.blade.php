@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="row">
            <div class="col">
                <form method="post" action="{{ route('reports.update', ['id' => $report['id']]) }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col">
                            <label>件名</label>
                            <input class="form-control" type="text" name="title" placeholder="Title"
                                value="{{ $report['title'] }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>詳細</label>
                        <textarea class="form-control" rows="5" name="description" placeholder="Description">{{ $report['description'] }}</textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>担当者</label>
                            <input class="form-control" type="text" name="who" value="{{ $report['who'] }}" readonly
                                required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>勤務時間</label>
                            <input class="form-control" type="text" name="work_time" value="{{ $report['work_time'] }}"
                                required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>日付</label>
                            <input class="form-control" type="date" name="date" value="{{ $report['date'] }}"
                                required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>始業時刻</label>
                            <input class="form-control" type="time" name="start_time"
                                value="{{ $report['start_time'] }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>終業時刻</label>
                            <input class="form-control" type="time" name="end_time" value="{{ $report['end_time'] }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>クライアント</label>
                            <input class="form-control" type="text" name="client" value="{{ $report['client'] }}"
                                readonly required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>プロジェクト</label>
                            <input class="form-control" type="text" name="project" value="" readonly required>
                        </div>
                    </div>
                    <input class="form-control" type="hidden" name="create_at" value="{{ date('Y-m-d H:i:s') }}">
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success border shadow-sm"><i class="fas fa-fw fa-check"></i>
                            更新する</button>
                        <button type="button" class="btn btn-danger border shadow-sm"><i
                                class="fas fa-fw fa-trash-alt"></i> 削除</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
