<?php

declare(strict_types=1);

namespace App\Support;

/**
 * enum の `options()` を画面のセレクト用に整える。
 *
 * ドメインの enum は `[値 => 表示名]` の連想配列を返すが、画面は
 * `[{value, label}]` の並びで受け取りたい (Svelte の `{#each}` で回すため、
 * キーと値がペアになっている必要がある)。その変換だけを行う。
 *
 * 案件の状態・勘定科目の区分・受講の状態・提出物の状態と、各コントローラが
 * 同じ変換を private メソッドで持っていたのをここへ集約した。
 */
final class SelectOptions
{
    /**
     * @param array<int|string, string> $options ドメインの enum が返す [値 => 表示名]
     * @return list<array{value: int|string, label: string}>
     */
    public static function fromMap(array $options): array
    {
        return array_map(
            static fn (int|string $value, string $label): array => ['value' => $value, 'label' => $label],
            array_keys($options),
            array_values($options),
        );
    }
}
