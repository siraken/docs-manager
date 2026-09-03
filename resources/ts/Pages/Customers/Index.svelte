<script lang="ts">
  import { Link } from "@inertiajs/svelte";

  import Badge from "../../components/ui/Badge.svelte";
  import Button from "../../components/ui/Button.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";
  import type { Customer } from "../../lib/master-types";

  type Props = { customers: Customer[]; urls: { create: string } };

  let { customers, urls }: Props = $props();
</script>

<PageHeader title="顧客管理">
  {#snippet actions()}
    <Button href={urls.create} variant="primary" icon="plus-lg">顧客の新規登録</Button>
  {/snippet}
</PageHeader>

{#if customers.length === 0}
  <EmptyState>顧客がまだ登録されていません</EmptyState>
{:else}
  <Table>
    {#snippet head()}
      <th class="px-4 py-3">Name</th>
      <th class="px-4 py-3">Address</th>
      <th class="px-4 py-3">Email</th>
      <th class="px-4 py-3"><span class="sr-only">操作</span></th>
    {/snippet}

    {#each customers as row (row.id)}
      <tr class="transition hover:bg-slate-50">
        <td class="px-4 py-3 align-middle">
          {#if row.urls}
            <Link href={row.urls.edit} class="font-medium text-brand-700 hover:text-brand-900 hover:underline">
              {row.name}
            </Link>
          {:else}
            <span class="font-medium text-slate-900">{row.name}</span>
          {/if}
          {#if row.isCompany}
            <Badge color="brand" class="ml-1.5">法人</Badge>
          {/if}
        </td>
        <td class="px-4 py-3 align-middle text-slate-600">{row.location}</td>
        <td class="px-4 py-3 align-middle text-slate-600">{row.email ?? ""}</td>
        <td class="px-4 py-3 text-right align-middle">
          {#if row.urls}
            <Button href={row.urls.edit} size="sm" icon="pencil">編集</Button>
          {/if}
        </td>
      </tr>
    {/each}
  </Table>
{/if}
