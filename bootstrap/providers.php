<?php

use App\Providers\AppServiceProvider;
use App\Providers\DomainServiceProvider;

return [
    AppServiceProvider::class,
    // インターフェースと実装の対応は DomainServiceProvider に集約している
    DomainServiceProvider::class,
];
