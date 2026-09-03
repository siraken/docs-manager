<script lang="ts">
  import { router } from "@inertiajs/svelte";

  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { User } from "../../lib/master-types";

  type Props = { users: User[]; urls: { create: string } };

  let { users, urls }: Props = $props();

  /**
   * 削除。
   *
   * 移行前は onclick で未定義の JS 関数 (deleteItem) を呼んでいて、
   * サーバー側の受け口も無かった。DELETE のリクエストに置き換えている。
   */
  function remove(user: User): void {
    if (!user.urls) {
      return;
    }

    if (!globalThis.confirm(`${user.name} を削除します。よろしいですか？`)) {
      return;
    }

    router.delete(user.urls.delete);
  }
</script>

<PageHeader title="ユーザー管理">
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">ユーザーの新規登録</Button>
  {/snippet}
</PageHeader>

{#if users.length === 0}
  <EmptyState>ユーザーがまだ登録されていません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">名前</th>
      <th class="px-4 py-3">メールアドレス</th>
      <th class="px-4 py-3">2FA</th>
      <th class="px-4 py-3">変更日</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each users as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle font-medium text-slate-900">{row.name}</td>
        <td class="px-4 py-3 align-middle text-slate-600">{row.email}</td>
        <td class="px-4 py-3 align-middle">
          {#if row.hasTwoFactor}
            <Badge color="brand">有効</Badge>
          {:else}
            <Badge>未設定</Badge>
          {/if}
        </td>
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{row.updatedAt}</td>
        <td class="px-4 py-3 text-right align-middle">
          {#if row.urls}
            <div class="flex justify-end gap-2">
              <Button href={row.urls.edit} size="sm" icon="pencil">編集</Button>
              <Button size="sm" variant="danger" icon="trash" onclick={() => remove(row)}>削除</Button>
            </div>
          {/if}
        </td>
      </tr>
    {/each}
  </Table>
{/if}
