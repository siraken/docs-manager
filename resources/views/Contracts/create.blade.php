@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="row">
            <div class="col">
                <form method="post" action="{{ route('contracts.store') }}" autocomplete="off">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col">
                            <label>プロジェクト名</label>
                            <input class="form-control" type="text" name="name" value="" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>PID <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top"
                                    title="JiraのPIDと合致するようにしてください"></i></label>
                            <input class="form-control" type="text" name="pid" value="" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>受注日</label>
                            <input class="form-control" type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col">
                            <label>備考</label>
                            <textarea name="description" id="" class="form-control" cols="30" rows="3"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success border shadow-sm"><i class="fas fa-fw fa-check"></i>
                        登録</button>
                </form>
            </div>
        </div>
    </div>
@endsection
