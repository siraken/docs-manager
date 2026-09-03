<script lang="ts">
  import { untrack } from "svelte";
  import { useForm } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import FormErrors from "../../components/ui/FormErrors.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import { scanNfcSerialNumber } from "../../lib/nfc-scan";
  import type { User } from "../../lib/master-types";

  type Props = {
    user: User | null;
    urls: { submit: string; back: string };
  };

  let { user, urls }: Props = $props();

  const isNew = $derived(user === null);

  const form = untrack(() =>
    useForm({
      name: user?.name ?? "",
      email: user?.email ?? "",
      password: "",
      nfc_serial_number: user?.nfcSerialNumber ?? "",
      nfc_pin: "",
      wallet_address: user?.walletAddress ?? "",
    }),
  );

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit} autocomplete="off">
  <PageHeader title={isNew ? "ユーザーの新規登録" : "ユーザーの編集"}>
    {#snippet actions()}
      <Button href={urls.back} icon="arrow-left">戻る</Button>

      <!-- 保存前のユーザーには 2FA を設定できないので、編集時だけ出す。
           移行前はこれを無条件に出していたため、新規登録画面では
           route('users.2fa', ['id' => null]) の組み立てに失敗して 500 になっていた。 -->
      {#if user?.urls}
        <Button href={user.urls.twoFactor} icon="shield-lock">2FA</Button>
      {/if}

      <Button type="submit" variant="primary" icon="check-lg" disabled={form.processing}>保存する</Button>
    {/snippet}
  </PageHeader>

  <div class="max-w-2xl">
    <FormErrors errors={form.errors} />

    <Card class="space-y-5">
      <div>
        <Label for="name" required>名前</Label>
        <Input id="name" bind:value={form.name} required />
      </div>

      <div>
        <Label for="email" required>メールアドレス</Label>
        <Input type="email" id="email" bind:value={form.email} required />
      </div>

      <div>
        <Label for="password" required={isNew}>パスワード</Label>
        <Input type="password" id="password" autocomplete="new-password" bind:value={form.password} />
        {#if !isNew}
          <p class="mt-1 text-xs text-slate-400">空のままにすると現在のパスワードを変更しません</p>
        {/if}
      </div>

      {#if !isNew}
        <div>
          <Label for="nfc_number">NFC Card</Label>
          <div class="flex">
            <Button
              class="rounded-r-none"
              onclick={() => scanNfcSerialNumber((serialNumber) => (form.nfc_serial_number = serialNumber))}
            >
              Scan
            </Button>
            <Input id="nfc_number" class="rounded-l-none" bind:value={form.nfc_serial_number} />
          </div>
          <p class="mt-1 text-xs text-slate-400">空にすると NFC ログインを無効にします</p>
        </div>

        <div>
          <Label for="nfc_pin">NFC PIN</Label>
          <Input type="password" id="nfc_pin" autocomplete="new-password" bind:value={form.nfc_pin} />
          <p class="mt-1 text-xs text-slate-400">空のままにすると現在の PIN を変更しません</p>
        </div>

        <div>
          <Label for="wallet_address">ウォレットアドレス</Label>
          <Input id="wallet_address" placeholder="0x..." bind:value={form.wallet_address} />
          <p class="mt-1 text-xs text-slate-400">0x で始まる 42 文字</p>
        </div>
      {/if}
    </Card>
  </div>
</form>
