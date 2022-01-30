<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js"></script>
</head>
<body>
    {{-- navigation --}}
    <nav class="navbar navbar-light bg-light p-3 border-bottom">
        <div class="d-flex col-12 col-md-3 col-lg-2 mb-2 mb-lg-0 flex-wrap flex-md-nowrap justify-content-between">
            <a class="navbar-brand" href="#">
                Novalumo Manager
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
                    Hello, Kento Shirasawa
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
                          <a class="nav-link {{ request()->route()->named('home*') ? 'active' : '' }}" aria-current="page" href="/">
                            <span class="ml-2">Dashboard</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('trip*') ? 'active' : '' }}" href="/trip">
                            <span class="ml-2">Business Trip</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('expense*') ? 'active' : '' }}" href="/expense">
                            <span class="ml-2">Travel Expense</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('pricing*') ? 'active' : '' }}" href="/pricing">
                            <span class="ml-2">Pricing</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('estimate*') ? 'active' : '' }}" href="/estimate">
                            <span class="ml-2">Estimate</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('invoice*') ? 'active' : '' }}" href="/invoice">
                            <span class="ml-2">Invoice</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('order*') ? 'active' : '' }}" href="/order">
                            <span class="ml-2">Order</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('item*') ? 'active' : '' }}" href="/item">
                            <span class="ml-2">Item</span>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link {{ request()->route()->named('inquiry*') ? 'active' : '' }}" href="/inquiry">
                            <span class="ml-2">Inquiry</span>
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

    {{-- flash --}}
    @if (session('flash_message'))
        <div class="row mb-3">
            <div class="bg-success text-white rounded p-3">
                <i class="fa fa-check-circle me-2"></i>{{ session('flash_message') }}
            </div>
        </div>
    @endif
</body>
</html>
