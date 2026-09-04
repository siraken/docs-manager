<?php

declare(strict_types=1);

namespace App\Domain\Chat\Entity;

use App\Domain\Shared\Exception\InvalidValueException;

/**
 * チャットの発言 1 件。
 *
 * 参考にした novalumo/e-learning は uid_from / uid_to を持ちながら、
 * 送信先を 999 に固定し、取得も全件から新しい 15 件を返すだけだった。
 * 画面の宛先一覧も「ユーザー１」が 3 つ並ぶ静的な HTML で、宛先を選ぶ
 * 手段が無かった。**実態は宛先の無い単一のルーム**だったので、
 * 使われていなかった uid_to は持たせていない。
 */
final class ChatMessage
{
    /** 1 発言の上限。長文は別の手段に誘導する */
    public const MAX_LENGTH = 2000;

    private function __construct(
        private ?int $id,
        private int $userId,
        private string $body,
        private ?\DateTimeImmutable $postedAt,
    ) {
    }

    /**
     * @param int $userId 投稿者。**クライアントから受け取らず、
     *                    ログイン中のユーザーをサーバーが入れる**
     * @throws InvalidValueException
     */
    public static function post(int $userId, string $body): self
    {
        $trimmed = trim($body);

        if ($trimmed === '') {
            throw new InvalidValueException('発言を入力してください。');
        }

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw new InvalidValueException(
                sprintf('発言は %s 文字以内にしてください。', number_format(self::MAX_LENGTH)),
            );
        }

        return new self(null, $userId, $trimmed, null);
    }

    public static function reconstitute(
        int $id,
        int $userId,
        string $body,
        ?\DateTimeImmutable $postedAt,
    ): self {
        return new self($id, $userId, $body, $postedAt);
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function postedAt(): ?\DateTimeImmutable
    {
        return $this->postedAt;
    }

    /**
     * 消せるのは自分の発言だけ。
     *
     * 参考実装には削除そのものが無かった。人の発言を消せると議論の記録が
     * 一方的に失われるため、投稿者に限っている。
     */
    public function isDeletableBy(int $userId): bool
    {
        return $this->userId === $userId;
    }
}
