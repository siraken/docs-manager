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

  type Props = {
    defaults: { relId: string; dateFrom: string; dateTo: string; applyDate: string };
    urls: { submit: string; back: string };
  };

  let { defaults, urls }: Props = $props();

  // 申請者は共有プロパティのログインユーザー名で埋める (Blade では session('name'))
  const authName = (page.props.auth as { name: string } | null)?.name ?? "";

  const form = untrack(() =>
    useForm({
      rel_id: defaults.relId,
      dir: "",
      purpose: "",
      price: "",
      date_from: defaults.dateFrom,
      date_to: defaults.dateTo,
      apply_date: defaults.applyDate,
      apply_person: authName,
    }),
  );

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit}>
  <PageHeader title="出張申請 / 申請">
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

      <div>
        <Label for="price">金額</Label>
        <Input type="number" id="price" bind:value={form.price} />
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

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <Label for="apply_date" required>申請日</Label>
          <Input type="date" id="apply_date" required bind:value={form.apply_date} />
        </div>
        <div>
          <Label for="apply_person" required>申請者氏名</Label>
          <Input id="apply_person" required bind:value={form.apply_person} />
        </div>
      </div>
    </Card>
  </div>
</form>
