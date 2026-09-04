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
  import type { AssignmentOption, Submission } from "../../lib/learning-types";

  /** 提出物。「誰がどの課題を出して、どう評価されたか」。 */
  type Props = {
    submissions: Submission[];
    assignments: AssignmentOption[];
    users: MasterOption[];
    statuses: Option[];
    filter: { userId: number | null; assignmentId: number | null; status: string | null };
    urls: { self: string; create: string; assignments: string };
  };

  let { submissions, assignments, users, statuses, filter, urls }: Props = $props();

  let userId = $state<number | string>(untrack(() => filter.userId ?? ""));
  let assignmentId = $state<number | string>(untrack(() => filter.assignmentId ?? ""));
  let status = $state(untrack(() => filter.status ?? ""));

  function search(event: SubmitEvent): void {
    event.preventDefault();

    router.get(
      urls.self,
      { user_id: userId, assignment_id: assignmentId, status },
      { preserveState: true, replace: true },
    );
  }

  let confirm = $state<DeleteConfirm<Submission> | undefined>();

  const badgeColor = (value: string): "green" | "brand" | "amber" | "slate" =>
    value === "approved" ? "green" : value === "returned" ? "amber" : value === "submitted" ? "brand" : "slate";
</script>

<PageHeader title="提出物">
  {#snippet description()}課題の提出状況と評価を記録します{/snippet}
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">提出を記録する</Button>
    <Button href={urls.assignments} icon="journal-check">課題</Button>
  {/snippet}
</PageHeader>

<Card class="mb-6">
  <form onsubmit={search} class="grid gap-3 sm:grid-cols-4 sm:items-end">
    <div>
      <Label for="user_id">提出者</Label>
      <Select id="user_id" bind:value={userId}>
        <option value="">すべて</option>
        {#each users as user (user.id)}
          <option value={user.id}>{user.name}</option>
        {/each}
      </Select>
    </div>

    <div>
      <Label for="assignment_id">課題</Label>
      <Select id="assignment_id" bind:value={assignmentId}>
        <option value="">すべて</option>
        {#each assignments as assignment (assignment.id)}
          <option value={assignment.id}>{assignment.name}</option>
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

{#if submissions.length === 0}
  <EmptyState>該当する提出物がありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">提出者</th>
      <th class="px-4 py-3">課題</th>
      <th class="hidden px-4 py-3 md:table-cell">講座</th>
      <th class="px-4 py-3">状態</th>
      <th class="px-4 py-3">提出日</th>
      <th class="hidden px-4 py-3 lg:table-cell">講評</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each submissions as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600">{row.userName}</td>
        <td class="px-4 py-3 align-middle">
          {#if row.urls}
            <Link href={row.urls.edit} class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
              {row.assignmentTitle}
            </Link>
          {:else}
            <span class="font-medium text-slate-900">{row.assignmentTitle}</span>
          {/if}
        </td>
        <td class="hidden px-4 py-3 align-middle text-slate-600 md:table-cell">{row.courseTitle || "-"}</td>
        <td class="px-4 py-3 align-middle"><Badge color={badgeColor(row.statusValue)}>{row.status}</Badge></td>
        <td class="px-4 py-3 align-middle whitespace-nowrap">
          <span class="text-slate-600 tabular">{row.submittedAtLabel}</span>
          {#if row.isLate}
            <Badge color="red" class="ml-1">遅延</Badge>
          {/if}
        </td>
        <td class="hidden max-w-64 truncate px-4 py-3 align-middle text-slate-600 lg:table-cell">
          {row.feedback ?? "-"}
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

<DeleteConfirm bind:this={confirm} title="提出物の削除">
  {#snippet body(row: Submission)}{row.userName} さんの「{row.assignmentTitle}」の提出物を削除します。取り消せません。{/snippet}
</DeleteConfirm>
