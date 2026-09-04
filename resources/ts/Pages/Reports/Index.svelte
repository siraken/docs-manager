<script lang="ts">
  import { untrack } from "svelte";
  import { Link, router } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import Modal from "../../components/ui/Modal.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Select from "../../components/ui/Select.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { Report, ReportFilter, ReportSummary } from "../../lib/report-types";

  /**
   * 勤務報告の一覧。
   *
   * 移植前は
   *  - 年月セレクトが GET で送る year / month をコントローラが見ておらず、
   *    絞り込みが一切効かなかった
   *  - 集計をビューの中でページ内の行だけ足しており「総」勤務時間ではなかった。
   *    総勤務日数にいたっては存在しないカラムの合計で常に 0
   *  - 年の選択肢が 2020〜2024 の直書きで、2025 年以降を選べなかった
   * という状態だった。絞り込みも集計もサーバー側に移してある。
   */
  type Props = {
    reports: Report[];
    summary: ReportSummary;
    filter: ReportFilter;
    years: number[];
    urls: { self: string; create: string };
  };

  let { reports, summary, filter, years, urls }: Props = $props();

  const MONTHS = Array.from({ length: 12 }, (_, index) => index + 1);

  /*
   * 絞り込みは GET なので useForm ではなく router.get で送る。
   *
   * 初期値をそのまま握るのが正しい (untrack で明示している)。検索は
   * preserveState で送るため再マウントされず、入力欄には利用者が打った値が
   * 残る。サーバーから返る filter は、それと同じ内容を折り返したものになる。
   */
  let year = $state<number | string>(untrack(() => filter.year ?? ""));
  let month = $state<number | string>(untrack(() => filter.month ?? ""));
  let keyword = $state(untrack(() => filter.keyword ?? ""));

  function search(event: SubmitEvent): void {
    event.preventDefault();

    router.get(urls.self, { year, month, q: keyword }, { preserveState: true, replace: true });
  }

  let confirming = $state(false);
  let target = $state<Report | null>(null);

  function askDelete(row: Report): void {
    target = row;
    confirming = true;
  }

  function confirmDelete(): void {
    if (target?.urls) {
      router.delete(target.urls.delete);
    }

    confirming = false;
  }
</script>

<PageHeader title="勤務報告">
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">勤務を記録する</Button>
  {/snippet}
</PageHeader>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
  <Card class="lg:col-span-2">
    <form onsubmit={search} class="grid gap-3 sm:grid-cols-4 sm:items-end">
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

      <div>
        <Label for="q">件名</Label>
        <Input id="q" bind:value={keyword} placeholder="キーワード" />
      </div>

      <Button type="submit" variant="primary" icon="search">検索</Button>
    </form>
  </Card>

  <Card class="flex items-center justify-around gap-4">
    <div class="text-center">
      <p class="text-xs font-medium text-slate-500">総勤務時間</p>
      <p class="text-2xl font-semibold text-slate-900 tabular">{summary.totalWorkTimeLabel}</p>
    </div>
    <div class="text-center">
      <p class="text-xs font-medium text-slate-500">総勤務日数</p>
      <p class="text-2xl font-semibold text-slate-900 tabular">{summary.workDays} <span class="text-sm">日</span></p>
    </div>
  </Card>
</div>

{#if reports.length === 0}
  <EmptyState>該当する勤務報告がありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">日付</th>
      <th class="px-4 py-3">件名</th>
      <th class="hidden px-4 py-3 md:table-cell">案件</th>
      <th class="hidden px-4 py-3 sm:table-cell">始業</th>
      <th class="hidden px-4 py-3 sm:table-cell">終業</th>
      <th class="px-4 py-3 text-right">勤務時間</th>
      <th class="px-4 py-3">担当者</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each reports as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{row.dateLabel}</td>
        <td class="px-4 py-3 align-middle">
          {#if row.urls}
            <Link href={row.urls.show} class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
              {row.title}
            </Link>
          {:else}
            <span class="font-medium text-slate-900">{row.title}</span>
          {/if}
        </td>
        <td class="hidden px-4 py-3 align-middle text-slate-600 md:table-cell">{row.projectName || "-"}</td>
        <td class="hidden px-4 py-3 align-middle text-slate-600 tabular sm:table-cell">{row.startTime ?? "-"}</td>
        <td class="hidden px-4 py-3 align-middle text-slate-600 tabular sm:table-cell">{row.endTime ?? "-"}</td>
        <td class="px-4 py-3 text-right align-middle font-semibold whitespace-nowrap text-slate-900 tabular">
          {row.workTimeLabel}
        </td>
        <td class="px-4 py-3 align-middle text-slate-600">{row.userName || "-"}</td>
        <td class="px-4 py-3 text-right align-middle whitespace-nowrap">
          {#if row.urls}
            <Button href={row.urls.edit} size="sm" icon="pencil">編集</Button>
          {/if}
          <Button size="sm" variant="ghost" icon="trash" onclick={() => askDelete(row)}>削除</Button>
        </td>
      </tr>
    {/each}
  </Table>
{/if}

<Modal bind:open={confirming} title="勤務報告の削除">
  <p class="text-sm text-slate-600">
    {target?.dateLabel}「{target?.title}」を削除します。取り消せません。
  </p>

  {#snippet footer()}
    <Button onclick={() => (confirming = false)}>キャンセル</Button>
    <Button variant="danger" icon="trash" onclick={confirmDelete}>削除する</Button>
  {/snippet}
</Modal>
