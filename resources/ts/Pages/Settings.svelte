<script lang="ts">
  import { untrack } from "svelte";
  import { useForm } from "@inertiajs/svelte";

  import Button from "../components/ui/Button.svelte";
  import Card from "../components/ui/Card.svelte";
  import FormErrors from "../components/ui/FormErrors.svelte";
  import Input from "../components/ui/Input.svelte";
  import Label from "../components/ui/Label.svelte";
  import PageHeader from "../components/ui/PageHeader.svelte";
  import Textarea from "../components/ui/Textarea.svelte";

  /**
   * 自社情報。発注書 PDF の差出人欄とロゴ・社印に使われる。
   *
   * 移行前は「設定項目はまだありません」と出すだけの画面で、settings テーブルは
   * 存在するのに参照するコードが無く、PDF の差出人欄はコントローラに直書きだった。
   *
   * TODO: 画像のアップロード UI は未実装。いまはファイルを resources/img に
   *       置いたうえでパスを手入力する必要がある。
   */
  type Profile = {
    name: string;
    zipcode: string;
    address: string;
    representative: string;
    telNo: string;
    logoUrl: string;
    companyStampUrl: string;
    representativeStampUrl: string;
    applyStampUrl: string;
    isDefault: boolean;
  };

  type Props = { profile: Profile; urls: { submit: string } };

  let { profile, urls }: Props = $props();

  const STAMPS = [
    { key: "logo_url", label: "ロゴ" },
    { key: "com_stamp_url", label: "社印" },
    { key: "rep_stamp_url", label: "代表者印" },
    { key: "apply_stamp_url", label: "承認印" },
  ] as const;

  const form = untrack(() =>
    useForm({
      name: profile.name,
      zipcode: profile.zipcode,
      tel_no: profile.telNo,
      address: profile.address,
      rep: profile.representative,
      logo_url: profile.logoUrl,
      com_stamp_url: profile.companyStampUrl,
      rep_stamp_url: profile.representativeStampUrl,
      apply_stamp_url: profile.applyStampUrl,
    }),
  );

  function submit(event: SubmitEvent): void {
    event.preventDefault();
    form.post(urls.submit);
  }
</script>

<form method="post" onsubmit={submit} autocomplete="off">
  <PageHeader title="Settings">
    {#snippet description()}発注書 PDF の差出人欄に使う自社情報です。{/snippet}
    {#snippet actions()}
      <Button type="submit" variant="primary" icon="check-lg" disabled={form.processing}>保存する</Button>
    {/snippet}
  </PageHeader>

  <div class="max-w-2xl">
    <FormErrors errors={form.errors} />

    {#if profile.isDefault}
      <div class="mb-6 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-200">
        まだ保存されていません。表示しているのは移行前に PDF へ直書きされていた既定値です。
      </div>
    {/if}

    <Card class="space-y-5">
      <div>
        <Label for="name" required>会社名</Label>
        <Input id="name" bind:value={form.name} required />
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <Label for="zipcode">郵便番号</Label>
          <Input id="zipcode" bind:value={form.zipcode} />
        </div>
        <div>
          <Label for="tel_no">電話番号</Label>
          <Input id="tel_no" bind:value={form.tel_no} />
        </div>
      </div>

      <div>
        <Label for="address">住所</Label>
        <Textarea id="address" rows={3} bind:value={form.address} />
        <p class="mt-1 text-xs text-slate-400">改行するとPDFでも行が分かれます</p>
      </div>

      <div>
        <Label for="rep">代表者名</Label>
        <Input id="rep" bind:value={form.rep} />
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        {#each STAMPS as stamp (stamp.key)}
          <div>
            <Label for={stamp.key}>{stamp.label}</Label>
            <Input id={stamp.key} bind:value={form[stamp.key]} />
          </div>
        {/each}
      </div>

      <p class="text-xs text-slate-400">
        画像は resources/ からの相対パスで指定します（例: img/Logo.png）。
      </p>
    </Card>
  </div>
</form>
