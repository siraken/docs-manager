<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    {{-- flash --}}
    @if (session('flash_message'))
        <div class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('flash_message') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <script>Toast.show()</script>
    @endif
    {{-- navigation --}}
    <nav class="navbar navbar-light bg-light p-3 border-bottom">
        <div class="d-flex col-12 col-md-3 col-lg-2 mb-2 mb-lg-0 flex-wrap flex-md-nowrap justify-content-between">
            <a class="navbar-brand" href="#">
                Novalumo 管理ツール
            </a>
            <button class="navbar-toggler d-md-none collapsed mb-3" type="button" data-toggle="collapse" data-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <input class="form-control form-control-dark" type="text" placeholder="Search" aria-label="Search">
        </div>
        <div class="col-12 col-md-5 col-lg-8 d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
            <div class="dropdown">
                <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false">
                    Novalumo合同会社
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><a class="dropdown-item" href="#">Messages</a></li>
                    <li><a class="dropdown-item" href="#">Sign out</a></li>
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
                          <a class="nav-link {{ request()->route()->named('home*') ? 'active fw-bold' : '' }}" aria-current="page" href="/">
                            <span class="ml-2">ダッシュボード</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('trips*') ? 'active fw-bold' : '' }}" href="{{ route('trips.index') }}">
                            <span class="ml-2">出張申請</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('expenses*') ? 'active fw-bold' : '' }}" href="{{ route('expenses.index') }}">
                            <span class="ml-2">出張旅費精算</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('pricing*') ? 'active fw-bold' : '' }}" href="/pricing">
                            <span class="ml-2">料金試算</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('estimates*') ? 'active fw-bold' : '' }}" href="{{ route('estimates.index') }}">
                            <span class="ml-2">見積書</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('invoices*') ? 'active fw-bold' : '' }}" href="{{ route('invoices.index') }}">
                            <span class="ml-2">請求書</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('orders*') ? 'active fw-bold' : '' }}" href="{{ route('orders.index') }}">
                            <span class="ml-2">発注書</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('items*') ? 'active fw-bold' : '' }}" href="{{ route('items.index') }}">
                            <span class="ml-2">商品</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('inquiries*') ? 'active fw-bold' : '' }}" href="{{ route('inquiries.index') }}">
                            <span class="ml-2">問い合わせ</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('clients*') ? 'active fw-bold' : '' }}" href="{{ route('clients.index') }}">
                            <span class="ml-2">取引先</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('tasks*') ? 'active fw-bold' : '' }}" href="{{ route('tasks.index') }}">
                            <span class="ml-2">タスク</span>
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
</body>
</html>
