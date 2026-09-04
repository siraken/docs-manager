<?php

declare(strict_types=1);

namespace App\Application\Accounting\Exception;

use App\Domain\Shared\Exception\DomainException;

/**
 * 仕訳から参照されている勘定科目を削除しようとした。
 *
 * 消してしまうと過去の仕訳が宙に浮くので、使わなくなった科目は
 * 「無効にする」で選択肢から外す。
 */
final class AccountInUseException extends DomainException
{
    public static function of(string $name): self
    {
        return new self(sprintf(
            '「%s」は仕訳で使われているため削除できません。使わなくなった科目は「無効にする」で選択肢から外せます。',
            $name,
        ));
    }
}
