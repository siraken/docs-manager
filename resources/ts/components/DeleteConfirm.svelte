<script lang="ts" generics="T extends { urls: { delete: string } | null }">
  import type { Snippet } from "svelte";
  import { router } from "@inertiajs/svelte";

  import Button from "./ui/Button.svelte";
  import Modal from "./ui/Modal.svelte";

  /**
   * 一覧から行を消すときの確認ダイアログ。
   *
   * 一覧が 8 画面あり、いずれも「confirming と target を持ち、
   * askDelete() で開いて router.delete する」という同じ形を手書きしていた。
   * それをここへ集約している。
   *
   * 使い方:
   *   let confirm = $state<DeleteConfirm<Course> | undefined>();
   *   <Button onclick={() => confirm?.ask(row)}>削除</Button>
   *   <DeleteConfirm bind:this={confirm} title="講座の削除">
   *     {#snippet body(row)}「{row.title}」を削除します{/snippet}
   *   </DeleteConfirm>
   *
   * 削除できない行 (urls が null) は ask() を呼んでも無視する。
   * サーバー側で弾かれる場合 (使用中の科目など) は理由がフラッシュで返る。
   */
  type Props = {
    title: string;
    /** 確認文。対象の行を受け取る */
    body: Snippet<[T]>;
    /** 補足の注意書き */
    note?: Snippet;
  };

  let { title, body, note }: Props = $props();

  let open = $state(false);
  let target = $state<T | null>(null);

  export function ask(row: T): void {
    target = row;
    open = true;
  }

  function confirm(): void {
    if (target?.urls) {
      router.delete(target.urls.delete);
    }

    open = false;
  }
</script>

<Modal bind:open {title}>
  {#if target}
    <p class="text-sm text-slate-600">{@render body(target)}</p>
  {/if}

  {#if note}
    <p class="mt-2 text-xs text-slate-500">{@render note()}</p>
  {/if}

  {#snippet footer()}
    <Button onclick={() => (open = false)}>キャンセル</Button>
    <Button variant="danger" icon="trash" onclick={confirm}>削除する</Button>
  {/snippet}
</Modal>
