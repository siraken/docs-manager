<script lang="ts">
  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import DetailList from "../../components/ui/DetailList.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import type { Report } from "../../lib/report-types";

  /**
   * 勤務報告の詳細。
   *
   * 移植前は reports/show.blade.php が @section('content') の中身ごと空で、
   * ルートは登録されているのに開いても何も表示されなかった。
   */
  type Props = { report: Report; urls: { back: string } };

  let { report, urls }: Props = $props();

  const rows = $derived([
    { label: "勤務日", value: report.dateLabel },
    { label: "件名", value: report.title },
    { label: "始業時刻", value: report.startTime ?? "-" },
    { label: "終業時刻", value: report.endTime ?? "-" },
    { label: "勤務時間", value: `${report.workTimeLabel} (${report.workHours} h)` },
    { label: "担当者", value: report.userName || "-" },
    { label: "取引先", value: report.customerName || "-" },
    { label: "案件", value: report.projectName || "-" },
  ]);
</script>

<PageHeader title="勤務報告 / 詳細">
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">戻る</Button>
    {#if report.urls}
      <Button href={report.urls.edit} variant="primary" icon="pencil">編集</Button>
    {/if}
  {/snippet}
</PageHeader>

<div class="max-w-2xl space-y-4">
  <DetailList {rows} />

  {#if report.description}
    <Card>
      <h2 class="mb-2 text-sm font-medium text-slate-500">詳細</h2>
      <p class="text-sm whitespace-pre-line text-slate-900">{report.description}</p>
    </Card>
  {/if}
</div>
