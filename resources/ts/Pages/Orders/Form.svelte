<script lang="ts">
  import { untrack } from "svelte";
  import { useForm } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Select from "../../components/ui/Select.svelte";
  import Textarea from "../../components/ui/Textarea.svelte";
  import OrderLines from "../../components/OrderLines.svelte";
  import { blankOrderLine, toDraft, type OrderLineDraft } from "../../lib/order-line";
  import type { CustomerOption, OrderDetail, TaxOption } from "../../lib/order-types";

  type Props = {
    order: OrderDetail | null;
    customers: CustomerOption[];
    taxOptions: TaxOption[];
    urls: { submit: string; back: string };
  };

  let { order, customers, taxOptions, urls }: Props = $props();

  const isNew = $derived(order === null);
  const today = new Date().toISOString().slice(0, 10);

  /** 既存の明細 + 空行。最低 5 行は出す (Blade 版と同じ) */
  function initialLines(): OrderLineDraft[] {
    const existing = (order?.lines ?? []).map(toDraft);
    const blanks = Math.max(0, 5 - existing.length);

    return [...existing, ...Array.from({ length: blanks }, blankOrderLine)];
  }

  /**
   * フォームの初期値。order を読むのはここ 1 回だけで、以後は編集中の値を持つ。
   * untrack で包んでいるのは「props が変わってもフォームを作り直さない」という
   * 意図を明示するため (包まないと Svelte が state_referenced_locally を警告する)。
   */
  const form = untrack(() =>
    useForm({
      customer_id: order?.customerId ?? "",
      responsible: order?.responsible ?? "",
      honor_title: order?.honorTitle ?? "御中",
      issued_date: order?.issuedDate ?? today,
      exp_date: order?.expDate ?? "",
      order_no: order?.orderNo ?? `${today.replace(/-/g, "")}-xxx`,
      title: order?.title ?? "",
      remarks: order?.remarks ?? "",
      lines: initialLines(),
    }),
  );

  const errors = $derived(Object.values(form.errors as Record<string, string>).filter(Boolean));

  function submit(event: SubmitEvent): void {
    event.preventDefault();

    // サーバーは item_name[] / qty[] ... という並列の配列で受け取る。
    // 金額 (price[]) は送らない —— 保存される金額はサーバーが計算し直すため。
    form
      .transform(({ lines, ...rest }) => ({
        ...rest,
        item_name: lines.map((line) => line.itemName),
        qty: lines.map((line) => line.quantity),
        unit: lines.map((line) => line.unit),
        cost: lines.map((line) => line.unitCost),
        tax: lines.map((line) => line.taxId),
      }))
      .post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit} autocomplete="off">
  <PageHeader title={isNew ? "発注書の作成" : "発注書の編集"}>
    {#snippet actions()}
      <Button href={urls.back} icon="arrow-left">戻る</Button>
      <Button type="submit" variant="primary" icon="check-lg" disabled={form.processing}>保存する</Button>
    {/snippet}
  </PageHeader>

  {#if errors.length > 0}
    <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
      <ul class="list-inside list-disc space-y-1">
        {#each errors as error, index (index)}
          <li>{error}</li>
        {/each}
      </ul>
    </div>
  {/if}

  <div class="space-y-6">
    <Card class="space-y-5">
      <!-- 取引先 -->
      <div>
        <Label required>取引先</Label>
        <div class="grid gap-2 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)]">
          <Select bind:value={form.customer_id}>
            <option value="">選択してください</option>
            {#each customers as customer (customer.id)}
              <option value={customer.id}>{customer.name}</option>
            {/each}
          </Select>
          <Input placeholder="担当者名" bind:value={form.responsible} />
          <Input placeholder="御中 / 様" bind:value={form.honor_title} />
        </div>
      </div>

      <!-- 日付 -->
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <Label for="issued_date" required>発行日</Label>
          <Input type="date" id="issued_date" bind:value={form.issued_date} required />
        </div>
        <div>
          <Label for="exp_date">有効期限</Label>
          <Input type="date" id="exp_date" bind:value={form.exp_date} />
        </div>
      </div>

      <!-- 番号・件名 -->
      <div class="grid gap-4 sm:grid-cols-4">
        <div>
          <Label required>発注書番号</Label>
          <Input bind:value={form.order_no} required />
        </div>
        <div class="sm:col-span-3">
          <Label>件名</Label>
          <Input maxlength={70} bind:value={form.title} />
          <p class="mt-1 text-xs text-slate-400">70文字まで</p>
        </div>
      </div>
    </Card>

    <OrderLines bind:lines={form.lines} {taxOptions} />

    <Card>
      <Label for="remarks">備考</Label>
      <Textarea id="remarks" rows={6} maxlength={1000} bind:value={form.remarks} />
      <p class="mt-1 text-xs text-slate-400">1000文字まで</p>
    </Card>
  </div>
</form>
