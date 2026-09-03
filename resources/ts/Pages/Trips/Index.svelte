<script lang="ts">
  import { Link } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import CsvImportModal from "../../components/CsvImportModal.svelte";
  import type { Travel } from "../../lib/travel-types";

  type Props = { trips: Travel[]; urls: { create: string; import: string } };

  let { trips, urls }: Props = $props();

  let importing = $state(false);
</script>

<PageHeader title="出張申請">
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">出張申請をする</Button>
    <Button icon="upload" onclick={() => (importing = true)}>CSV取り込み</Button>
  {/snippet}
</PageHeader>

<CsvImportModal bind:open={importing} url={urls.import} />

{#if trips.length === 0}
  <EmptyState>申請がまだありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">申請日</th>
      <th class="px-4 py-3">出張先</th>
      <th class="hidden px-4 py-3 sm:table-cell">目的</th>
      <th class="hidden px-4 py-3 sm:table-cell">出発日</th>
      <th class="px-4 py-3">申請者</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each trips as row (row.id)}
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
          {row.dateFrom}
        </td>
        <td class="px-4 py-3 align-middle text-slate-600">{row.applyPerson}</td>
        <td class="px-4 py-3 text-right align-middle">
          <!-- 移行前はここのドロップダウンに「ごみ箱に入れる」があり、
               リンク先が発注書の削除ルート (orders.delete) を指していた。
               出張申請に削除機能は無いため外してある。 -->
          {#if row.urls}
            <Button href={row.urls.pdf} external size="sm" icon="file-earmark-pdf">PDF</Button>
          {/if}
        </td>
      </tr>
    {/each}
  </Table>
{/if}
