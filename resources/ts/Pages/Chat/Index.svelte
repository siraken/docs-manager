<script lang="ts">
  import { router, useForm } from "@inertiajs/svelte";
  import { untrack } from "svelte";

  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import FormErrors from "../../components/ui/FormErrors.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Textarea from "../../components/ui/Textarea.svelte";
  import type { ChatMessage } from "../../lib/learning-types";

  /**
   * チャット。全員が読み書きする 1 つのルーム。
   *
   * 更新は Inertia の部分リロードで取る。参考にした e-learning は axios で
   * /chat/get を 10 秒ごとに叩き、返ってきた全件で DOM を作り直していた
   * (差分は見ず、innerHTML を空にして組み直していた)。
   */
  type Props = {
    messages: ChatMessage[];
    maxLength: number;
    urls: { self: string; post: string };
  };

  let { messages, maxLength, urls }: Props = $props();

  /** 取り直す間隔 (ms)。参考実装と同じ 10 秒 */
  const POLL_INTERVAL = 10_000;

  const form = untrack(() => useForm({ body: "" }));

  let listEl = $state<HTMLElement | null>(null);
  /** 直近に描いた発言の id。増えたときだけ一番下へ送る */
  let lastSeenId = $state(0);

  function send(): void {
    if (form.body.trim() === "") {
      return;
    }

    form.post(urls.post, {
      preserveScroll: true,
      onSuccess: () => {
        // useForm の reset() は「送信時点の値」に戻ることがあるので代入する
        form.body = "";
      },
    });
  }

  /** Shift+Enter で送信（参考実装と同じ操作感） */
  function onKeydown(event: KeyboardEvent): void {
    if (event.shiftKey && event.key === "Enter") {
      event.preventDefault();
      send();
    }
  }

  // 一定間隔で messages だけ取り直す。他の props はサーバーに作らせない
  $effect(() => {
    const timer = setInterval(() => {
      // reload は元々スクロール位置と状態を保つので、明示の指定は要らない
      router.reload({ only: ["messages"] });
    }, POLL_INTERVAL);

    return () => clearInterval(timer);
  });

  // 発言が増えたときだけ一番下へ送る。読み返している最中に
  // 勝手にスクロールされないようにする
  $effect(() => {
    const latest = messages.at(-1)?.id ?? 0;

    if (latest > untrack(() => lastSeenId)) {
      lastSeenId = latest;
      queueMicrotask(() => listEl?.scrollTo({ top: listEl.scrollHeight }));
    }
  });

  function remove(message: ChatMessage): void {
    if (message.urls) {
      router.delete(message.urls.delete, { preserveScroll: true });
    }
  }
</script>

<PageHeader title="チャット">
  {#snippet description()}全員が読み書きする 1 つのルームです{/snippet}
</PageHeader>

<div class="mx-auto flex max-w-3xl flex-col gap-4">
  <div
    bind:this={listEl}
    class="max-h-[60vh] min-h-64 overflow-y-auto rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200"
  >
    {#if messages.length === 0}
      <EmptyState class="border-0 bg-transparent">まだ発言がありません</EmptyState>
    {:else}
      <ul class="space-y-3">
        {#each messages as message (message.id)}
          <li class="flex flex-col {message.isMine ? 'items-end' : 'items-start'}">
            <div class="flex max-w-[85%] flex-col gap-1">
              <p class="text-xs text-slate-500 {message.isMine ? 'text-right' : ''}">
                {message.userName}<span class="ml-2 tabular">{message.postedAtLabel}</span>
              </p>

              <div class="group flex items-end gap-1.5 {message.isMine ? 'flex-row-reverse' : ''}">
                <p
                  class="rounded-2xl px-3.5 py-2 text-sm whitespace-pre-wrap {message.isMine
                    ? 'bg-brand-600 text-white'
                    : 'bg-slate-100 text-slate-900'}"
                >
                  {message.body}
                </p>

                {#if message.urls}
                  <button
                    type="button"
                    aria-label="この発言を削除"
                    onclick={() => remove(message)}
                    class="rounded p-1 text-slate-300 opacity-0 transition group-hover:opacity-100 hover:text-red-600 focus-visible:opacity-100"
                  >
                    <i class="bi bi-trash text-xs" aria-hidden="true"></i>
                  </button>
                {/if}
              </div>
            </div>
          </li>
        {/each}
      </ul>
    {/if}
  </div>

  <div>
    <FormErrors errors={form.errors} />

    <div class="flex items-end gap-2">
      <Textarea
        rows={2}
        maxlength={maxLength}
        placeholder="発言を入力（Shift + Enter で送信）"
        bind:value={form.body}
        onkeydown={onKeydown}
      />
      <Button variant="primary" icon="send" disabled={form.processing} onclick={send}>送信</Button>
    </div>
  </div>
</div>
