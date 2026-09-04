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
  import type { Course } from "../../lib/learning-types";

  type Props = { course: Course | null; urls: { submit: string; back: string } };

  let { course, urls }: Props = $props();

  const isNew = $derived(course === null);

  const form = untrack(() =>
    useForm({
      title: course?.title ?? "",
      description: course?.description ?? "",
      exp: course?.exp ?? 0,
      is_published: course?.isPublished ?? true,
    }),
  );

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit} autocomplete="off">
  <PageHeader title={isNew ? "講座の追加" : "講座の編集"}>
    {#snippet actions()}
      <Button href={urls.back} icon="arrow-left">戻る</Button>
      <Button type="submit" variant="primary" icon="check-lg" disabled={form.processing}>保存する</Button>
    {/snippet}
  </PageHeader>

  <div class="max-w-2xl">
    <FormErrors errors={form.errors} />

    <Card class="space-y-5">
      <div>
        <Label for="title" required>講座名</Label>
        <Input id="title" bind:value={form.title} placeholder="Laravel 入門" required />
      </div>

      <div>
        <Label for="description">説明</Label>
        <Textarea id="description" rows={4} bind:value={form.description} />
      </div>

      <div>
        <Label for="exp">獲得ポイント</Label>
        <div class="flex">
          <Input type="number" id="exp" min="0" step="1" bind:value={form.exp} class="rounded-r-none" />
          <span
            class="inline-flex items-center rounded-r-lg bg-slate-100 px-3 text-sm text-slate-500 ring-1 ring-inset ring-slate-300"
          >
            pt
          </span>
        </div>
        <p class="mt-1.5 text-xs text-slate-500">受講が「完了」になったときだけ加算されます</p>
      </div>

      <div>
        <Toggle bind:checked={form.is_published} label="受講記録の選択肢に出す" />
        <p class="mt-1.5 text-xs text-slate-500">
          外すと下書きになり、新しい受講記録から選べなくなります（既存の記録は残ります）
        </p>
      </div>
    </Card>
  </div>
</form>
