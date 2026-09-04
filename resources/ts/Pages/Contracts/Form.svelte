<script lang="ts">
  import { untrack } from "svelte";
  import { useForm } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import FormErrors from "../../components/ui/FormErrors.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Select from "../../components/ui/Select.svelte";
  import Textarea from "../../components/ui/Textarea.svelte";
  import type { Contract, MasterOption } from "../../lib/master-types";

  /**
   * 契約の登録・編集フォーム。
   *
   * 移植前の編集画面はコントローラが渡さない変数 ($name / $pid など) を
   * 参照していたうえ、form の action も id 抜きの route('contracts.update') で
   * 組もうとしており、開いた時点で 500 になっていた。
   * 取引先は文字列ではなく customers から選ぶ。
   */
  type Props = {
    contract: Contract | null;
    customers: MasterOption[];
    urls: { submit: string; back: string };
  };

  let { contract, customers, urls }: Props = $props();

  const isNew = $derived(contract === null);

  const form = untrack(() =>
    useForm({
      name: contract?.name ?? "",
      contract_no: contract?.contractNo ?? "",
      customer_id: contract?.customerId ?? "",
      start_date: contract?.startDate ?? "",
      end_date: contract?.endDate ?? "",
      description: contract?.description ?? "",
    }),
  );

  function save(): void {
    form.post(urls.submit);
  }
</script>

<PageHeader title={isNew ? "契約の新規登録" : "契約の編集"}>
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">戻る</Button>
    <Button variant="primary" icon="check-lg" disabled={form.processing} onclick={save}>保存する</Button>
  {/snippet}
</PageHeader>

<div class="max-w-2xl">
  <FormErrors errors={form.errors} />

  <Card class="space-y-5">
    <div>
      <Label for="name" required>契約名</Label>
      <Input id="name" bind:value={form.name} required />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <Label for="contract_no">契約番号</Label>
        <Input id="contract_no" bind:value={form.contract_no} placeholder="CT-001" />
      </div>

      <div>
        <Label for="customer_id">取引先</Label>
        <Select id="customer_id" bind:value={form.customer_id}>
          <option value="">選択してください</option>
          {#each customers as customer (customer.id)}
            <option value={customer.id}>{customer.name}</option>
          {/each}
        </Select>
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <Label for="start_date">契約開始日</Label>
        <Input type="date" id="start_date" bind:value={form.start_date} />
      </div>
      <div>
        <Label for="end_date">契約終了日</Label>
        <Input type="date" id="end_date" bind:value={form.end_date} />
      </div>
    </div>

    <div>
      <Label for="description">備考</Label>
      <Textarea id="description" rows={4} bind:value={form.description} />
    </div>
  </Card>
</div>
