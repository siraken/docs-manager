<!DOCTYPE html>
<html lang="ja" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex" />
    <title>管理ツール</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/ts/app.tsx'])
</head>

{{-- 旧レイアウトには <script>Toast.show()</script> があったが、Toast は
     どこにも定義されておらず ReferenceError になっていた。表示と閉じる操作は
     x-flash (Alpine) が持つので、この inline script は不要になった。 --}}
<body class="flex min-h-full items-center justify-center bg-slate-100 px-4 py-10">
    <x-flash top="top-4" />
    @yield('page')
</body>

</html>
