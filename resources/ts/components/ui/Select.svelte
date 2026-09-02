<script lang="ts">
  import type { Snippet } from "svelte";

  type Props = {
    value?: string | number | null;
    class?: string;
    children?: Snippet;
    [key: string]: unknown;
  };

  let { value = $bindable(""), class: extraClass = "", children, ...rest }: Props = $props();

  const CLASSES =
    "block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm" +
    " ring-1 ring-inset ring-slate-300" +
    " focus:ring-2 focus:ring-inset focus:ring-brand-600";
</script>

<select bind:value class="{CLASSES} {extraClass}" {...rest}>{@render children?.()}</select>
