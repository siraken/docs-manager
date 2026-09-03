<script lang="ts">
  import { untrack } from "svelte";
  import { router, useForm } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import EmptyState from "../../components/ui/EmptyState.svelte";
  import FormErrors from "../../components/ui/FormErrors.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Table from "../../components/ui/Table.svelte";

  /**
   * ファイルの受け渡し。
   *
   * アップロードは認証なしでも開ける (取引先に送ってもらう想定)。一覧は
   * ログイン中だけサーバーが返すので、files の有無ではなく canList で出し分ける。
   */
  type FileRow = { name: string; urls: { download: string; delete: string } };
  type Props = {
    files: FileRow[];
    canList: boolean;
    urls: { upload: string };
  };

  let { files, canList, urls }: Props = $props();

  const form = untrack(() => useForm({ name: "", email: "", file: null as File | null }));

  function pick(event: Event): void {
    form.file = (event.currentTarget as HTMLInputElement).files?.[0] ?? null;
  }

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.upload, { onSuccess: () => form.reset() });
  }

  function remove(file: FileRow): void {
    if (!globalThis.confirm(`${file.name} を削除します。よろしいですか？`)) {
      return;
    }

    router.delete(file.urls.delete);
  }
</script>

<PageHeader title="ファイル">
  {#snippet description()}ファイルをアップロードして共有します。{/snippet}
</PageHeader>

<div class="mb-6 max-w-2xl">
  <FormErrors errors={form.errors} />

  <Card>
    <form method="post" onsubmit={submit} class="space-y-5">
      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <Label for="name">お名前</Label>
          <Input id="name" bind:value={form.name} />
        </div>
        <div>
          <Label for="email">メールアドレス</Label>
          <Input type="email" id="email" bind:value={form.email} />
        </div>
      </div>

      <div>
        <Label for="file">ファイルを選択してください</Label>
        <Input type="file" id="file" onchange={pick} />
      </div>

      <Button type="submit" variant="primary" icon="upload" disabled={form.processing}>アップロード</Button>
    </form>
  </Card>
</div>

<!-- 一覧はログイン中のみ -->
{#if canList}
  {#if files.length === 0}
    <EmptyState>アップロードされたファイルはありません</EmptyState>
  {:else}
    <Table>
      {#snippet head()}
        <th class="px-4 py-3">ファイル名</th>
        <th class="px-4 py-3"><span class="sr-only">操作</span></th>
      {/snippet}

      {#each files as file (file.name)}
        <tr class="transition hover:bg-slate-50">
          <td class="px-4 py-3 align-middle font-medium break-all text-slate-900">{file.name}</td>
          <td class="px-4 py-3 text-right align-middle">
            <div class="flex justify-end gap-2">
              <!-- ダウンロードは Inertia を通さない (XHR ではファイルを受け取れない) -->
              <Button href={file.urls.download} external size="sm" icon="download">ダウンロード</Button>
              <Button size="sm" variant="danger" icon="trash" onclick={() => remove(file)}>削除</Button>
            </div>
          </td>
        </tr>
      {/each}
    </Table>
  {/if}
{/if}
