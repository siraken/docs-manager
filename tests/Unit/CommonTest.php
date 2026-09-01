<?php

namespace Tests\Unit;

use App\Lib\Common;
use PHPUnit\Framework\TestCase;

/**
 * App\Lib\Common の現状の挙動を固定するリグレッションテスト。
 *
 * 仕様の正しさを主張するものではなく、Laravel / PHP のバージョンを
 * 上げた際に戻り値が変わっていないことを検出するのが目的。
 */
class CommonTest extends TestCase
{
    /** @var Common */
    private $common;

    protected function setUp(): void
    {
        parent::setUp();
        $this->common = new Common();
    }

    public function test_calc_per_は百分率を掛けた値を返す(): void
    {
        $this->assertSame(100.0, $this->common->calcPer(1000, 10));
        $this->assertSame(80.0, $this->common->calcPer(1000, 8));
        $this->assertSame(0.0, $this->common->calcPer(1000, 0));
        $this->assertSame(0.0, $this->common->calcPer(0, 10));
    }

    public function test_calc_per_は小数を丸めない(): void
    {
        // 端数処理を入れていないため、浮動小数がそのまま返る
        $this->assertEqualsWithDelta(80.08, $this->common->calcPer(1001, 8), 0.0001);
    }

    public function test_get_months_は1から12のゼロ埋め文字列を返す(): void
    {
        $months = $this->common->getMonths();

        $this->assertCount(12, $months);
        $this->assertSame('01', $months[1]);
        $this->assertSame('09', $months[9]);
        $this->assertSame('10', $months[10]);
        $this->assertSame('12', $months[12]);
        // キーは 1 始まりの整数
        $this->assertSame(range(1, 12), array_keys($months));
    }

    public function test_get_taxes_は5種類の税区分を返す(): void
    {
        $taxes = $this->common->getTaxes();

        $this->assertCount(5, $taxes);

        $expected = [
            ['id' => 1, 'name' => '10%',    'per' => 0.1],
            ['id' => 2, 'name' => '軽減8%', 'per' => 0.08],
            ['id' => 3, 'name' => '8%',     'per' => 0.08],
            ['id' => 4, 'name' => '5%',     'per' => 0.05],
            ['id' => 5, 'name' => '対象外', 'per' => 0],
        ];

        $this->assertSame($expected, $taxes);
    }
}
