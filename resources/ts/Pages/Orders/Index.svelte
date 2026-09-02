<script lang="ts">
  import { Link } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Dropdown from "../../components/ui/Dropdown.svelte";
  import DropdownDivider from "../../components/ui/DropdownDivider.svelte";
  import DropdownItem from "../../components/ui/DropdownItem.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import OrderStatusPill from "../../components/OrderStatusPill.svelte";
  import type { OrderListItem } from "../../lib/order-types";

  type Props = {
    orders: OrderListItem[];
    urls: { create: string; trash: string; setStatus: string };
  };

  let { orders, urls }: Props = $props();
</script>

<PageHeader title="発注書">
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">発注書を新しく作る</Button>
    <Button href={urls.trash} icon="trash">ごみ箱</Button>
  {/snippet}
</PageHeader>

{#if orders.length === 0}
  <EmptyState>データがありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">ステータス</th>
      <th class="px-4 py-3">文書</th>
      <th class="px-4 py-3">発行日</th>
      <th class="px-4 py-3">有効期限</th>
      <th class="px-4 py-3 text-right">金額</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each orders as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle">
          <div class="flex flex-col gap-1">
            <OrderStatusPill type="issued" id={row.id} value={row.issueStatus} url={urls.setStatus} />
            <OrderStatusPill type="ordered" id={row.id} value={row.orderStatus} url={urls.setStatus} />
          </div>
        </td>
        <td class="px-4 py-3 align-middle">
          <Link href={row.urls.show} class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
            {row.displayName}
          </Link>
          <p class="text-xs text-slate-400">#{row.orderNo}</p>
        </td>
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{row.issuedDateLabel}</td>
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{row.expDateLabel}</td>
        <td class="px-4 py-3 text-right align-middle font-semibold whitespace-nowrap text-slate-900 tabular">
          {row.totalLabel}円
        </td>
        <td class="px-4 py-3 text-right align-middle">
          <Dropdown>
            {#snippet trigger()}
              <Button size="sm" icon="gear-fill" aria-label="操作" />
            {/snippet}

            {#if row.note}
              <DropdownItem disabled>{row.note}</DropdownItem>
              <DropdownDivider />
            {/if}
            <DropdownItem href={row.urls.edit}>編集</DropdownItem>
            <DropdownItem href={row.urls.pdf} external>PDF出力</DropdownItem>
            <DropdownItem href={row.urls.csv} external>CSV出力</DropdownItem>
            <DropdownItem href={row.urls.trash}>ごみ箱に入れる</DropdownItem>
          </Dropdown>
        </td>
      </tr>
    {/each}
  </Table>
{/if}
