<script lang="ts">
  import type { Snippet } from "svelte";

  type Props = {
    color?: "slate" | "brand" | "green" | "red" | "amber";
    class?: string;
    children?: Snippet;
  };

  let { color = "slate", class: extraClass = "", children }: Props = $props();

  const COLORS: Record<string, string> = {
    slate: "bg-slate-100 text-slate-700 ring-slate-200",
    brand: "bg-brand-50 text-brand-800 ring-brand-200",
    green: "bg-green-50 text-green-700 ring-green-200",
    red: "bg-red-50 text-red-700 ring-red-200",
    amber: "bg-amber-50 text-amber-800 ring-amber-200",
  };
</script>

<span
  class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {COLORS[
    color
  ] ?? COLORS.slate} {extraClass}"
>
  {@render children?.()}
</span>
