<script lang="ts">
  import { untrack } from "svelte";
  import { router } from "@inertiajs/svelte";

  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Select from "../../components/ui/Select.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { TrialBalanceRow, TrialBalanceTotals } from "../../lib/accounting-types";

  /**
   * 残高試算表。
   *
   * 科目ごとの借方合計・貸方合計と、区分から決まる側に立てた残高を出す。
   * 縦計は単一仕訳しか作れない以上、必ず貸借一致する。一致しない結果が
   * 出たらそれ自体が不具合なので、検算の結果を画面に出している。
   */
  type Props = {
    rows: TrialBalanceRow[];
    totals: TrialBalanceTotals;
    filter: { year: number | null; month: number | null };
    years: number[];
    urls: { self: string; back: string };
  };

  let { rows, totals, filter, years, urls }: Props = $props();

  const MONTHS = Array.from({ length: 12 }, (_, index) => index + 1);

  let year = $state<number | string>(untrack(() => filter.year ?? ""));
  let month = $state<number | string>(untrack(() => filter.month ?? ""));

  function search(event: SubmitEvent): void {
    event.preventDefault();

    router.get(urls.self, { year, month }, { preserveState: true, replace: true });
  }

  /** 区分ごとに色を変えて、貸借対照表と損益計算書の別を見分けやすくする */
  const badgeColor = (type: string): "brand" | "amber" | "green" | "slate" =>
    type === "asset" ? "brand" : type === "liability" || type === "equity" ? "amber" : type === "revenue" ? "green" : "slate";

  const periodLabel = $derived(
    filter.year === null
      ? "全期間"
      : filter.month === null
        ? `${filter.year}年`
        : `${filter.year}年${filter.month}月`,
  );
</script>

<PageHeader title="残高試算表">
  {#snippet description()}{periodLabel}の仕訳を集計しています{/snippet}
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">仕訳帳へ戻る</Button>
  {/snippet}
</PageHeader>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
  <Card class="lg:col-span-2">
    <form onsubmit={search} class="grid gap-3 sm:grid-cols-3 sm:items-end">
      <div>
        <Label for="year">年</Label>
        <Select id="year" bind:value={year}>
          <option value="">すべて</option>
          {#each years as option (option)}
            <option value={option}>{option}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="month">月</Label>
        <Select id="month" bind:value={month}>
          <option value="">すべて</option>
          {#each MONTHS as option (option)}
            <option value={option}>{option}</option>
          {/each}
        </Select>
      </div>

      <Button type="submit" variant="primary" icon="search">集計する</Button>
    </form>
  </Card>

  <Card class="flex flex-col items-center justify-center">
    <p class="text-xs font-medium text-slate-500">貸借の検算</p>
    {#if totals.isBalanced}
      <p class="mt-1 text-lg font-semibold text-green-700">
        <i class="bi bi-check-circle-fill" aria-hidden="true"></i> 一致
      </p>
      <p class="mt-1 text-xs text-slate-400">借方合計 = 貸方合計</p>
    {:else}
      <p class="mt-1 text-lg font-semibold text-red-700">
        <i class="bi bi-x-circle-fill" aria-hidden="true"></i> 不一致
      </p>
      <p class="mt-1 text-xs text-red-600">集計に不具合があります</p>
    {/if}
  </Card>
</div>

{#if rows.length === 0}
  <EmptyState>集計対象の仕訳がありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">勘定科目</th>
      <th class="hidden px-4 py-3 sm:table-cell">区分</th>
      <th class="px-4 py-3 text-right">借方合計</th>
      <th class="px-4 py-3 text-right">貸方合計</th>
      <th class="px-4 py-3 text-right">借方残高</th>
      <th class="px-4 py-3 text-right">貸方残高</th>
    {/snippet}

    {#each rows as row (row.accountId)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle font-medium whitespace-nowrap text-slate-900">{row.accountName}</td>
        <td class="hidden px-4 py-3 align-middle sm:table-cell">
          <Badge color={badgeColor(row.typeValue)}>{row.type}</Badge>
        </td>
        <td class="px-4 py-3 text-right align-middle whitespace-nowrap text-slate-600 tabular">
          {row.debitTotalLabel}
        </td>
        <td class="px-4 py-3 text-right align-middle whitespace-nowrap text-slate-600 tabular">
          {row.creditTotalLabel}
        </td>
        <td class="px-4 py-3 text-right align-middle font-semibold whitespace-nowrap text-slate-900 tabular">
          {row.debitBalanceLabel}
        </td>
        <td class="px-4 py-3 text-right align-middle font-semibold whitespace-nowrap text-slate-900 tabular">
          {row.creditBalanceLabel}
        </td>
      </tr>
    {/each}

    <tr class="border-t-2 border-slate-300 bg-slate-50 font-semibold">
      <td class="px-4 py-3 text-slate-900" colspan="2">合計</td>
      <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{totals.debitTotal}</td>
      <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{totals.creditTotal}</td>
      <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{totals.debitBalance}</td>
      <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">{totals.creditBalance}</td>
    </tr>
  </Table>
{/if}
