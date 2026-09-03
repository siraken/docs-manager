<script lang="ts">
  import Button from "../../components/ui/Button.svelte";
  import DetailList from "../../components/ui/DetailList.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import type { Travel } from "../../lib/travel-types";

  type Props = { trip: Travel; urls: { back: string } };

  let { trip, urls }: Props = $props();

  const rows = $derived([
    { label: "管理ID", value: trip.relId },
    { label: "出張先", value: trip.destination },
    { label: "目的", value: trip.purpose },
    { label: "申請者", value: trip.applyPerson },
    { label: "金額", value: `¥${trip.priceLabel}` },
    { label: "出発日", value: trip.dateFrom },
    { label: "帰着日", value: trip.dateTo },
    { label: "申請日", value: trip.applyDate },
  ]);
</script>

<PageHeader title="出張申請 / 詳細">
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">戻る</Button>
    {#if trip.urls}
      <Button href={trip.urls.pdf} external icon="file-earmark-pdf">PDF</Button>
    {/if}
  {/snippet}
</PageHeader>

<div class="max-w-2xl">
  <DetailList {rows} />
</div>
