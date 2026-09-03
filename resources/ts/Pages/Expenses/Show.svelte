<script lang="ts">
  import Button from "../../components/ui/Button.svelte";
  import DetailList from "../../components/ui/DetailList.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import type { TravelExpense } from "../../lib/travel-types";

  type Props = { expense: TravelExpense; urls: { back: string } };

  let { expense, urls }: Props = $props();

  const yen = (amount: number) => `¥${amount.toLocaleString("ja-JP")}`;

  const rows = $derived([
    { label: "管理ID", value: expense.relId },
    { label: "出張先", value: expense.destination },
    { label: "目的", value: expense.purpose },
    { label: "申請者", value: expense.applyPerson },
    { label: "交通費", value: yen(expense.transportationFee) },
    { label: "ガソリン代", value: yen(expense.gasFee) },
    { label: "日当", value: yen(expense.dailyAllowance) },
    { label: "宿泊費", value: yen(expense.accommodationFee) },
    { label: "昼食代", value: yen(expense.lunchFee) },
    { label: "夕食代", value: yen(expense.dinnerFee) },
    { label: "合計金額", value: `¥${expense.totalFeeLabel}` },
    { label: "申請日", value: expense.applyDate },
    { label: "出発日", value: expense.dateFrom },
    { label: "帰着日", value: expense.dateTo },
    { label: "精算日", value: expense.payDate },
  ]);
</script>

<PageHeader title="旅費精算 / 詳細">
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">戻る</Button>
    {#if expense.urls}
      <Button href={expense.urls.edit} icon="pencil">編集</Button>
      <Button href={expense.urls.pdf} external icon="file-earmark-pdf">PDF</Button>
    {/if}
  {/snippet}
</PageHeader>

<div class="max-w-2xl">
  <DetailList {rows} />
</div>
