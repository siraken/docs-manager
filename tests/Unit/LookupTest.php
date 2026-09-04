<?php

use App\Domain\Customer\Entity\Customer;
use App\Support\Lookup;

/**
 * 一覧の N+1 を避けるための対応表づくり。
 *
 * 各コントローラが同じ private メソッドを持っていたのをここへ集約した。
 */
function customerEntity(int $id, string $name): Customer
{
    return Customer::reconstitute($id, $name, null, true, null, null, null, null, null, null, null, null);
}

test('id をキーにした表示名の対応表を作れる', function () {
    $map = Lookup::byId(
        [customerEntity(1, '株式会社A'), customerEntity(2, '株式会社B')],
        fn (Customer $c): string => $c->name(),
    );

    expect($map)->toBe([1 => '株式会社A', 2 => '株式会社B']);
});

test('空の配列からは空の対応表ができる', function () {
    expect(Lookup::byId([], fn (Customer $c): string => $c->name()))->toBe([]);
});

test('id をキーにエンティティそのものを引ける', function () {
    $a = customerEntity(1, '株式会社A');
    $map = Lookup::keyById([$a, customerEntity(2, '株式会社B')]);

    expect($map[1])->toBe($a)
        ->and($map[2]->name())->toBe('株式会社B');
});

test('同じ id が重なったら後勝ちになる', function () {
    // 実運用では id は一意なので起きないが、挙動を明示しておく
    $map = Lookup::byId([customerEntity(1, '先'), customerEntity(1, '後')], fn (Customer $c): string => $c->name());

    expect($map)->toBe([1 => '後']);
});
