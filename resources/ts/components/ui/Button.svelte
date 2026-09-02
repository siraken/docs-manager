<script lang="ts">
  import type { Snippet } from "svelte";
  import { inertia } from "@inertiajs/svelte";

  /**
   * Blade の x-button を移植したもの。
   * href を渡すと <a>、渡さなければ <button> になるのは Blade 版と同じ。
   *
   * href が内部の画面を指す場合は Inertia の遷移 (use:inertia) にする。
   * そうしないと素のリンクとしてページ全体が読み直され、SPA にした意味が無い。
   * PDF / CSV のダウンロードのようにブラウザに処理させたいものは
   * external を渡して素のリンクにすること。
   */
  type Props = {
    variant?: "primary" | "secondary" | "danger" | "ghost";
    size?: "sm" | "md" | "lg";
    href?: string | null;
    external?: boolean;
    icon?: string | null;
    type?: "button" | "submit" | "reset";
    class?: string;
    children?: Snippet;
    [key: string]: unknown;
  };

  let {
    variant = "secondary",
    size = "md",
    href = null,
    external = false,
    icon = null,
    type = "button",
    class: extraClass = "",
    children,
    ...rest
  }: Props = $props();

  const BASE =
    "inline-flex items-center justify-center gap-1.5 rounded-lg font-medium whitespace-nowrap transition" +
    " focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600" +
    " disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50";

  const VARIANTS: Record<string, string> = {
    primary: "bg-brand-600 text-white shadow-sm hover:bg-brand-700 active:bg-brand-800",
    secondary: "bg-white text-slate-700 ring-1 ring-slate-300 shadow-sm hover:bg-slate-50 active:bg-slate-100",
    danger: "bg-red-600 text-white shadow-sm hover:bg-red-700 active:bg-red-800",
    ghost: "text-slate-600 hover:bg-slate-100 hover:text-slate-900",
  };

  const SIZES: Record<string, string> = {
    sm: "px-2.5 py-1.5 text-xs",
    md: "px-3.5 py-2 text-sm",
    lg: "w-full px-4 py-2.5 text-sm",
  };

  const classes = $derived(
    [BASE, VARIANTS[variant] ?? VARIANTS.secondary, SIZES[size] ?? SIZES.md, extraClass]
      .filter(Boolean)
      .join(" "),
  );
</script>

{#if href && !external}
  <a {href} use:inertia class={classes} {...rest}>
    {#if icon}<i class="bi bi-{icon}" aria-hidden="true"></i>{/if}{@render children?.()}
  </a>
{:else if href}
  <a {href} class={classes} {...rest}>
    {#if icon}<i class="bi bi-{icon}" aria-hidden="true"></i>{/if}{@render children?.()}
  </a>
{:else}
  <button {type} class={classes} {...rest}>
    {#if icon}<i class="bi bi-{icon}" aria-hidden="true"></i>{/if}{@render children?.()}
  </button>
{/if}
