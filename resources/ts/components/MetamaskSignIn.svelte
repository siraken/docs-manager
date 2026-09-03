<script lang="ts">
  import { router } from "@inertiajs/svelte";

  import Button from "./ui/Button.svelte";

  /**
   * MetaMask でのサインイン。ウォレットが注入されているときだけ出す。
   *
   * 移行前は lib/metamask-auth.ts が window の load で無条件に
   * confirm("Are you sure you want to sign in with MetaMask?") を出していた。
   * ログイン画面を開くたびにダイアログが出る作りだったので、明示的なボタンに
   * 変えてある (押したときだけウォレットに接続を要求する)。
   *
   * TODO: ウォレットアドレスは公開情報なので、いまは「知っていれば入れる」認証。
   *       nonce への署名と ecrecover による検証に置き換える必要がある
   *       (Domain\User\ValueObject\WalletAddress)。
   */
  type Props = { url: string };

  let { url }: Props = $props();

  let available = $state(false);
  let error = $state("");
  let connecting = $state(false);

  $effect(() => {
    available = "ethereum" in window;
  });

  async function signIn(): Promise<void> {
    connecting = true;
    error = "";

    try {
      const accounts: string[] = await window.ethereum.request({ method: "eth_requestAccounts" });
      const address = accounts[0];

      if (!address) {
        error = "アカウントが選択されませんでした";

        return;
      }

      if (window.ethereum.chainId !== "0x1") {
        error = "メインネットに切り替えてください";

        return;
      }

      router.post(url, { address }, { onFinish: () => (connecting = false) });

      return;
    } catch (cause) {
      error = String(cause);
    }

    connecting = false;
  }
</script>

{#if available}
  <div class="mt-4 space-y-3 border-t border-slate-200 pt-4">
    <Button variant="secondary" size="lg" icon="wallet2" disabled={connecting} onclick={signIn}>
      MetaMask でサインイン
    </Button>
    {#if error}
      <p class="text-xs text-red-700">{error}</p>
    {/if}
  </div>
{/if}
