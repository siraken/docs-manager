@extends('layouts/default')
@section('page')


@csrf

<div class="row mb-3">
	<div class="col-12">
		<a href="/inquiry/create" class="btn btn-primary"><i class="fa fa-plus me-2"></i>New Inquiry</a>
		<a href="/inquiry/truncate" class="btn btn-primary"><i class="fa fa-eraser me-2"></i>Truncate</a>
		{{-- <a href="/estimate/trash" class="btn btn-primary">ごみ箱</a> --}}
	</div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                </tr>
            </thead>
            <tbody>
                @if ($inquiries)
                @foreach ($inquiries as $row)
                <tr>
                    <td><?= $row->id ?></td>
                    <td><a href="/inquiry/view/<?= $row->id ?>"><?= $row->title ?></a></td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="2">No data</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@endsection
