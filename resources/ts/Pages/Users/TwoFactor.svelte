<script lang="ts">
  import { untrack } from "svelte";
  import { useForm } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import FormErrors from "../../components/ui/FormErrors.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import type { User } from "../../lib/master-types";

  /**
   * 二段階認証の設定。
   *
   * シークレットはこの画面を開いたときに発行され、認証コードの検証が通るまで
   * ユーザーのレコードには書かれない (セッションに一時保管される)。
   *
   * TODO: QR コード画像は生成していない。下の URI を認証アプリに手入力するか、
   *       コピーして読み込ませる必要がある。画像を出すなら QR エンコーダを
   *       足して Infrastructure 層の実装として差し込むこと。
   */
  type Props = {
    user: User;
    setup: { secret: string; uri: string; alreadyEnabled: boolean };
    urls: { submit: string; back: string };
  };

  let { user, setup, urls }: Props = $props();

  const form = untrack(() => useForm({ code: "" }));

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit} autocomplete="off">
  <PageHeader title="二段階認証の設定">
    {#snippet description()}
      {user.name}（{user.email}）
    {/snippet}
    {#snippet actions()}
      <Button href={urls.back} icon="arrow-left">戻る</Button>
      <Button type="submit" variant="primary" icon="check-lg" disabled={form.processing}>有効にする</Button>
    {/snippet}
  </PageHeader>

  <div class="max-w-md">
    <FormErrors errors={form.errors} />

    <Card class="space-y-5">
      {#if setup.alreadyEnabled}
        <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800 ring-1 ring-amber-200">
          このユーザーは既に二段階認証が有効です。ここで登録し直すと、いま使っている認証アプリの登録は無効になります。
        </p>
      {/if}

      <div>
        <Label>セットアップ用のキー</Label>
        <p class="mt-1 font-mono text-sm break-all text-slate-900 select-all">{setup.secret}</p>
        <p class="mt-1 text-xs text-slate-400">認証アプリに手入力する場合はこのキーを使います</p>
      </div>

      <div>
        <Label>otpauth URI</Label>
        <p class="mt-1 font-mono text-xs break-all text-slate-500 select-all">{setup.uri}</p>
      </div>

      <div>
        <Label for="code" required>認証コード</Label>
        <Input
          id="code"
          inputmode="numeric"
          autocomplete="one-time-code"
          maxlength={6}
          placeholder="000000"
          required
          bind:value={form.code}
        />
        <p class="mt-1 text-xs text-slate-400">認証アプリに表示された 6 桁を入力してください</p>
      </div>
    </Card>
  </div>
</form>
