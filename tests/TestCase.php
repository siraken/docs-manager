<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // @vite ディレクティブがビルド成果物 (manifest) を探しに行かないようにする。
        // これがないと、テスト前に pnpm build しておく必要が出てしまう。
        $this->withoutVite();
    }
}
