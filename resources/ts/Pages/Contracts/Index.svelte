<script lang="ts">
  import { Link } from "@inertiajs/svelte";

  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import DeleteConfirm from "../../components/DeleteConfirm.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { Contract } from "../../lib/master-types";

  type Props = { contracts: Contract[]; urls: { create: string } };

  let { contracts, urls }: Props = $props();

  /**
   * 削除の確認。移植前は編集画面に type="button" の削除ボタンがあるだけで
   * 何も起きず、サーバー側の受け口も無かった。
   */
  let confirm = $state<DeleteConfirm<Contract> | undefined>();

  /** 契約期間から導出した状態。保存されたカラムではない */
  const badgeColor = (status: string): "green" | "amber" | "slate" =>
    status === "active" ? "green" : status === "scheduled" ? "amber" : "slate";
</script>

<PageHeader title="契約管理">
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">契約を追加</Button>
  {/snippet}
</PageHeader>

{#if contracts.length === 0}
  <EmptyState>契約がまだありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">契約名</th>
      <th class="hidden px-4 py-3 sm:table-cell">契約番号</th>
      <th class="px-4 py-3">取引先</th>
      <th class="px-4 py-3">契約期間</th>
      <th class="px-4 py-3">状態</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each contracts as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle">
          {#if row.urls}
            <Link href={row.urls.edit} class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
              {row.name}
            </Link>
          {:else}
            <span class="font-medium text-slate-900">{row.name}</span>
          {/if}
        </td>
        <td class="hidden px-4 py-3 align-middle text-slate-600 tabular sm:table-cell">{row.contractNo ?? "-"}</td>
        <td class="px-4 py-3 align-middle text-slate-600">{row.customerName || "-"}</td>
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{row.termLabel}</td>
        <td class="px-4 py-3 align-middle"><Badge color={badgeColor(row.statusValue)}>{row.status}</Badge></td>
        <td class="px-4 py-3 text-right align-middle">
          <Button size="sm" variant="ghost" icon="trash" onclick={() => confirm?.ask(row)}>削除</Button>
        </td>
      </tr>
    {/each}
  </Table>
{/if}

<DeleteConfirm bind:this={confirm} title="契約の削除">
  {#snippet body(row: Contract)}「{row.name}」を削除します。取り消せません。{/snippet}
</DeleteConfirm>
