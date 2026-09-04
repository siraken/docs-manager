<script lang="ts">
  import { untrack } from "svelte";
  import { Link, router } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import Modal from "../../components/ui/Modal.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Select from "../../components/ui/Select.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { AccountOption, JournalEntry, JournalFilter } from "../../lib/accounting-types";

  /**
   * 仕訳帳の一覧。
   *
   * 参考にした移植元 (CakePHP 時代の posts) は摘要・その他の全文検索だけで、
   * 日付でも科目でも絞れず、合計も出していなかった。
   */
  type Props = {
    entries: JournalEntry[];
    total: number;
    totalLabel: string;
    accounts: AccountOption[];
    filter: JournalFilter;
    years: number[];
    urls: { self: string; create: string; trialBalance: string; accounts: string };
  };

  let { entries, total, totalLabel, accounts, filter, years, urls }: Props = $props();

  const MONTHS = Array.from({ length: 12 }, (_, index) => index + 1);

  // 絞り込みは GET なので useForm ではなく router.get で送る。
  // 初期値をそのまま握るのが正しい (検索は preserveState で送るため再マウントされず、
  // 入力欄には利用者が打った値が残る)
  let year = $state<number | string>(untrack(() => filter.year ?? ""));
  let month = $state<number | string>(untrack(() => filter.month ?? ""));
  let keyword = $state(untrack(() => filter.keyword ?? ""));
  let accountId = $state<number | string>(untrack(() => filter.accountId ?? ""));

  function search(event: SubmitEvent): void {
    event.preventDefault();

    router.get(
      urls.self,
      { year, month, q: keyword, account_id: accountId },
      { preserveState: true, replace: true },
    );
  }

  let confirming = $state(false);
  let target = $state<JournalEntry | null>(null);

  function askDelete(row: JournalEntry): void {
    target = row;
    confirming = true;
  }

  function confirmDelete(): void {
    if (target?.urls) {
      router.delete(target.urls.delete);
    }

    confirming = false;
  }
</script>

<PageHeader title="仕訳帳">
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">取引を追加</Button>
    <Button href={urls.trialBalance} icon="table">残高試算表</Button>
    <Button href={urls.accounts} icon="list-ul">勘定科目</Button>
  {/snippet}
</PageHeader>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
  <Card class="lg:col-span-2">
    <form onsubmit={search} class="grid gap-3 sm:grid-cols-5 sm:items-end">
      <div>
        <Label for="year">年</Label>
        <Select id="year" bind:value={year}>
          <option value="">すべて</option>
          {#each years as option (option)}
            <option value={option}>{option}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="month">月</Label>
        <Select id="month" bind:value={month}>
          <option value="">すべて</option>
          {#each MONTHS as option (option)}
            <option value={option}>{option}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="account_id">勘定科目</Label>
        <Select id="account_id" bind:value={accountId}>
          <option value="">すべて</option>
          {#each accounts as account (account.id)}
            <option value={account.id}>{account.name}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="q">摘要 / その他</Label>
        <Input id="q" bind:value={keyword} placeholder="キーワード" />
      </div>

      <Button type="submit" variant="primary" icon="search">検索</Button>
    </form>
  </Card>

  <Card class="flex flex-col items-center justify-center">
    <p class="text-xs font-medium text-slate-500">取引金額の合計</p>
    <p class="text-2xl font-semibold text-slate-900 tabular">￥{totalLabel}</p>
    <p class="mt-1 text-xs text-slate-400">{entries.length} 件</p>
  </Card>
</div>

{#if entries.length === 0}
  <EmptyState>該当する仕訳がありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">日付</th>
      <th class="px-4 py-3">借方</th>
      <th class="px-4 py-3">貸方</th>
      <th class="px-4 py-3 text-right">金額</th>
      <th class="px-4 py-3">摘要</th>
      <th class="hidden px-4 py-3 lg:table-cell">その他</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each entries as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-600 tabular">{row.dateLabel}</td>
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-900">{row.debitAccountName}</td>
        <td class="px-4 py-3 align-middle whitespace-nowrap text-slate-900">{row.creditAccountName}</td>
        <td class="px-4 py-3 text-right align-middle font-semibold whitespace-nowrap text-slate-900 tabular">
          ￥{row.amountLabel}
        </td>
        <td class="px-4 py-3 align-middle">
          {#if row.urls}
            <Link href={row.urls.edit} class="text-brand-700 hover:text-brand-900 hover:underline">
              {row.description}
            </Link>
          {:else}
            {row.description}
          {/if}
        </td>
        <td class="hidden px-4 py-3 align-middle text-slate-600 lg:table-cell">{row.note ?? "-"}</td>
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

<Modal bind:open={confirming} title="仕訳の削除">
  <p class="text-sm text-slate-600">
    {target?.dateLabel}「{target?.description}」（￥{target?.amountLabel}）を削除します。取り消せません。
  </p>

  {#snippet footer()}
    <Button onclick={() => (confirming = false)}>キャンセル</Button>
    <Button variant="danger" icon="trash" onclick={confirmDelete}>削除する</Button>
  {/snippet}
</Modal>
