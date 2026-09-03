<script lang="ts">
  /**
   * 検証エラーの一覧。
   *
   * Inertia は失敗時のレスポンスで errors を共有プロパティとして返すので、
   * useForm の errors をそのまま渡せばよい。Blade 側で毎フォームに書いていた
   * $errors->any() のブロックの置き換え。
   */
  type Props = { errors?: Record<string, string>; class?: string };

  let { errors = {}, class: extraClass = "" }: Props = $props();

  const messages = $derived(Object.values(errors).filter(Boolean));
</script>

{#if messages.length > 0}
  <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200 {extraClass}">
    <ul class="list-inside list-disc space-y-1">
      {#each messages as message, index (index)}
        <li>{message}</li>
      {/each}
    </ul>
  </div>
{/if}
