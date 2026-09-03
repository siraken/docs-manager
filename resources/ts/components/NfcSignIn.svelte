<script lang="ts">
  import { router } from "@inertiajs/svelte";

  import Button from "./ui/Button.svelte";
  import Input from "./ui/Input.svelte";
  import Label from "./ui/Label.svelte";

  /**
   * NFC カードでのサインイン。Web NFC が使えるブラウザでだけ出す。
   *
   * 移行前は lib/nfc-auth.ts が window の load で document.body に div を
   * 組み立てていた。Bootstrap を剥がしたあとも form-control / btn-primary を
   * 付けたままだったので、実際には素のフォーム部品が並んでいただけだった。
   *
   * TODO: PIN は平文で保存・照合されている (Domain\User\ValueObject\NfcCredential)。
   */
  type Props = { url: string };

  let { url }: Props = $props();

  let supported = $state(false);
  let pin = $state("");
  let status = $state("");
  let scanning = $state(false);

  $effect(() => {
    supported = "NDEFReader" in window;
  });

  async function scanAndSignIn(): Promise<void> {
    scanning = true;
    status = "カードをかざしてください…";

    try {
      const reader = new NDEFReader();
      await reader.scan();

      reader.addEventListener("error", () => {
        status = "読み取りに失敗しました";
        scanning = false;
      });

      reader.addEventListener("reading", (event) => {
        const serialNumber = String((event as NDEFReadingEvent).serialNumber);
        status = `読み取りました: ${serialNumber}`;

        // 認証は通常のフォーム送信と同じ扱いにする。成功時のリダイレクトも
        // フラッシュメッセージも、サーバーの応答をそのまま Inertia が処理する。
        router.post(url, { serialNumber, pin }, { onFinish: () => (scanning = false) });
      });
    } catch (error) {
      status = String(error);
      scanning = false;
    }
  }
</script>

{#if supported}
  <div class="mt-4 space-y-3 border-t border-slate-200 pt-4">
    <Label for="nfc-pin">NFC でサインイン</Label>
    <Input type="password" id="nfc-pin" placeholder="PIN" autocomplete="off" bind:value={pin} />
    <Button variant="secondary" size="lg" icon="credit-card-2-front" disabled={scanning} onclick={scanAndSignIn}>
      カードをスキャンしてサインイン
    </Button>
    {#if status}
      <p class="text-xs text-slate-500">{status}</p>
    {/if}
  </div>
{/if}
