<script lang="ts">
  import { untrack } from "svelte";
  import { useForm } from "@inertiajs/svelte";

  import Button from "./ui/Button.svelte";
  import Input from "./ui/Input.svelte";
  import Label from "./ui/Label.svelte";
  import Modal from "./ui/Modal.svelte";
  import Toggle from "./ui/Toggle.svelte";

  /**
   * CSV 取り込みのダイアログ。出張申請と旅費精算の一覧が使う。
   *
   * useForm は値に File が混ざると自動で multipart/form-data に切り替えるので、
   * enctype を自分で指定する必要は無い。
   */
  type Props = { open?: boolean; url: string };

  let { open = $bindable(false), url }: Props = $props();

  const form = untrack(() => useForm({ csv: null as File | null, header: true }));

  function pick(event: Event): void {
    form.csv = (event.currentTarget as HTMLInputElement).files?.[0] ?? null;
  }

  function submit(event: SubmitEvent): void {
    event.preventDefault();

    form.post(url, {
      onSuccess: () => {
        open = false;
        form.reset();
      },
    });
  }
</script>

<Modal bind:open title="CSV取り込み">
  <form id="csv-import-form" method="post" onsubmit={submit} class="space-y-4">
    <div>
      <Label for="csv">CSVを選択してください</Label>
      <Input type="file" id="csv" accept=".csv" required onchange={pick} />
      {#if form.errors.csv}
        <p class="mt-1 text-xs text-red-700">{form.errors.csv}</p>
      {/if}
    </div>

    <Toggle label="ヘッダーあり" bind:checked={form.header} />
  </form>

  {#snippet footer()}
    <p class="text-xs text-slate-500">1行目を見出しとして読み飛ばすかを選べます</p>
    <Button type="submit" form="csv-import-form" variant="primary" disabled={form.processing}>取り込み</Button>
  {/snippet}
</Modal>
