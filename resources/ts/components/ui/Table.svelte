<script lang="ts">
  import type { Snippet } from "svelte";

  type Props = { head?: Snippet; children?: Snippet; class?: string };
  let { head, children, class: extraClass = "" }: Props = $props();
</script>

<!-- 横スクロールはこのラッパーの中だけで起こす -->
<div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
  <table class="min-w-full divide-y divide-slate-200 text-sm {extraClass}">
    <thead class="bg-slate-50">
      <tr class="text-left text-xs font-semibold tracking-wide text-slate-500 uppercase">
        {@render head?.()}
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      {@render children?.()}
    </tbody>
  </table>
</div>
