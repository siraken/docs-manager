<!DOCTYPE html>
<html lang="ja" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex" />
    <meta name="googlebot" content="nofollow" />
    <title>管理ツール</title>

    {{-- favicon.ico は長らく 0 バイトの空ファイルで、参照もされていなかった。
         novalumo/e-learning から実体を持ってきて繋いである。 --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    @inertiaHead
</head>

{{-- Inertia のルートテンプレート。ページの中身は Svelte が描くので、ここには
     <html> の骨格しか無い。ナビやトーストは Layouts/Default.svelte にある。

     Blade のままの画面は従来どおり layouts/default.blade.php を使う。
     移行が済むまでこの 2 枚が並存し、見た目を揃えておく必要がある。 --}}
<body class="flex min-h-full flex-col">
    @inertia
</body>

</html>
