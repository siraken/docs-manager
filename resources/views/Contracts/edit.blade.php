@extends('layouts.app')

@section('content')
    <div class="container my-5">

        <div class="row">
            <div class="col">
                <form method="post" action="{{ route('contracts.update') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col">
                            <label>プロジェクト名</label>
                            <input class="form-control" type="text" name="name" value="<?php echo $name; ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>PID</label>
                            <input class="form-control" type="text" name="pid" value="<?php echo $pid; ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>受注日</label>
                            <input class="form-control" type="date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col">
                            <label>備考</label>
                            <textarea name="description" id="" class="form-control" cols="30" rows="3"><?php echo $description; ?></textarea>
                        </div>
                    </div>
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
