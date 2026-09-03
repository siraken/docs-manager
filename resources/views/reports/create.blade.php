@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="row">
            <div class="col">
                <form method="post" action="{{ route('reports.store') }}" autocomplete="off">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>日付</label>
                            <input class="form-control" type="date" name="date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group col-md-8">
                            <label>件名</label>
                            <input class="form-control" type="text" name="title" placeholder="Title" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>始業時刻</label>
                            <input class="form-control" type="time" name="start_time" value="">
                        </div>
                        <div class="form-group col-md-4">
                            <label>終業時刻</label>
                            <input class="form-control" type="time" name="end_time" value="<?= date('H:i') ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label>勤務時間</label>
                            <input class="form-control" type="text" name="work_time" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>詳細</label>
                        <textarea class="form-control" rows="5" name="description" placeholder="Description"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>担当者</label>
                            <select name="user_id" id="" class="form-control" required>
                                <option value="" selected disabled>選択してください</option>
                                <?php foreach ($users as $user) : ?>
                                <option value="<?= $user['name'] ?>"><?= $user['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>クライアント</label>
                            <select name="client" id="" class="form-control">
                                <option value="" selected disabled>選択してください</option>
                                <?php foreach ($clients as $client) : ?>
                                <option value="<?= $client['name'] ?>"><?= $client['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>プロジェクト</label>
                            <select name="" id="" class="form-control">
                                <option value="" selected disabled>選択してください</option>
                                <?php foreach ($projects as $project) : ?>
                                <option value="<?= $project['name'] ?>"><?= $project['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <input class="form-control" type="hidden" name="create_at" value="<?= date('Y-m-d H:i:s') ?>">
                    <button type="submit" class="btn btn-success border shadow-sm"><i class="fas fa-fw fa-check"></i>
                        登録</button>
                </form>
            </div>
        </div>
    </div>
@endsection
