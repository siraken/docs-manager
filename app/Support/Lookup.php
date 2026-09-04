<?php

declare(strict_types=1);

namespace App\Support;

/**
 * エンティティの配列から「id => 表示名」の対応表を作る。
 *
 * 一覧で行ごとに関連を引くと N+1 になるため、コントローラはマスタを
 * まとめて引いて対応表にし、ViewModel へ渡している。この形が
 * 発注書・案件・契約・勤務報告・仕訳帳・研修とほぼすべての一覧で要る。
 *
 * 各コントローラが private メソッドとして同じものを持っていたのを
 * ここへ集約した (userNames / customerNames / courseTitles /
 * accountNames / projectNames が中身まで同一だった)。
 */
final class Lookup
{
    /**
     * id をキー、コールバックの戻り値を値にした配列を作る。
     *
     * @template T of object
     * @param list<T> $entities `id(): ?int` を持つエンティティの配列
     * @param callable(T): string $label 表示名の取り出し方
     * @return array<int, string>
     */
    public static function byId(array $entities, callable $label): array
    {
        $map = [];

        foreach ($entities as $entity) {
            $map[(int) $entity->id()] = $label($entity);
        }

        return $map;
    }

    /**
     * id をキー、エンティティそのものを値にした配列を作る。
     *
     * 表示名だけでなくエンティティ自体が要る場合に使う
     * (提出物が課題の提出期限を見るなど)。
     *
     * @template T of object
     * @param list<T> $entities
     * @return array<int, T>
     */
    public static function keyById(array $entities): array
    {
        $map = [];

        foreach ($entities as $entity) {
            $map[(int) $entity->id()] = $entity;
        }

        return $map;
    }
}
