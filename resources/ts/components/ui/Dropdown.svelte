<script lang="ts">
  import type { Snippet } from "svelte";

  /**
   * Blade の x-dropdown を移植したもの。
   * 開閉は Alpine ではなく Svelte の状態で持つ。Blade 版と同じく
   * Escape キーと外側クリックで閉じる。
   */
  type Props = {
    align?: "left" | "right";
    width?: string;
    class?: string;
    trigger?: Snippet;
    children?: Snippet;
  };

  let {
    align = "right",
    width = "w-56",
    class: extraClass = "",
    trigger,
    children,
  }: Props = $props();

  let open = $state(false);
  let root: HTMLElement | undefined = $state();

  function onWindowKeydown(event: KeyboardEvent): void {
    if (event.key === "Escape") {
      open = false;
    }
  }

  function onWindowPointerdown(event: PointerEvent): void {
    if (open && root && !root.contains(event.target as Node)) {
      open = false;
    }
  }
</script>

<svelte:window onkeydown={onWindowKeydown} onpointerdown={onWindowPointerdown} />

<div bind:this={root} class="relative inline-block {extraClass}">
  <div class="contents" onclick={() => (open = !open)} role="presentation">
    {@render trigger?.()}
  </div>

  {#if open}
    <!-- 項目を選んだら閉じる。個々の項目に onclick を付けなくて済むよう親で拾う -->
    <div
      class="absolute z-30 mt-2 {width} {align === 'right'
        ? 'right-0 origin-top-right'
        : 'left-0 origin-top-left'} overflow-hidden rounded-xl bg-white py-1 shadow-lg ring-1 ring-slate-900/5"
      onclick={() => (open = false)}
      role="presentation"
    >
      {@render children?.()}
    </div>
  {/if}
</div>
