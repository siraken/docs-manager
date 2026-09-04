<script lang="ts">
  import { untrack } from "svelte";
  import { useForm } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import FormErrors from "../../components/ui/FormErrors.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Textarea from "../../components/ui/Textarea.svelte";
  import Toggle from "../../components/ui/Toggle.svelte";
  import type { Customer } from "../../lib/master-types";

  type Props = {
    customer: Customer | null;
    urls: { submit: string; back: string };
  };

  let { customer, urls }: Props = $props();

  const isNew = $derived(customer === null);

  /** name はサーバーが受け取るキー。ラベルは Blade 版と同じ英語表記のまま */
  const FIELDS = [
    { key: "person", label: "Person in charge" },
    { key: "email", label: "Email" },
    { key: "phone", label: "Phone" },
    { key: "post_code", label: "Post code" },
    { key: "address", label: "Address" },
    { key: "city", label: "City" },
    { key: "state", label: "State" },
    { key: "country", label: "Country" },
  ] as const;

  // props を読むのはここ 1 回だけ。以後は編集中の値を持つ
  const form = untrack(() =>
    useForm({
      name: customer?.name ?? "",
      is_company: customer?.isCompany ?? false,
      // 発注書フォームの担当者欄の初期値になる
      person: customer?.person ?? "",
      email: customer?.email ?? "",
      phone: customer?.phone ?? "",
      post_code: customer?.postCode ?? "",
      address: customer?.address ?? "",
      city: customer?.city ?? "",
      state: customer?.state ?? "",
      country: customer?.country ?? "",
      note: customer?.note ?? "",
    }),
  );

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit} autocomplete="off">
  <PageHeader title={isNew ? "顧客の新規登録" : "顧客の編集"}>
    {#snippet actions()}
      <Button href={urls.back} icon="arrow-left">Back</Button>
      <Button type="submit" variant="primary" icon="check-lg" disabled={form.processing}>Save</Button>
    {/snippet}
  </PageHeader>

  <div class="max-w-2xl">
    <FormErrors errors={form.errors} />

    <Card class="space-y-5">
      <div>
        <Label for="name" required>Name</Label>
        <Input id="name" bind:value={form.name} required />
      </div>

      <Toggle label="This is a company" bind:checked={form.is_company} />

      {#each FIELDS as field (field.key)}
        <div>
          <Label for={field.key}>{field.label}</Label>
          <Input id={field.key} bind:value={form[field.key]} />
        </div>
      {/each}

      <div>
        <Label for="note">Note</Label>
        <Textarea id="note" bind:value={form.note} />
      </div>
    </Card>
  </div>
</form>
