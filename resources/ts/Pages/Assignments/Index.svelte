<script lang="ts">
  import { Link, router } from "@inertiajs/svelte";

  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import Modal from "../../components/ui/Modal.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { Assignment } from "../../lib/learning-types";

  /** 課題（講座に紐づく提出物の題目）。提出物が課題を選ぶ元になる。 */
  type Props = { assignments: Assignment[]; urls: { create: string; submissions: string } };

  let { assignments, urls }: Props = $props();

  let confirming = $state(false);
  let target = $state<Assignment | null>(null);

  function askDelete(row: Assignment): void {
    target = row;
    confirming = true;
  }

  function confirmDelete(): void {
    if (target?.urls) {
      // 提出物のある課題はサーバー側で弾かれ、理由がフラッシュで返る
      router.delete(target.urls.delete);
    }

    confirming = false;
  }
</script>

<PageHeader title="課題">
  {#snippet description()}講座に紐づく提出物の題目です{/snippet}
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">課題を追加</Button>
    <Button href={urls.submissions} icon="inbox">提出物</Button>
  {/snippet}
</PageHeader>

{#if assignments.length === 0}
  <EmptyState>課題がまだありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">課題名</th>
      <th class="px-4 py-3">講座</th>
      <th class="hidden px-4 py-3 md:table-cell">内容</th>
      <th class="px-4 py-3">提出期限</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each assignments as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle">
          {#if row.urls}
            <Link href={row.urls.edit} class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
              {row.title}
            </Link>
          {:else}
            <span class="font-medium text-slate-900">{row.title}</span>
          {/if}
        </td>
        <td class="px-4 py-3 align-middle text-slate-600">{row.courseTitle}</td>
        <td class="hidden px-4 py-3 align-middle text-slate-600 md:table-cell">{row.description ?? "-"}</td>
        <td class="px-4 py-3 align-middle whitespace-nowrap">
          {#if row.isOverdue}
            <Badge color="red">{row.dueOnLabel}</Badge>
          {:else}
            <span class="text-slate-600 tabular">{row.dueOnLabel}</span>
          {/if}
        </td>
        <td class="px-4 py-3 text-right align-middle whitespace-nowrap">
          {#if row.urls}
            <Button href={row.urls.edit} size="sm" icon="pencil">編集</Button>
          {/if}
          <Button size="sm" variant="ghost" icon="trash" onclick={() => askDelete(row)}>削除</Button>
        </td>
      </tr>
    {/each}
  </Table>
{/if}

<Modal bind:open={confirming} title="課題の削除">
  <p class="text-sm text-slate-600">「{target?.title}」を削除します。取り消せません。</p>
  <p class="mt-2 text-xs text-slate-500">提出物のある課題は削除できません。</p>

  {#snippet footer()}
    <Button onclick={() => (confirming = false)}>キャンセル</Button>
    <Button variant="danger" icon="trash" onclick={confirmDelete}>削除する</Button>
  {/snippet}
</Modal>
