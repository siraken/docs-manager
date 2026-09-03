<script lang="ts">
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";

  /**
   * Lumo Academy の問い合わせ一覧。
   *
   * 移行前は AcademyController::index() が空 (何も返さない) で、ルートだけが
   * 登録されていた。登録用の register も保存していなかったため、そもそも
   * 表示するデータが存在しなかった。
   */
  type Inquiry = { id: number; name: string; email: string; inquiry: string };
  type Props = { inquiries: Inquiry[] };

  let { inquiries }: Props = $props();
</script>

<PageHeader title="Lumo Academy">
  {#snippet description()}フォームから届いた問い合わせです。{/snippet}
</PageHeader>

{#if inquiries.length === 0}
  <EmptyState>問い合わせはまだありません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">お名前</th>
      <th class="px-4 py-3">メールアドレス</th>
      <th class="px-4 py-3">内容</th>
    {/snippet}

    {#each inquiries as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-top font-medium whitespace-nowrap text-slate-900">{row.name}</td>
        <td class="px-4 py-3 align-top whitespace-nowrap text-slate-600">{row.email}</td>
        <td class="px-4 py-3 align-top text-sm whitespace-pre-line text-slate-700">{row.inquiry}</td>
      </tr>
    {/each}
  </Table>
{/if}
