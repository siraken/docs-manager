@extends('layouts/default')
@section('page')

<div class="row mb-3">
    <div class="col-12">
        <h4>出張旅費 > 精算</h4>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        @csrf

        <div class="mb-1">
            <label for="rel_id">管理ID</label>
            <input type="text" id="rel_id" class="form-control" name="rel_id" value="{{ date('Ymd').'-'.'Num' }}">
        </div>

        <div class="mb-1">
            <label for="dir">出張先</label>
            <input type="text" id="dir" class="form-control" name="dir" value="">
        </div>

        <div class="mb-1">
            <label for="purpose">目的</label>
            <textarea name="purpose" id="purpose" class="form-control" cols="30" rows="10"></textarea>
        </div>

        {{-- TODO:交通費計 --}}
        {{-- TODO:ガソリン代 --}}
        {{-- TODO:日当計 --}}
        {{-- TODO:宿泊費計 --}}
        {{-- TODO:昼食計 --}}
        {{-- TODO:夕食計 --}}
        {{-- TODO:合計 --}}

        <div class="mb-1">
            <label for="apply_date">申請日</label>
            <input type="date" id="apply_date" class="form-control" name="apply_date"
                value="{{ date('Y-m-d', strtotime('now')) }}">
        </div>

        <div class="mb-1">
            <label for="date_from">出発日</label>
            <input type="date" id="date_from" class="form-control" name="date_from"
                value="{{ date('Y-m-d', strtotime('+1 day')) }}">
        </div>

        <div class="mb-1">
            <label for="date_to">帰着日</label>
            <input type="date" id="date_to" class="form-control" name="date_to"
                value="{{ date('Y-m-d', strtotime('+1 week')) }}">
        </div>

        {{-- TODO:精算日 --}}

        <div class="mb-1">
            <label for="apply_person">申請者氏名</label>
            <input type="text" id="apply_person" class="form-control" name="apply_person" value="">
        </div>

    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <button class="btn btn-secondary" type="submit">保存する</button>
    </div>
</div>

@endsection
