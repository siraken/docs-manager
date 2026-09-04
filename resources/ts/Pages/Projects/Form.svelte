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
  import ProjectsModal from "../../components/ProjectsModal.svelte";
  import Textarea from "../../components/ui/Textarea.svelte";
  import type { MasterOption, Option, Project } from "../../lib/master-types";

  type Props = {
    project: Project | null;
    statuses: Option<number>[];
    customers: MasterOption[];
    urls: { submit: string; back: string };
  };

  let { project, statuses, customers, urls }: Props = $props();

  const isNew = $derived(project === null);

  /** 受注前確認モーダルの開閉。確認が済むまで保存しない */
  let confirming = $state(false);

  const form = untrack(() =>
    useForm({
      name: project?.name ?? "",
      client_id: project?.clientId ?? "",
      jira_key: project?.jiraKey ?? "",
      status: project?.statusValue ?? statuses[0]?.value ?? 0,
      start_date: project?.startDate ?? "",
      end_date: project?.endDate ?? "",
      payment_date: project?.paymentDate ?? "",
      price: project?.price ?? "",
      // 移行前はサーバー側だけが description を扱い、フォームに入力欄が無かった
      description: project?.description ?? "",
    }),
  );

  function save(): void {
    form.post(urls.submit);
  }
</script>

<PageHeader title={isNew ? "案件の新規登録" : "案件の編集"}>
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">戻る</Button>
    <Button variant="primary" icon="check-lg" disabled={form.processing} onclick={() => (confirming = true)}>
      保存する
    </Button>
  {/snippet}
</PageHeader>

<div class="max-w-2xl">
  <FormErrors errors={form.errors} />

  <Card class="space-y-5">
    <div>
      <Label for="name" required>案件名</Label>
      <Input id="name" bind:value={form.name} required />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <Label for="client_id">取引先</Label>
        <!-- 移行前は顧客 ID を手で打ち込ませていた -->
        <Select id="client_id" bind:value={form.client_id}>
          <option value="">選択してください</option>
          {#each customers as customer (customer.id)}
            <option value={customer.id}>{customer.name}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="jira_key">Jira のキー</Label>
        <Input id="jira_key" bind:value={form.jira_key} placeholder="NOVA-123" />
        <p class="mt-1.5 text-xs text-slate-500">一覧からこのキーで Jira を開けます</p>
      </div>
    </div>

    <div>
      <Label for="status">状態</Label>
      <Select id="status" bind:value={form.status}>
        {#each statuses as status (status.value)}
          <option value={status.value}>{status.label}</option>
        {/each}
      </Select>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
      <div>
        <Label for="start_date">開始日</Label>
        <Input type="date" id="start_date" bind:value={form.start_date} />
      </div>
      <div>
        <Label for="end_date">終了日</Label>
        <Input type="date" id="end_date" bind:value={form.end_date} />
      </div>
      <div>
        <Label for="payment_date">支払日</Label>
        <Input type="date" id="payment_date" bind:value={form.payment_date} />
      </div>
    </div>

    <div>
      <Label for="price">請求金額</Label>
      <div class="flex">
        <span
          class="inline-flex items-center rounded-l-lg bg-slate-100 px-3 text-sm text-slate-500 ring-1 ring-inset ring-slate-300"
        >
          ¥
        </span>
        <Input type="number" id="price" bind:value={form.price} class="rounded-l-none" />
      </div>
    </div>

    <div>
      <Label for="description">備考</Label>
      <Textarea id="description" rows={4} bind:value={form.description} />
    </div>
  </Card>
</div>

<ProjectsModal bind:open={confirming} onconfirm={save} />
