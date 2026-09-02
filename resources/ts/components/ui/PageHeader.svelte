<script lang="ts">
  import type { Snippet } from "svelte";

  type Props = { title?: string | null; description?: Snippet; actions?: Snippet; class?: string };
  let { title = null, description, actions, class: extraClass = "" }: Props = $props();
</script>

<div class="mb-6 flex flex-wrap items-center justify-between gap-3 {extraClass}">
  <div>
    {#if title}
      <h1 class="text-xl font-semibold tracking-tight text-slate-900">{title}</h1>
    {/if}
    {#if description}
      <p class="mt-1 text-sm text-slate-500">{@render description()}</p>
    {/if}
  </div>
  {#if actions}
    <div class="flex flex-wrap items-center gap-2">{@render actions()}</div>
  {/if}
</div>
