@extends('layouts.app')

@section('content')
    <?php $totalWorkTimeArray = []; ?>
    <div class="container my-5">

        <div class="row my-2">
            <div class="col d-flex">
                <a class="btn btn-info border shadow-sm mr-1" href="{{ route('reports.create') }}" role="button"><i
                        class="fas fa-plus fa-fw"></i></a>
                <a class="btn btn-info border shadow-sm" href="#" role="button" data-toggle="modal"
                    data-target="#calc_modal" data-placement="top" title="Tooltip on top"><i
                        class="fas fa-calculator fa-fw"></i></a>
                <form class="form-inline ml-auto d-none d-md-block">
                    <div class="input-group">
                        <select class="custom-select w-auto" name="year">
                            <option selected disabled>年</option>
                            <?php for ($year = 2020; $year < 2025; $year++) {?>
                            <option value="{{ $year }}">{{ $year }}</option>
                            <?php } ?>
                        </select>
                        <select class="custom-select w-auto" name="month">
                            <option selected disabled>月</option>
                            <?php for ($month = 1; $month < 13; $month++) {?>
                            <option value="{{ $month }}">{{ $month }}</option>
                            <?php } ?>
                        </select>
                        <input class="form-control w-50" type="search" name="q" placeholder="Search"
                            aria-label="Search" value="{{ $q }}">
                        <div class="input-group-append">
                            <button class="btn btn-info my-2 my-sm-0" type="submit"><i
                                    class="fas fa-fw fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col table-responsive">
                <table class="table table-bordered table-hover shadow-sm text-center">
                    <thead class="bg-white">
                        <tr>
                            <th class="w-auto">日付</th>
                            <th class="w-25">内容</th>
                            <th class="w-auto">始業時刻</th>
                            <th class="w-auto">終業時刻</th>
                            <th class="w-auto">勤務時間</th>
                            <th class="w-auto">担当者</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">

                        <?php foreach ($reports as $report): ?>
                        <tr class="clickable-row"
                            onclick="location.href = '{{ route('reports.edit', ['id' => $report['id']]) }}'">
                            <td class="align-middle">
                                <?= date('Y/m/d', strtotime($report['date'])) ?>
                            </td>
                            <td class="align-middle">
                                <?= $report['title'] ?>
                            </td>
                            <td class="align-middle">
                                <?php empty($report['start_time']) ? print '-' : print date('H:i', strtotime($report['start_time'])); ?>
                            </td>
                            <td class="align-middle">
                                <?php empty($report['end_time']) ? print '-' : print date('H:i', strtotime($report['end_time'])); ?>
                            </td>
                            <td class="align-middle">
                                <?= $report['work_time'] ?> h
                            </td>
                            <td class="align-middle">
                                <?= $report['who'] ?>
                            </td>
                        </tr>
                        <?php $totalWorkTimeArray[] = $report['work_time']; ?>
                        <?php endforeach ?>
                    </tbody>
                    <?php $totalWorkTime = array_sum($totalWorkTimeArray); ?>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                {{ $reports->onEachSide(5)->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="calc_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calculator fa-fw"></i> 計算結果</h5>
                </div>
                <div class="modal-body" id="app">
                    <p>総勤務時間：<span class="h3"><?= $totalWorkTime ?> h</span></p>
                    <p>総勤務日数：<span class="h3"><?= $totalWorkDays ?> 日</span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info border" data-dismiss="modal"><i
                            class="fas fa-times-circle"></i> 閉じる</button>
                </div>
            </div>
        </div>
    </div>
@endsection
