<script module lang="ts">
  // ログイン画面はナビを出さない。既定のレイアウトではなく Auth を使う
  export { default as layout } from "../../Layouts/Auth.svelte";
</script>

<script lang="ts">
  import { untrack } from "svelte";
  import { useForm } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import MetamaskSignIn from "../../components/MetamaskSignIn.svelte";
  import NfcSignIn from "../../components/NfcSignIn.svelte";

  type Props = { urls: { submit: string; nfc: string; metamask: string } };

  let { urls }: Props = $props();

  const form = untrack(() => useForm({ email: "", password: "" }));

  function submit(event: SubmitEvent): void {
    event.preventDefault();

    // ログインに失敗しても /login を描き直すだけなので Inertia 的には成功扱いで、
    // ページもコンポーネントの状態も残る。パスワードは自分で消す。
    // form.reset("password") ではダメで、useForm は onSuccess の中で
    // 「いまの値」を既定値に取り直すため、reset すると送信した値が戻ってくる。
    form.post(urls.submit, {
      onFinish: () => {
        form.password = "";
      },
    });
  }
</script>

<main class="w-full max-w-sm">
  <div class="mb-6 flex flex-col items-center gap-3">
    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-600 text-lg font-bold text-white">N</span>
    <h1 class="text-lg font-semibold tracking-tight text-slate-900">Novalumo Console</h1>
  </div>

  <Card class="space-y-4">
    <form method="post" onsubmit={submit} class="space-y-4">
      <div>
        <Label for="email">メールアドレス</Label>
        <Input
          type="email"
          id="email"
          autocomplete="username"
          placeholder="name@example.com"
          bind:value={form.email}
        />
      </div>

      <div>
        <Label for="password">パスワード</Label>
        <Input type="password" id="password" autocomplete="current-password" bind:value={form.password} />
      </div>

      <Button type="submit" variant="primary" size="lg" disabled={form.processing}>サインイン</Button>
    </form>

    <NfcSignIn url={urls.nfc} />
    <MetamaskSignIn url={urls.metamask} />
  </Card>

  <p class="mt-6 text-center text-xs text-slate-400">&copy; Novalumo</p>
</main>
