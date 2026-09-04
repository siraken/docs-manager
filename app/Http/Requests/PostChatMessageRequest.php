<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Chat\Entity\ChatMessage;
use Illuminate\Foundation\Http\FormRequest;

final class PostChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 投稿者は受け取らない。ログイン中のユーザーをサーバーが入れる
     * (PostChatMessageUseCase)。参考実装は検証そのものが無く、
     * $request->body をそのまま保存していた。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:' . ChatMessage::MAX_LENGTH],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['body' => '発言'];
    }

    public function body(): string
    {
        return (string) $this->input('body');
    }
}
