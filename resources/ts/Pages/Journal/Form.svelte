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
  import type { AccountOption, JournalEntry } from "../../lib/accounting-types";

  /**
   * 仕訳の登録・編集。
   *
   * **金額の入力欄は 1 つだけ。** 参考にした移植元は借方金額と貸方金額を
   * 別々に入力させていたが、単一仕訳なら両者は必ず一致する。2 つあると
   * 貸借がずれた帳簿を作れてしまう。
   */
  type Props = {
    entry: JournalEntry | null;
    /** 新規のときだけ届く。日付の既定値はサーバーが決める */
    defaults: { date: string } | null;
    accounts: AccountOption[];
    urls: { submit: string; back: string };
  };

  let { entry, defaults, accounts, urls }: Props = $props();

  const isNew = $derived(entry === null);

  const form = untrack(() =>
    useForm({
      date: entry?.date ?? defaults?.date ?? "",
      debit_account_id: entry?.debitAccountId ?? "",
      credit_account_id: entry?.creditAccountId ?? "",
      amount: entry?.amount ?? "",
      description: entry?.description ?? "",
      note: entry?.note ?? "",
    }),
  );

  /** 借方と貸方に同じ科目を選んでいないか。送信前に画面でも気付けるようにする */
  const sameAccount = $derived(
    form.debit_account_id !== "" && String(form.debit_account_id) === String(form.credit_account_id),
  );

  function save(): void {
    form.post(urls.submit);
  }
</script>

<PageHeader title={isNew ? "取引の追加" : "仕訳の編集"}>
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">戻る</Button>
    <Button variant="primary" icon="check-lg" disabled={form.processing || sameAccount} onclick={save}>
      保存する
    </Button>
  {/snippet}
</PageHeader>

<div class="max-w-3xl">
  <FormErrors errors={form.errors} />

  <Card class="space-y-5">
    <div class="grid gap-4 sm:grid-cols-3">
      <div>
        <Label for="date" required>日付</Label>
        <Input type="date" id="date" bind:value={form.date} required />
      </div>

      <div class="sm:col-span-2">
        <Label for="amount" required>金額</Label>
        <div class="flex">
          <span
            class="inline-flex items-center rounded-l-lg bg-slate-100 px-3 text-sm text-slate-500 ring-1 ring-inset ring-slate-300"
          >
            ¥
          </span>
          <Input type="number" id="amount" min="1" step="1" bind:value={form.amount} class="rounded-l-none" required />
        </div>
        <p class="mt-1.5 text-xs text-slate-500">借方と貸方で同じ金額が計上されます</p>
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <Label for="debit_account_id" required>借方科目</Label>
        <Select id="debit_account_id" bind:value={form.debit_account_id} required>
          <option value="">選択してください</option>
          {#each accounts as account (account.id)}
            <option value={account.id}>{account.name}（{account.type}）</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="credit_account_id" required>貸方科目</Label>
        <Select id="credit_account_id" bind:value={form.credit_account_id} required>
          <option value="">選択してください</option>
          {#each accounts as account (account.id)}
            <option value={account.id}>{account.name}（{account.type}）</option>
          {/each}
        </Select>
      </div>
    </div>

    {#if sameAccount}
      <p class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
        借方と貸方に同じ勘定科目は指定できません。
      </p>
    {/if}

    <div>
      <Label for="description" required>摘要</Label>
      <Input id="description" bind:value={form.description} placeholder="株式会社サンプル 入金" required />
    </div>

    <div>
      <Label for="note">その他</Label>
      <Textarea id="note" rows={3} bind:value={form.note} />
    </div>
  </Card>
</div>
