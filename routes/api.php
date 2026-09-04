<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| このアプリに API はまだ無い。画面はすべて Inertia で、サーバーとの
| やりとりは web ルート越しに行う (docs/frontend.md の「サーバーへの送信」)。
|
| ファイル自体を消していないのは bootstrap/app.php の withRouting() が
| このパスを指しているため。API を足すときはここに書く。
|
| throttle は bootstrap/app.php の throttleApi() で掛かる。リミッター
| (60/min) は AppServiceProvider が定義している。
|
*/
