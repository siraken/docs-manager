<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // @vite ディレクティブがビルド成果物 (manifest) を探しに行かないようにする。
        // これがないと、テスト前に yarn build しておく必要が出てしまう。
        $this->withoutVite();
    }
}
