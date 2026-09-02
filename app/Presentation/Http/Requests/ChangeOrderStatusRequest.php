<?php

declare(strict_types=1);

namespace App\Presentation\Http\Requests;

use App\Domain\Order\ValueObject\StatusKind;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 一覧のステータスピルからの JSON リクエスト。
 *
 * 移行前は file_get_contents("php://input") を直接読んでいたため、Laravel の
 * 検証もテストからの操作も効かなかった。フロント (status.ts) は現在値
 * (currentStatus) も送ってくるが、遷移は保存済みの値から決めるので使わない。
 */
final class ChangeOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', 'in:issued,ordered'],
        ];
    }

    public function orderId(): int
    {
        return (int) $this->input('id');
    }

    public function statusKind(): StatusKind
    {
        return StatusKind::fromString((string) $this->input('type'));
    }
}
