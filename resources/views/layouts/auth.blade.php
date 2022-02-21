<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>管理ツール</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    {{-- flash --}}
    @if (session('flash_message'))
        <div class="toast-container m-3 fixed-top top-0 start-50 translate-middle-x">
            <div class="toast align-items-center text-white bg-{{ session('flash_status') ? session('flash_status') : 'primary' }} border-0 fade show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-{{ session('flash_icon') ? session('flash_icon') : 'exclamation-circle-fill' }} me-2"></i>{{ session('flash_message') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <script>Toast.show()</script>
    @endif
    {{-- container --}}
    @yield('page')
</body>
</html>
