<script lang="ts">
  /**
   * Blade の x-flash を移植したもの。
   * メッセージは Inertia の共有データ (HandleInertiaRequests::flash) から届く。
   *
   * Blade ではページ遷移のたびに DOM ごと描き直されるため、トーストは自然に
   * 消えていた。Inertia はレイアウトを保持するので、共有データの flash が
   * null に変わったことを見て自分で閉じる。
   *
   * 表示するメッセージを内部の state 1 つに寄せているのは、表示条件が
   * 2 つ (届いた flash と「閉じたか」) に分かれていると、片方が変わった瞬間に
   * 表示が中途半端な状態で止まるため。
   *
   * 出現アニメーションは Svelte の transition: ではなく CSS (.toast-enter) で
   * 行う。Inertia がレイアウトを保持したままページを差し替えるとき、Svelte の
   * トランジションが中断され、opacity 0 の要素が DOM に残ることがあった。
   */
  type Flash = { message: string; status: string; icon: string } | null;

  type Props = { flash?: Flash; top?: string };
  let { flash = null, top = "top-20" }: Props = $props();

  /** いま出しているトースト。閉じると null になる */
  let current = $state<Flash>(null);

  // 共有データが変わったら追随する (遷移して null になれば消える)
  $effect(() => {
    current = flash;
  });

  const TONES: Record<string, string> = {
    danger: "bg-red-600",
    success: "bg-green-600",
    warning: "bg-amber-500",
  };

  const tone = $derived(TONES[current?.status ?? ""] ?? "bg-slate-800");
</script>

{#if current}
  <div
    role="alert"
    aria-live="assertive"
    class="toast-enter fixed {top} left-1/2 z-50 flex w-[min(28rem,calc(100vw-2rem))] -translate-x-1/2 items-start
           gap-3 rounded-xl px-4 py-3 text-sm text-white shadow-lg {tone}"
  >
    <i class="bi bi-{current.icon || 'exclamation-circle-fill'} mt-0.5 shrink-0" aria-hidden="true"></i>
    <span class="flex-1">{current.message}</span>
    <button
      type="button"
      aria-label="閉じる"
      onclick={() => (current = null)}
      class="-mt-0.5 -mr-1 shrink-0 rounded p-1 text-white/70 transition hover:bg-white/10 hover:text-white"
    >
      <i class="bi bi-x-lg text-xs" aria-hidden="true"></i>
    </button>
  </div>
{/if}
