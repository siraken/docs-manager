<script lang="ts">
  import type { Snippet } from "svelte";
  import { inertia } from "@inertiajs/svelte";

  /**
   * href が内部の画面なら Inertia 遷移、external ならブラウザに任せる素のリンク。
   * PDF / CSV のダウンロードは後者 (Inertia の遷移だと XHR になりファイルを
   * 受け取れない)。
   */
  type Props = {
    href?: string | null;
    external?: boolean;
    disabled?: boolean;
    onclick?: (event: MouseEvent) => void;
    children?: Snippet;
  };

  let { href = null, external = false, disabled = false, onclick, children }: Props = $props();

  const BASE = "block w-full px-4 py-2 text-left text-sm transition";
  const classes = $derived(
    disabled
      ? `${BASE} cursor-default text-slate-400`
      : `${BASE} text-slate-700 hover:bg-slate-50 hover:text-slate-900`,
  );
</script>

{#if href && !disabled && !external}
  <a {href} use:inertia class={classes}>{@render children?.()}</a>
{:else if href && !disabled}
  <!-- ダウンロードなど、ブラウザに処理させたいリンク -->
  <a {href} class={classes}>{@render children?.()}</a>
{:else if onclick && !disabled}
  <button type="button" class={classes} {onclick}>{@render children?.()}</button>
{:else}
  <div class={classes}>{@render children?.()}</div>
{/if}
