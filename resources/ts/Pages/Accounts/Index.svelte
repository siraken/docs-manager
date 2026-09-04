<script lang="ts">
  import { Link, router } from "@inertiajs/svelte";

  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import Modal from "../../components/ui/Modal.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { Account } from "../../lib/accounting-types";

  /**
   * 勘定科目マスタ。仕訳帳が科目を選ぶ元になる。
   *
   * 参考にした移植元は科目を自由入力の文字列で持っていたため、
   * マスタそのものが存在しなかった。
   */
  type Props = { accounts: Account[]; urls: { create: string; journal: string } };

  let { accounts, urls }: Props = $props();

  let confirming = $state(false);
  let target = $state<Account | null>(null);

  function askDelete(row: Account): void {
    target = row;
    confirming = true;
  }

  function confirmDelete(): void {
    if (target?.urls) {
      // 仕訳から参照されている科目はサーバー側で弾かれ、
      // 理由がフラッシュメッセージで返る
      router.delete(target.urls.delete);
    }

    confirming = false;
  }

  const badgeColor = (type: string): "brand" | "amber" | "green" | "slate" =>
    type === "asset" ? "brand" : type === "liability" || type === "equity" ? "amber" : type === "revenue" ? "green" : "slate";
</script>

<PageHeader title="勘定科目">
  {#snippet description()}仕訳帳で選べる科目の一覧です{/snippet}
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">科目を追加</Button>
    <Button href={urls.journal} icon="journal-text">仕訳帳</Button>
  {/snippet}
</PageHeader>

{#if accounts.length === 0}
  <EmptyState>勘定科目がまだありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">コード</th>
      <th class="px-4 py-3">科目名</th>
      <th class="px-4 py-3">区分</th>
      <th class="hidden px-4 py-3 sm:table-cell">残高</th>
      <th class="px-4 py-3">状態</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each accounts as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle text-slate-600 tabular">{row.code ?? "-"}</td>
        <td class="px-4 py-3 align-middle">
          {#if row.urls}
            <Link href={row.urls.edit} class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
              {row.name}
            </Link>
          {:else}
            <span class="font-medium text-slate-900">{row.name}</span>
          {/if}
        </td>
        <td class="px-4 py-3 align-middle"><Badge color={badgeColor(row.typeValue)}>{row.type}</Badge></td>
        <td class="hidden px-4 py-3 align-middle text-slate-600 sm:table-cell">{row.normalBalance}</td>
        <td class="px-4 py-3 align-middle">
          {#if row.isActive}
            <Badge color="green">有効</Badge>
          {:else}
            <Badge>無効</Badge>
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

<Modal bind:open={confirming} title="勘定科目の削除">
  <p class="text-sm text-slate-600">「{target?.name}」を削除します。取り消せません。</p>
  <p class="mt-2 text-xs text-slate-500">
    仕訳で使われている科目は削除できません。使わなくなった科目は編集画面で「無効にする」と選択肢から外せます。
  </p>

  {#snippet footer()}
    <Button onclick={() => (confirming = false)}>キャンセル</Button>
    <Button variant="danger" icon="trash" onclick={confirmDelete}>削除する</Button>
  {/snippet}
</Modal>
