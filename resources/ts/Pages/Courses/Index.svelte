<script lang="ts">
  import { Link, router } from "@inertiajs/svelte";

  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import Modal from "../../components/ui/Modal.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { Course } from "../../lib/learning-types";

  /** 講座（社内研修の教材）。受講記録が講座を選ぶ元になる。 */
  type Props = { courses: Course[]; urls: { create: string; enrollments: string } };

  let { courses, urls }: Props = $props();

  let confirming = $state(false);
  let target = $state<Course | null>(null);

  function askDelete(row: Course): void {
    target = row;
    confirming = true;
  }

  function confirmDelete(): void {
    if (target?.urls) {
      // 受講記録のある講座はサーバー側で弾かれ、理由がフラッシュで返る
      router.delete(target.urls.delete);
    }

    confirming = false;
  }
</script>

<PageHeader title="講座">
  {#snippet description()}受講記録で選べる講座の一覧です{/snippet}
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">講座を追加</Button>
    <Button href={urls.enrollments} icon="mortarboard">受講記録</Button>
  {/snippet}
</PageHeader>

{#if courses.length === 0}
  <EmptyState>講座がまだありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">講座名</th>
      <th class="hidden px-4 py-3 md:table-cell">説明</th>
      <th class="px-4 py-3 text-right">獲得ポイント</th>
      <th class="px-4 py-3">状態</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each courses as row (row.id)}
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
        <td class="hidden px-4 py-3 align-middle text-slate-600 md:table-cell">{row.description ?? "-"}</td>
        <td class="px-4 py-3 text-right align-middle font-semibold whitespace-nowrap text-slate-900 tabular">
          {row.expLabel} pt
        </td>
        <td class="px-4 py-3 align-middle">
          {#if row.isPublished}
            <Badge color="green">公開中</Badge>
          {:else}
            <Badge>下書き</Badge>
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

<Modal bind:open={confirming} title="講座の削除">
  <p class="text-sm text-slate-600">「{target?.title}」を削除します。取り消せません。</p>
  <p class="mt-2 text-xs text-slate-500">
    受講記録のある講座は削除できません。使わなくなった講座は編集画面で「公開しない」にすると選択肢から外せます。
  </p>

  {#snippet footer()}
    <Button onclick={() => (confirming = false)}>キャンセル</Button>
    <Button variant="danger" icon="trash" onclick={confirmDelete}>削除する</Button>
  {/snippet}
</Modal>
