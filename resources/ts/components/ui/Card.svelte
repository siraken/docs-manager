<script lang="ts">
  import type { Snippet } from "svelte";

  type Props = { class?: string; children?: Snippet; [key: string]: unknown };
  let { class: extraClass = "", children, ...rest }: Props = $props();
</script>

<div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 {extraClass}" {...rest}>
  {@render children?.()}
</div>
