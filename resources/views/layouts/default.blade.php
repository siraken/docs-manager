<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex" />
    <meta name="googlebot" content="nofollow" />
    <title>管理ツール</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>

<body>
    {{-- flash --}}
    @if (session('flash_message'))
    <div class="toast-container m-3 fixed-top top-0 start-50 translate-middle-x">
        <div class="toast align-items-center text-white bg-{{ session('flash_status') }} border-0 fade show"
            role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i
                        class="bi bi-{{ session('flash_icon') ? session('flash_icon') : 'exclamation-circle-fill' }} me-2"></i>{{
                    session('flash_message') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>
    {{-- <script>
        Toast.show()
    </script> --}}
    @endif
    {{-- navigation --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom px-1 py-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard.index') }}">Console</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ session('name') }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <p class="dropdown-item disabled text-center m-0">
                                    <img src="https://www.gravatar.com/avatar/{{ md5(session('email')) }}"
                                        class="img-fluid rounded-circle" alt="profile">
                                </p>
                            </li>
                            <li><small class="dropdown-item disabled text-center">
                                    {{ session('user_id') }} : {{ session('name') }}<br>
                                    {{ session('email') }}
                                </small></li>
                            <li class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('settings.index') }}">Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="javascript:toBeLoggedOut.submit()">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    {{-- container --}}
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse border-end">
                <div class="position-sticky pt-md-5">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->named('trips.*') ? 'active fw-bold' : '' }}"
                                href="{{ route('trips.index') }}">
                                <span class="ml-2">出張申請</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->named('expenses.*') ? 'active fw-bold' : '' }}"
                                href="{{ route('expenses.index') }}">
                                <span class="ml-2">出張旅費精算</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->named('pricing.*') ? 'active fw-bold' : '' }}"
                                href="{{ route('pricing.index') }}">
                                <span class="ml-2">料金試算</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->named('orders.*') ? 'active fw-bold' : '' }}"
                                href="{{ route('orders.index') }}">
                                <span class="ml-2">発注書</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->named('works.*') ? 'active fw-bold' : '' }}"
                                href="{{ route('works.index') }}">
                                <span class="ml-2">案件管理</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->named('users.*') ? 'active fw-bold' : '' }}"
                                href="{{ route('users.index') }}">
                                <span class="ml-2">ユーザー管理</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->route()->named('files.*') ? 'active fw-bold' : '' }}"
                                href="{{ route('files.index') }}">
                                <span class="ml-2">ファイル管理</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4" style="overflow-y: scroll; height: 100vh;">
                {{-- page --}}
                @yield('page')
            </main>
        </div>
    </div>
    <form name="toBeLoggedOut" method="POST" action="{{ route('logout') }}">
        @csrf
    </form>
</body>

</html>
