<script lang="ts">
  import { untrack } from "svelte";
  import { page, useForm } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import FormErrors from "../../components/ui/FormErrors.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Textarea from "../../components/ui/Textarea.svelte";
  import type { TravelExpense } from "../../lib/travel-types";

  type Props = {
    expense: TravelExpense;
    isNew: boolean;
    urls: { submit: string; back: string };
  };

  let { expense, isNew, urls }: Props = $props();

  /** 費目。合計はここから導出するため入力欄を持たない */
  const FEES = [
    { key: "trans_fee", label: "交通費" },
    { key: "acm_fee", label: "宿泊費" },
    { key: "gas_fee", label: "ガソリン代" },
    { key: "lunch_fee", label: "昼食代" },
    { key: "dinner_fee", label: "夕食代" },
    { key: "daily_pay", label: "日当" },
  ] as const;

  const authName = (page.props.auth as { name: string } | null)?.name ?? "";

  const form = untrack(() =>
    useForm({
      rel_id: expense.relId,
      dir: expense.destination,
      purpose: expense.purpose,
      trans_fee: expense.transportationFee,
      acm_fee: expense.accommodationFee,
      gas_fee: expense.gasFee,
      lunch_fee: expense.lunchFee,
      dinner_fee: expense.dinnerFee,
      daily_pay: expense.dailyAllowance,
      apply_date: expense.applyDate,
      pay_date: expense.payDate,
      date_from: expense.dateFrom,
      date_to: expense.dateTo,
      apply_person: expense.applyPerson || authName,
    }),
  );

  /** 入力中の合計。保存される値はサーバーが費目から計算し直す */
  const total = $derived(
    FEES.reduce((sum, fee) => sum + (Number(form[fee.key]) || 0), 0),
  );

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit}>
  <PageHeader title={isNew ? "出張旅費 / 精算" : "出張旅費 / 精算の編集"}>
    {#snippet actions()}
      <Button href={urls.back} icon="arrow-left">戻る</Button>
      <Button type="submit" variant="primary" icon="check-lg" disabled={form.processing}>保存する</Button>
    {/snippet}
  </PageHeader>

  <div class="max-w-2xl">
    <FormErrors errors={form.errors} />

    <Card class="space-y-5">
      <div>
        <Label for="rel_id" required>管理ID</Label>
        <Input id="rel_id" bind:value={form.rel_id} required />
      </div>

      <div>
        <Label for="dir" required>出張先</Label>
        <Input id="dir" bind:value={form.dir} required />
      </div>

      <div>
        <Label for="purpose" required>目的</Label>
        <Textarea id="purpose" rows={6} required bind:value={form.purpose} />
      </div>

      <!-- 移行前はここに費目を足す TODO が残っていて、入力欄が無いまま
           PDF だけがこれらを印字していた (常に空欄になっていた)。 -->
      <div>
        <Label>費目</Label>
        <div class="grid gap-4 sm:grid-cols-3">
          {#each FEES as fee (fee.key)}
            <div>
              <Label for={fee.key}>{fee.label}</Label>
              <div class="flex">
                <span
                  class="inline-flex items-center rounded-l-lg bg-slate-100 px-3 text-sm text-slate-500 ring-1 ring-inset ring-slate-300"
                >
                  ¥
                </span>
                <Input type="number" min={0} id={fee.key} class="rounded-l-none" bind:value={form[fee.key]} />
              </div>
            </div>
          {/each}
        </div>
        <p class="mt-2 text-xs text-slate-400">
          合計 ¥{total.toLocaleString("ja-JP")} —— 保存時にサーバーが費目から計算し直します
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <Label for="apply_date" required>申請日</Label>
          <Input type="date" id="apply_date" required bind:value={form.apply_date} />
        </div>
        <div>
          <Label for="pay_date" required>精算日</Label>
          <Input type="date" id="pay_date" required bind:value={form.pay_date} />
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <Label for="date_from" required>出発日</Label>
          <Input type="date" id="date_from" required bind:value={form.date_from} />
        </div>
        <div>
          <Label for="date_to" required>帰着日</Label>
          <Input type="date" id="date_to" required bind:value={form.date_to} />
        </div>
      </div>

      <div>
        <Label for="apply_person" required>申請者氏名</Label>
        <Input id="apply_person" required bind:value={form.apply_person} />
      </div>
    </Card>
  </div>
</form>
