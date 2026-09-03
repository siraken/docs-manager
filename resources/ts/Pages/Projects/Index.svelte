<script lang="ts">
  import { Link } from "@inertiajs/svelte";

  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { Project } from "../../lib/master-types";

  type Props = { projects: Project[]; urls: { create: string; analysis: string } };

  let { projects, urls }: Props = $props();
</script>

<PageHeader title="案件管理">
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">新規案件</Button>
    <Button href={urls.analysis} icon="graph-up">分析</Button>
  {/snippet}
</PageHeader>

{#if projects.length === 0}
  <EmptyState>案件がまだありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">案件名</th>
      <th class="px-4 py-3">取引先</th>
      <th class="px-4 py-3">状態</th>
      <th class="px-4 py-3">開始日</th>
      <th class="px-4 py-3">終了日</th>
      <th class="px-4 py-3">支払日</th>
      <th class="px-4 py-3 text-right">請求金額</th>
    {/snippet}

    {#each projects as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3">
          {#if row.urls}
            <Link href={row.urls.edit} class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
              {row.name}
            </Link>
          {:else}
            <span class="font-medium text-slate-900">{row.name}</span>
          {/if}
        </td>
        <td class="px-4 py-3 text-slate-600">{row.clientId ?? ""}</td>
        <td class="px-4 py-3"><Badge>{row.status}</Badge></td>
        <td class="px-4 py-3 whitespace-nowrap text-slate-600 tabular">{row.startDateLabel}</td>
        <td class="px-4 py-3 whitespace-nowrap text-slate-600 tabular">{row.endDateLabel}</td>
        <td class="px-4 py-3 whitespace-nowrap text-slate-600 tabular">{row.paymentDateLabel}</td>
        <td class="px-4 py-3 text-right font-semibold whitespace-nowrap text-slate-900 tabular">
          ￥{row.priceLabel}
        </td>
      </tr>
    {/each}
  </Table>
{/if}
