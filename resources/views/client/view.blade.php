@extends('layouts/default')
@section('page')


@csrf

<div class="row mb-3">
	<div class="col-12">
		<a href="../" class="btn btn-primary"><i class="fa fa-chevron-left me-2"></i>Back</a>
	</div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <h1><?= $inquiry->title ?></h1>
        <hr>
        <p class="lead">
            <?= nl2br($inquiry->body) ?>
        </p>
    </div>
</div>

@endsection
