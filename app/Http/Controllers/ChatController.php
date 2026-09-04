<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Chat\UseCase\DeleteChatMessageUseCase;
use App\Application\Chat\UseCase\ListChatMessagesUseCase;
use App\Application\Chat\UseCase\PostChatMessageUseCase;
use App\Application\User\UseCase\ListUsersUseCase;
use App\Domain\User\Entity\User;
use App\Http\Requests\PostChatMessageRequest;
use App\Http\ViewModels\ChatMessageView;
use App\Support\Lookup;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * チャット。全員が読み書きする 1 つのルーム。
 *
 * novalumo/e-learning を参考にしているが、あちらは
 *  - 送信先 (uid_to) が 999 固定で、宛先を選ぶ手段が無かった
 *  - 取得は全件から新しい 15 件を返すだけで、宛先を見ていなかった
 *  - 発言の検証が無く、$request->body をそのまま保存していた
 *  - 誰がいつ発言したのかを画面に出す処理がコメントアウトされていた
 *  - 削除の手段が無かった
 * という状態だった。詳しくは docs/history/e-learning-port.md を参照。
 *
 * 更新は専用の JSON エンドポイントではなく Inertia の部分リロードで行う
 * (画面側が router.reload({ only: ['messages'] }) を叩く)。参考実装は
 * axios で /chat/get を 10 秒ごとに叩き、返ってきた全件で DOM を作り直していた。
 */
final class ChatController extends Controller
{
    public function index(ListChatMessagesUseCase $listMessages, ListUsersUseCase $listUsers): InertiaResponse
    {
        return Inertia::render('Chat/Index', [
            'messages' => ChatMessageView::collection(
                $listMessages->execute(),
                $this->userNames($listUsers->execute()),
                session('user_id') === null ? null : (int) session('user_id'),
            ),
            'maxLength' => \App\Domain\Chat\Entity\ChatMessage::MAX_LENGTH,
            'urls' => [
                'self' => route('chat.index'),
                'post' => route('chat.index'),
            ],
        ]);
    }

    public function store(PostChatMessageRequest $request, PostChatMessageUseCase $postMessage): RedirectResponse
    {
        $postMessage->execute($request->body());

        // フラッシュは出さない。投稿のたびにトーストが出ると会話の邪魔になる
        return redirect()->route('chat.index');
    }

    public function destroy(int $id, DeleteChatMessageUseCase $deleteMessage): RedirectResponse
    {
        $deleteMessage->execute($id);

        return redirect()->route('chat.index');
    }

    /**
     * ユーザー ID => 名前。発言ごとに引くと N+1 になるためまとめて引く。
     *
     * @param list<User> $users
     * @return array<int, string>
     */
    private function userNames(array $users): array
    {
        return Lookup::byId($users, static fn (User $u): string => $u->name());
    }
}
