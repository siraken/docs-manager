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
  import type { CourseOption, Enrollment } from "../../lib/learning-types";

  /**
   * 受講記録の登録・編集。
   *
   * 状態に合わせて日付欄の出し方を変える。未受講なら日付は不要、
   * 受講中なら開始日だけ、完了なら完了日が要る。保存時にサーバー側でも
   * 同じ規則で整えられる（Enrollment エンティティ）。
   */
  type Props = {
    enrollment: Enrollment | null;
    /** 新規のときだけ届く。日付と受講者の既定値はサーバーが決める */
    defaults: { date: string; userId: number | null } | null;
    users: MasterOption[];
    courses: CourseOption[];
    statuses: Option[];
    urls: { submit: string; back: string };
  };

  let { enrollment, defaults, users, courses, statuses, urls }: Props = $props();

  const isNew = $derived(enrollment === null);

  const form = untrack(() =>
    useForm({
      user_id: enrollment?.userId ?? defaults?.userId ?? "",
      course_id: enrollment?.courseId ?? "",
      status: enrollment?.statusValue ?? statuses[0]?.value ?? "not_started",
      started_at: enrollment?.startedAt ?? "",
      completed_at: enrollment?.completedAt ?? "",
      note: enrollment?.note ?? "",
    }),
  );

  const needsStartedAt = $derived(form.status === "in_progress" || form.status === "completed");
  const needsCompletedAt = $derived(form.status === "completed");

  /** 選んだ講座のポイント。完了にしたときに入る値を先に見せる */
  const selectedExp = $derived(
    courses.find((c) => String(c.id) === String(form.course_id))?.exp ?? 0,
  );

  function save(): void {
    form.post(urls.submit);
  }
</script>

<PageHeader title={isNew ? "受講の記録" : "受講記録の編集"}>
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
        <Label for="user_id" required>受講者</Label>
        <Select id="user_id" bind:value={form.user_id} required>
          <option value="">選択してください</option>
          {#each users as user (user.id)}
            <option value={user.id}>{user.name}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="course_id" required>講座</Label>
        <Select id="course_id" bind:value={form.course_id} required>
          <option value="">選択してください</option>
          {#each courses as course (course.id)}
            <option value={course.id}>{course.name}（{course.exp} pt）</option>
          {/each}
        </Select>
        {#if selectedExp > 0}
          <p class="mt-1.5 text-xs text-slate-500">完了にすると {selectedExp} pt が加算されます</p>
        {/if}
      </div>
    </div>

    <div>
      <Label for="status" required>状態</Label>
      <Select id="status" bind:value={form.status}>
        {#each statuses as option (option.value)}
          <option value={option.value}>{option.label}</option>
        {/each}
      </Select>
    </div>

    {#if needsStartedAt}
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <Label for="started_at">受講開始日</Label>
          <Input type="date" id="started_at" bind:value={form.started_at} />
        </div>

        {#if needsCompletedAt}
          <div>
            <Label for="completed_at" required>完了日</Label>
            <Input type="date" id="completed_at" bind:value={form.completed_at} required />
          </div>
        {/if}
      </div>
    {:else}
      <p class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600 ring-1 ring-slate-200">
        未受講の記録は日付を持ちません。受講中か完了に変えると日付を入力できます。
      </p>
    {/if}

    <div>
      <Label for="note">メモ</Label>
      <Textarea id="note" rows={3} bind:value={form.note} />
    </div>
  </Card>
</div>
