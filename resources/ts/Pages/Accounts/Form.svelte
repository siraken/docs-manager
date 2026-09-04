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
  import Toggle from "../../components/ui/Toggle.svelte";
  import type { Option } from "../../lib/master-types";
  import type { Account } from "../../lib/accounting-types";

  type Props = {
    account: Account | null;
    types: Option[];
    urls: { submit: string; back: string };
  };

  let { account, types, urls }: Props = $props();

  const isNew = $derived(account === null);

  const form = untrack(() =>
    useForm({
      code: account?.code ?? "",
      name: account?.name ?? "",
      type: account?.typeValue ?? types[0]?.value ?? "asset",
      is_active: account?.isActive ?? true,
      note: account?.note ?? "",
    }),
  );

  /** 残高がどちらの側に立つかは区分で決まる。選んだ結果をその場で見せる */
  const normalBalance = $derived(
    form.type === "asset" || form.type === "expense" ? "借方" : "貸方",
  );

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit} autocomplete="off">
  <PageHeader title={isNew ? "勘定科目の追加" : "勘定科目の編集"}>
    {#snippet actions()}
      <Button href={urls.back} icon="arrow-left">戻る</Button>
      <Button type="submit" variant="primary" icon="check-lg" disabled={form.processing}>保存する</Button>
    {/snippet}
  </PageHeader>

  <div class="max-w-2xl">
    <FormErrors errors={form.errors} />

    <Card class="space-y-5">
      <div class="grid gap-4 sm:grid-cols-3">
        <div>
          <Label for="code">科目コード</Label>
          <Input id="code" bind:value={form.code} placeholder="101" />
          <p class="mt-1.5 text-xs text-slate-500">試算表の並び順に使います</p>
        </div>

        <div class="sm:col-span-2">
          <Label for="name" required>科目名</Label>
          <Input id="name" bind:value={form.name} placeholder="現金" required />
        </div>
      </div>

      <div>
        <Label for="type" required>区分</Label>
        <Select id="type" bind:value={form.type}>
          {#each types as type (type.value)}
            <option value={type.value}>{type.label}</option>
          {/each}
        </Select>
        <p class="mt-1.5 text-xs text-slate-500">
          この区分では残高が<span class="font-medium text-slate-700">{normalBalance}</span>に立ちます
        </p>
      </div>

      <div>
        <Toggle bind:checked={form.is_active} label="仕訳フォームの選択肢に出す" />
        <p class="mt-1.5 text-xs text-slate-500">
          使わなくなった科目はここを外して選択肢から隠します（過去の仕訳は残ります）
        </p>
      </div>

      <div>
        <Label for="note">備考</Label>
        <Textarea id="note" rows={3} bind:value={form.note} />
      </div>
    </Card>
  </div>
</form>
