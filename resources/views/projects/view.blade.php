@extends('layouts/default')
@section('page')

<x-page-header :title="$task->title">
    <x-slot:actions>
        <x-button :href="route('projects.index')" icon="arrow-left">戻る</x-button>
    </x-slot:actions>
</x-page-header>

<x-card class="text-sm leading-relaxed text-slate-700">
    {!! $task->description !!}
</x-card>

@endsection
