<script lang="ts">
  import { untrack } from "svelte";
  import { Link, router } from "@inertiajs/svelte";

  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import Label from "../../components/ui/Label.svelte";
  import DeleteConfirm from "../../components/DeleteConfirm.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Select from "../../components/ui/Select.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { MasterOption, Option } from "../../lib/master-types";
  import type { CourseOption, Enrollment, EnrollmentFilter, LearningSummary } from "../../lib/learning-types";

  /**
   * 受講記録の一覧。
   *
   * ポイントは完了した受講だけを足す。受講中のぶんまで数えると
   * 「まだ終わっていないのに獲得済み」になってしまう。
   */
  type Props = {
    enrollments: Enrollment[];
    summary: LearningSummary;
    users: MasterOption[];
    courses: CourseOption[];
    statuses: Option[];
    filter: EnrollmentFilter;
    urls: { self: string; create: string; courses: string };
  };

  let { enrollments, summary, users, courses, statuses, filter, urls }: Props = $props();

  // 初期値をそのまま握るのが正しい（検索は preserveState で送るため再マウントされない）
  let userId = $state<number | string>(untrack(() => filter.userId ?? ""));
  let courseId = $state<number | string>(untrack(() => filter.courseId ?? ""));
  let status = $state(untrack(() => filter.status ?? ""));

  function search(event: SubmitEvent): void {
    event.preventDefault();

    router.get(
      urls.self,
      { user_id: userId, course_id: courseId, status },
      { preserveState: true, replace: true },
    );
  }

  let confirm = $state<DeleteConfirm<Enrollment> | undefined>();

  const badgeColor = (value: string): "green" | "brand" | "slate" =>
    value === "completed" ? "green" : value === "in_progress" ? "brand" : "slate";
</script>

<PageHeader title="研修">
  {#snippet description()}講座の受講状況を記録します{/snippet}
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">受講を記録する</Button>
    <Button href={urls.courses} icon="journal-bookmark">講座</Button>
  {/snippet}
</PageHeader>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
  <Card class="lg:col-span-2">
    <form onsubmit={search} class="grid gap-3 sm:grid-cols-4 sm:items-end">
      <div>
        <Label for="user_id">受講者</Label>
        <Select id="user_id" bind:value={userId}>
          <option value="">すべて</option>
          {#each users as user (user.id)}
            <option value={user.id}>{user.name}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="course_id">講座</Label>
        <Select id="course_id" bind:value={courseId}>
          <option value="">すべて</option>
          {#each courses as course (course.id)}
            <option value={course.id}>{course.name}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="status">状態</Label>
        <Select id="status" bind:value={status}>
          <option value="">すべて</option>
          {#each statuses as option (option.value)}
            <option value={option.value}>{option.label}</option>
          {/each}
        </Select>
      </div>

      <Button type="submit" variant="primary" icon="search">検索</Button>
    </form>
  </Card>

  <Card class="flex items-center justify-around gap-4">
    <div class="text-center">
      <p class="text-xs font-medium text-slate-500">完了</p>
      <p class="text-2xl font-semibold text-slate-900 tabular">
        {summary.completedCount}<span class="text-sm"> 件</span>
      </p>
      <p class="mt-0.5 text-xs text-slate-400">受講中 {summary.inProgressCount} 件</p>
    </div>
    <div class="text-center">
      <p class="text-xs font-medium text-slate-500">獲得ポイント</p>
      <p class="text-2xl font-semibold text-slate-900 tabular">
        {summary.earnedExpLabel}<span class="text-sm"> pt</span>
      </p>
      <p class="mt-0.5 text-xs text-slate-400">完了分のみ</p>
    </div>
  </Card>
</div>

{#if enrollments.length === 0}
  <EmptyState>該当する受講記録がありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">受講者</th>
      <th class="px-4 py-3">講座</th>
      <th class="px-4 py-3">状態</th>
      <th class="hidden px-4 py-3 sm:table-cell">開始日</th>
      <th class="hidden px-4 py-3 sm:table-cell">完了日</th>
      <th class="px-4 py-3 text-right">ポイント</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each enrollments as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600">{row.userName}</td>
        <td class="px-4 py-3 align-middle">
          {#if row.urls}
            <Link href={row.urls.edit} class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
              {row.courseTitle}
            </Link>
          {:else}
            <span class="font-medium text-slate-900">{row.courseTitle}</span>
          {/if}
        </td>
        <td class="px-4 py-3 align-middle"><Badge color={badgeColor(row.statusValue)}>{row.status}</Badge></td>
        <td class="hidden px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular sm:table-cell">
          {row.startedAtLabel}
        </td>
        <td class="hidden px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular sm:table-cell">
          {row.completedAtLabel}
        </td>
        <td class="px-4 py-3 text-right align-middle whitespace-nowrap tabular">
          {#if row.exp > 0}
            <span class="font-semibold text-slate-900">{row.exp} pt</span>
          {:else}
            <span class="text-slate-400">-</span>
          {/if}
        </td>
        <td class="px-4 py-3 text-right align-middle whitespace-nowrap">
          {#if row.urls}
            <Button href={row.urls.edit} size="sm" icon="pencil">編集</Button>
          {/if}
          <Button size="sm" variant="ghost" icon="trash" onclick={() => confirm?.ask(row)}>削除</Button>
        </td>
      </tr>
    {/each}
  </Table>
{/if}

<DeleteConfirm bind:this={confirm} title="受講記録の削除">
  {#snippet body(row: Enrollment)}{row.userName} さんの「{row.courseTitle}」の記録を削除します。取り消せません。{/snippet}
</DeleteConfirm>
