<script lang="ts">
  import Card from "./Card.svelte";

  /**
   * 「ラベル : 値」を並べる詳細表示。
   * 出張申請と旅費精算の詳細画面が使う (Blade 版は同じ dl を両方に書いていた)。
   */
  type Props = { rows: { label: string; value: string }[]; class?: string };

  let { rows, class: extraClass = "" }: Props = $props();
</script>

<Card class="p-0 {extraClass}">
  <dl class="divide-y divide-slate-100">
    {#each rows as row (row.label)}
      <div class="grid grid-cols-3 gap-4 px-6 py-3.5">
        <dt class="text-sm font-medium text-slate-500">{row.label}</dt>
        <dd class="col-span-2 text-sm whitespace-pre-line text-slate-900">{row.value}</dd>
      </div>
    {/each}
  </dl>
</Card>
