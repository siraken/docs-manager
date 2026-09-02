<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

/**
 * 指定された識別子のエンティティが存在しないことを表す。
 *
 * リポジトリの find 系は「見つからなければ null」を返し、
 * 「存在しないと処理を続けられない」ユースケース側でこれを投げる。
 */
final class EntityNotFoundException extends DomainException
{
    public static function of(string $entity, int|string $id): self
    {
        return new self(sprintf('%s (id: %s) が見つかりません。', $entity, (string) $id));
    }
}
