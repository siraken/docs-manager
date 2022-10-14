@extends('layouts/default')
@section('page')

<div class="row mb-3">
    <div class="col-12">
        {{-- File uploader --}}
        <form action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group mb-1">
                <label for="mame">お名前</label>
                <input type="text" id="name" class="form-control" name="name">
            </div>
            <div class="form-group mb-1">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" class="form-control" name="email">
            </div>
            <div class="form-group mb-2">
                <label for="file">ファイルを選択してください</label>
                <input type="file" id="file" class="form-control" name="file">
            </div>
            <button type="submit" class="btn btn-secondary">アップロード</button>
        </form>
    </div>
</div>

{{-- Session only --}}
@if (session('email'))
<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ファイル名</th>
                        {{-- <th>メールアドレス</th> --}}
                        {{-- <th>アップロード日</th> --}}
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < count($files); $i++) <tr>
                        <td>{{ $files[$i] }}</td>
                        {{-- <td>{{ $files[$i] }}</td> --}}
                        {{-- <td>{{ $files[$i] }}</td> --}}
                        <td>
                            <a href="{{ route('files.download', ['file' => $files[$i]]) }}" class="btn btn-secondary">
                                ダウンロード
                            </a>
                            <form action="{{ route('files.delete', ['file' => $files[$i]]) }}" method="POST"
                                style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">削除</button>
                            </form>
                        </td>
                        </tr>
                        @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection
