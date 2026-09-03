<script lang="ts">
  import { Link } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import CsvImportModal from "../../components/CsvImportModal.svelte";
  import type { TravelExpense } from "../../lib/travel-types";

  type Props = { expenses: TravelExpense[]; urls: { create: string; import: string } };

  let { expenses, urls }: Props = $props();

  let importing = $state(false);
</script>

<PageHeader title="出張旅費精算">
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">旅費精算をする</Button>
    <Button icon="upload" onclick={() => (importing = true)}>CSV取り込み</Button>
  {/snippet}
</PageHeader>

<CsvImportModal bind:open={importing} url={urls.import} />

{#if expenses.length === 0}
  <EmptyState>精算がまだありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">申請日</th>
      <th class="px-4 py-3">出張先</th>
      <th class="hidden px-4 py-3 sm:table-cell">目的</th>
      <th class="hidden px-4 py-3 sm:table-cell">精算日</th>
      <th class="px-4 py-3">申請者</th>
      <th class="px-4 py-3 text-right">合計</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each expenses as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{row.applyDate}</td>
        <td class="px-4 py-3 align-middle font-medium text-slate-900">
          {#if row.urls}
            <Link href={row.urls.show} class="text-brand-700 hover:text-brand-900 hover:underline">
              {row.destination}
            </Link>
          {:else}
            {row.destination}
          {/if}
        </td>
        <td class="hidden px-4 py-3 align-middle text-slate-600 sm:table-cell">{row.shortPurpose}</td>
        <td class="hidden px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular sm:table-cell">
          {row.payDate}
        </td>
        <td class="px-4 py-3 align-middle text-slate-600">{row.applyPerson}</td>
        <td class="px-4 py-3 text-right align-middle font-semibold whitespace-nowrap text-slate-900 tabular">
          ￥{row.totalFeeLabel}
        </td>
        <td class="px-4 py-3 text-right align-middle">
          {#if row.urls}
            <div class="flex justify-end gap-2">
              <Button href={row.urls.edit} size="sm" icon="pencil">編集</Button>
              <Button href={row.urls.pdf} external size="sm" icon="file-earmark-pdf">PDF</Button>
            </div>
          {/if}
        </td>
      </tr>
    {/each}
  </Table>
{/if}
