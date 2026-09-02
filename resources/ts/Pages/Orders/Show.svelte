<script lang="ts">
  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { OrderDetail } from "../../lib/order-types";

  type Props = { order: OrderDetail; urls: { index: string } };
  let { order, urls }: Props = $props();

  const yen = (value: number): string => value.toLocaleString("ja-JP");

  const info = $derived([
    ["発注書番号", `#${order.orderNo}`],
    ["取引先", order.addressee],
    ["件名", order.title || "-"],
    ["発注金額", `${order.totalLabel}円`],
    ["発行日", order.issuedDateLabel],
    ["有効期限", order.expDateLabel],
  ] as const);
</script>

<PageHeader title={order.displayName}>
  {#snippet description()}
    #{order.orderNo}{order.isDeleted ? "（ごみ箱）" : ""}
  {/snippet}
  {#snippet actions()}
    <Button href={urls.index} icon="arrow-left">戻る</Button>
    <Button href={order.urls.edit} icon="pencil">編集</Button>
    <Button href={order.urls.pdf} external icon="file-earmark-pdf">PDF</Button>
    <Button href={order.urls.csv} external icon="download">CSV</Button>
  {/snippet}
</PageHeader>

<div class="space-y-6">
  <div class="grid gap-6 lg:grid-cols-2">
    <Card class="p-0">
      <dl class="divide-y divide-slate-100">
        {#each info as [label, value] (label)}
          <div class="grid grid-cols-3 gap-4 px-6 py-3.5">
            <dt class="text-sm font-medium text-slate-500">{label}</dt>
            <dd class="col-span-2 text-sm text-slate-900">{value}</dd>
          </div>
        {/each}
      </dl>
    </Card>

    <Card>
      <Label>社内メモ</Label>
      <p class="mt-1 min-h-24 text-sm whitespace-pre-line text-slate-700">
        {order.note || "（メモはありません）"}
      </p>
      <!-- TODO: メモの編集は未実装。保存先 (Order::changeNote) はドメイン側に
           用意してあるので、フォームとルートを足せば繋がる。 -->
    </Card>
  </div>

  <div>
    <h2 class="mb-3 text-sm font-semibold text-slate-700">明細</h2>

    {#if order.lines.length === 0}
      <EmptyState>明細がありません</EmptyState>
    {:else}
      <Table>
        {#snippet head()}
          <th class="px-4 py-3">品名</th>
          <th class="px-4 py-3 text-right">数量</th>
          <th class="px-4 py-3 text-right">単価</th>
          <th class="px-4 py-3">税区分</th>
          <th class="px-4 py-3 text-right">金額(税込)</th>
        {/snippet}

        {#each order.lines as line, index (index)}
          <tr class="transition hover:bg-slate-50">
            <td class="px-4 py-3 text-slate-900">{line.itemName}</td>
            <td class="px-4 py-3 text-right whitespace-nowrap text-slate-600 tabular">
              {yen(line.quantity)}{line.unit ?? ""}
            </td>
            <td class="px-4 py-3 text-right whitespace-nowrap text-slate-600 tabular">{yen(line.unitCost)}</td>
            <td class="px-4 py-3"><Badge>{line.taxLabel}</Badge></td>
            <td class="px-4 py-3 text-right font-medium whitespace-nowrap text-slate-900 tabular">
              {yen(line.total)}
            </td>
          </tr>
        {/each}

        <tr class="bg-slate-50">
          <td colspan="4" class="px-4 py-3 text-right text-sm text-slate-600">小計</td>
          <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{yen(order.subtotal)}</td>
        </tr>
        <tr class="bg-slate-50">
          <td colspan="4" class="px-4 py-3 text-right text-sm text-slate-600">消費税</td>
          <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{yen(order.tax)}</td>
        </tr>
        <tr class="bg-slate-50 font-semibold">
          <td colspan="4" class="px-4 py-3 text-right text-sm text-slate-700">合計</td>
          <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{order.totalLabel}</td>
        </tr>
      </Table>
    {/if}
  </div>

  {#if order.remarks}
    <Card>
      <Label>備考</Label>
      <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{order.remarks}</p>
    </Card>
  {/if}
</div>
