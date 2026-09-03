<script lang="ts">
  import { router } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Select from "../../components/ui/Select.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { Option, Project } from "../../lib/master-types";

  type Props = {
    projects: Project[];
    totalPrice: number;
    totalPriceLabel: string;
    criteria: { dateField: string; year: number; month: number };
    columns: Option[];
    years: number[];
    urls: { self: string; back: string };
  };

  let { projects, totalPriceLabel, criteria, columns, years, urls }: Props = $props();

  const MONTHS = Array.from({ length: 12 }, (_, index) => index + 1);

  /** Select は属性をそのまま通すだけなので、ハンドラの型はここで与える */
  type SelectEvent = Event & { currentTarget: HTMLSelectElement };

  /**
   * 絞り込みはクエリ文字列で表す。
   *
   * Blade の頃は location.href を差し替えてページ全体を読み直していた。
   * preserveState でセレクトのフォーカスを保ったまま props だけ差し替える。
   */
  function reload(patch: Partial<typeof criteria>): void {
    const next = { ...criteria, ...patch };

    router.get(
      urls.self,
      { type: next.dateField, year: next.year, month: next.month },
      { preserveState: true, preserveScroll: true, replace: true },
    );
  }
</script>

<PageHeader title="売上分析">
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">戻る</Button>
  {/snippet}
</PageHeader>

<Card class="mb-6">
  <div class="grid gap-4 sm:grid-cols-3">
    <div>
      <Label for="search-column">検索条件</Label>
      <!-- 集計対象にできる日付は SalesAnalysisCriteria::DATE_FIELDS が唯一の定義。
           ここに無い値はユースケース側で弾かれる。 -->
      <Select
        id="search-column"
        value={criteria.dateField}
        onchange={(event: SelectEvent) => reload({ dateField: event.currentTarget.value })}
      >
        {#each columns as column (column.value)}
          <option value={column.value}>{column.label}</option>
        {/each}
      </Select>
    </div>
    <div>
      <Label for="search-year">年</Label>
      <Select
        id="search-year"
        value={criteria.year}
        onchange={(event: SelectEvent) => reload({ year: Number(event.currentTarget.value) })}
      >
        {#each years as year (year)}
          <option value={year}>{year}年</option>
        {/each}
      </Select>
    </div>
    <div>
      <Label for="search-month">月</Label>
      <Select
        id="search-month"
        value={criteria.month}
        onchange={(event: SelectEvent) => reload({ month: Number(event.currentTarget.value) })}
      >
        {#each MONTHS as month (month)}
          <option value={month}>{month}月</option>
        {/each}
      </Select>
    </div>
  </div>
</Card>

<Table>
  {#snippet head()}
    <th class="px-4 py-3">案件名</th>
    <th class="px-4 py-3 text-right">請求金額</th>
  {/snippet}

  {#each projects as row (row.id)}
    <tr class="transition hover:bg-slate-50">
      <td class="px-4 py-3 text-slate-700">{row.name}</td>
      <td class="px-4 py-3 text-right font-medium whitespace-nowrap text-slate-900 tabular">￥{row.priceLabel}</td>
    </tr>
  {/each}

  <tr class="bg-slate-50 font-semibold">
    <td class="px-4 py-3 text-slate-700">合計</td>
    <td class="px-4 py-3 text-right whitespace-nowrap text-slate-900 tabular">￥{totalPriceLabel}</td>
  </tr>
</Table>
