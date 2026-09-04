<?php

declare(strict_types=1);

namespace App\Domain\Project\ValueObject;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * Jira のキー。案件に紐づく Jira のプロジェクト (NOVA) か課題 (NOVA-123) を指す。
 *
 * in-house-timecard-app の projects.pid に相当する。移植元は一覧の PID から
 * Jira の /browse/ へリンクしており、実際に使われていた導線だった。
 *
 * docs-manager 側にあった related_task_id は整数で、指す先の tasks テーブルは
 * マイグレーションごと存在せず、画面にも一切出ていなかった。この値オブジェクトが
 * その置き換えになる。
 *
 * ブラウズ先の URL はここでは組まない。ホスト名は環境設定 (config/services.php)
 * の値で、ドメイン層が知ってよいものではないため。
 */
final readonly class JiraKey implements \Stringable, \JsonSerializable
{
    /**
     * Jira のキーの書式。
     *
     * 先頭は英字で、以降は英数字とアンダースコア。課題番号 (-123) は任意。
     * Jira 自身の既定と同じく大文字だけを正とし、小文字入力は大文字に寄せる。
     */
    private const PATTERN = '/^[A-Z][A-Z0-9_]{0,29}(-\d{1,10})?$/';

    private function __construct(public string $value)
    {
    }

    /** @throws InvalidValueException */
    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (preg_match(self::PATTERN, $normalized) !== 1) {
            throw new InvalidValueException(
                sprintf('Jira のキーの形式が不正です: %s (例: NOVA / NOVA-123)', $value),
            );
        }

        return new self($normalized);
    }

    /** 未入力はそのまま null で返す (案件に Jira を紐付けないこともある) */
    public static function parseNullable(mixed $value): ?self
    {
        if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidValueException('Jira のキーとして解釈できません。');
        }

        return self::fromString($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
