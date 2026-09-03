<script lang="ts">
  import type { Snippet } from "svelte";

  /**
   * Blade の x-modal を移植したもの。
   *
   * Blade 版は開閉を Alpine の x-data で内側に閉じ込め、トリガーをスロットで
   * 受け取っていた。Svelte 版は open を $bindable で外に出し、トリガーは
   * 呼び出し側が置く (ページヘッダーのボタン列に混ぜたいため)。
   */
  type Props = {
    open?: boolean;
    title?: string | null;
    maxWidth?: string;
    children?: Snippet;
    footer?: Snippet;
  };

  let {
    open = $bindable(false),
    title = null,
    maxWidth = "sm:max-w-lg",
    children,
    footer,
  }: Props = $props();

  function close() {
    open = false;
  }

  $effect(() => {
    const onKeydown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        close();
      }
    };

    window.addEventListener("keydown", onKeydown);

    return () => window.removeEventListener("keydown", onKeydown);
  });
</script>

{#if open}
  <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:items-center">
    <!-- 背景。キーボードからは Escape で閉じられるので、ここは装飾扱いにする -->
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div class="fixed inset-0 bg-slate-900/50" onclick={close}></div>

    <div
      role="dialog"
      aria-modal="true"
      class="relative w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-900/5 {maxWidth}"
    >
      {#if title}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <h2 class="text-base font-semibold text-slate-900">{title}</h2>
          <button
            type="button"
            aria-label="閉じる"
            onclick={close}
            class="rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
          >
            <i class="bi bi-x-lg text-sm" aria-hidden="true"></i>
          </button>
        </div>
      {/if}

      <div class="px-5 py-4">{@render children?.()}</div>

      {#if footer}
        <div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-3">
          {@render footer()}
        </div>
      {/if}
    </div>
  </div>
{/if}
