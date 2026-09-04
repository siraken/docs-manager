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
  import type { MasterOption, Option } from "../../lib/master-types";
  import type { AssignmentOption, Submission } from "../../lib/learning-types";

  /**
   * 提出物の登録・編集。
   *
   * 状態に合わせて入力欄の出し方を変える。未提出なら提出日も講評も不要、
   * 提出済みなら提出日だけ、差し戻し・合格なら講評も書ける。保存時に
   * サーバー側でも同じ規則で整えられる（Submission エンティティ）。
   */
  type Props = {
    submission: Submission | null;
    /** 新規のときだけ届く。日付と提出者の既定値はサーバーが決める */
    defaults: { date: string; userId: number | null } | null;
    assignments: AssignmentOption[];
    users: MasterOption[];
    statuses: Option[];
    urls: { submit: string; back: string };
  };

  let { submission, defaults, assignments, users, statuses, urls }: Props = $props();

  const isNew = $derived(submission === null);

  const form = untrack(() =>
    useForm({
      user_id: submission?.userId ?? defaults?.userId ?? "",
      assignment_id: submission?.assignmentId ?? "",
      status: submission?.statusValue ?? statuses[0]?.value ?? "not_submitted",
      submitted_at: submission?.submittedAt ?? "",
      body: submission?.body ?? "",
      feedback: submission?.feedback ?? "",
    }),
  );

  const needsSubmittedAt = $derived(form.status !== "not_submitted");
  const acceptsFeedback = $derived(form.status === "returned" || form.status === "approved");

  function save(): void {
    form.post(urls.submit);
  }
</script>

<PageHeader title={isNew ? "提出の記録" : "提出物の編集"}>
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">戻る</Button>
    <Button variant="primary" icon="check-lg" disabled={form.processing} onclick={save}>保存する</Button>
  {/snippet}
</PageHeader>

<div class="max-w-3xl">
  <FormErrors errors={form.errors} />

  <Card class="space-y-5">
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <Label for="user_id" required>提出者</Label>
        <Select id="user_id" bind:value={form.user_id} required>
          <option value="">選択してください</option>
          {#each users as user (user.id)}
            <option value={user.id}>{user.name}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="assignment_id" required>課題</Label>
        <Select id="assignment_id" bind:value={form.assignment_id} required>
          <option value="">選択してください</option>
          {#each assignments as assignment (assignment.id)}
            <option value={assignment.id}>
              {assignment.name}{assignment.course ? `（${assignment.course}）` : ""}
            </option>
          {/each}
        </Select>
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <Label for="status" required>状態</Label>
        <Select id="status" bind:value={form.status}>
          {#each statuses as option (option.value)}
            <option value={option.value}>{option.label}</option>
          {/each}
        </Select>
      </div>

      {#if needsSubmittedAt}
        <div>
          <Label for="submitted_at" required>提出日</Label>
          <Input type="date" id="submitted_at" bind:value={form.submitted_at} required />
        </div>
      {/if}
    </div>

    {#if !needsSubmittedAt}
      <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600 ring-1 ring-slate-200">
        未提出の記録は提出日も講評も持ちません。状態を変えると入力できます。
      </p>
    {/if}

    <div>
      <Label for="body">提出内容</Label>
      <Textarea id="body" rows={5} bind:value={form.body} placeholder="成果物へのリンクや回答を書きます" />
    </div>

    {#if acceptsFeedback}
      <div>
        <Label for="feedback">講評</Label>
        <Textarea id="feedback" rows={3} bind:value={form.feedback} />
      </div>
    {:else}
      <p class="text-xs text-slate-500">
        講評は「差し戻し」か「合格」にすると書けます（提出される前に評価はできません）。
      </p>
    {/if}
  </Card>
</div>
