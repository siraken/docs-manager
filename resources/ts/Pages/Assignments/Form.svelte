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
  import type { Assignment, CourseOption } from "../../lib/learning-types";

  type Props = {
    assignment: Assignment | null;
    courses: CourseOption[];
    urls: { submit: string; back: string };
  };

  let { assignment, courses, urls }: Props = $props();

  const isNew = $derived(assignment === null);

  const form = untrack(() =>
    useForm({
      course_id: assignment?.courseId ?? "",
      title: assignment?.title ?? "",
      description: assignment?.description ?? "",
      due_on: assignment?.dueOn ?? "",
    }),
  );

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit} autocomplete="off">
  <PageHeader title={isNew ? "課題の追加" : "課題の編集"}>
    {#snippet actions()}
      <Button href={urls.back} icon="arrow-left">戻る</Button>
      <Button type="submit" variant="primary" icon="check-lg" disabled={form.processing}>保存する</Button>
    {/snippet}
  </PageHeader>

  <div class="max-w-2xl">
    <FormErrors errors={form.errors} />

    <Card class="space-y-5">
      <div>
        <Label for="course_id" required>講座</Label>
        <Select id="course_id" bind:value={form.course_id} required>
          <option value="">選択してください</option>
          {#each courses as course (course.id)}
            <option value={course.id}>{course.name}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="title" required>課題名</Label>
        <Input id="title" bind:value={form.title} placeholder="演習 1: ルーティングを書く" required />
      </div>

      <div>
        <Label for="description">内容</Label>
        <Textarea id="description" rows={4} bind:value={form.description} />
      </div>

      <div>
        <Label for="due_on">提出期限</Label>
        <Input type="date" id="due_on" bind:value={form.due_on} />
        <p class="mt-1.5 text-xs text-slate-500">空にすると期限なしの課題になります</p>
      </div>
    </Card>
  </div>
</form>
