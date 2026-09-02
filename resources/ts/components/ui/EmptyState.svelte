<script lang="ts">
  import type { Snippet } from "svelte";

  type Props = { class?: string; children?: Snippet };
  let { class: extraClass = "", children }: Props = $props();
</script>

<p
  class="rounded-xl border border-dashed border-slate-300 bg-white/60 px-6 py-12 text-center text-sm text-slate-500 {extraClass}"
>
  {@render children?.()}
</p>
