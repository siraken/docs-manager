# Docs Manager

Novalumo 社内向けの業務管理ツール（発注書・出張申請・出張旅費精算・案件管理・顧客管理・勤務報告・契約管理）。Laravel 13 + Inertia + Svelte 5。

## セットアップ

```bash
nix develop        # direnv 済みならディレクトリに入るだけ
just init          # .env 作成 + APP_KEY 発行
just install       # composer install + pnpm install

just up            # ターミナル 1: アプリ + MySQL (http://localhost)
just dev           # ターミナル 2: Vite の HMR
```

詳しくは [docs/development.md](docs/development.md) を参照。

## ドキュメント

[docs/](docs/README.md) に種類ごとに置いてある。
