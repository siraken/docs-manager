# CI とデプロイ

`.github/workflows/ci.yml` がテストと型チェックを回す。**デプロイのワークフローは無い。**

## ワークフロー

| ジョブ | 内容 |
| --- | --- |
| `php` | PHP 8.3 + `composer install` → `./vendor/bin/pest` |
| `frontend` | Node 22 + pnpm 11 → `tsc --noEmit` / `svelte-check` / `pnpm build` |

`pull_request` と `main` への push で走る。2 つのジョブは独立なので並列に動く。ローカルの `just check` と同じものを見ている（`just check` は build を含まない点だけ違う）。

- **`php artisan key:generate` が要る**。`.env.example` の `APP_KEY` は空で、暗号化クッキーのミドルウェアが鍵を要求する。DB は `phpunit.xml` が sqlite の `:memory:` を指定するので、`.env.example` の `DB_CONNECTION=mysql` は使われない
- **テスト前にアセットをビルドする必要は無い**。`tests/TestCase.php` が `withoutVite()` を呼ぶため
- **Node は 22 を指定する**。pnpm 11 が 22.13+、Vite 8 が 22.12+ を要求し、`@tailwindcss/oxide` と `rolldown` のネイティブバイナリは engines が合わないと黙ってスキップされて `Cannot find native binding` で落ちる
- **サードパーティの action は SHA で固定する**（`shivammathur/setup-php` / `pnpm/action-setup`）。バージョンはコメントで併記

## デプロイを作る場合

**デプロイ先のサーバーは廃止済み**で、いま復活させる先は無い。作り直す場合、旧 `deploy.yml` が抱えていた問題を引き継がないよう注意する。

- 旧デプロイは SSH 先で `git reset --hard origin/main` → `yarn install && yarn prod` を実行するだけで、**`composer install` を実行しなかった**
- **マイグレーションも自動実行されなかった**。旧 `runner` にあった `prod:migrate` は `ssh` 先で `migrate:fresh`（＝全テーブル削除）を走らせるものだったので、justfile には移していない
- テストとデプロイが 1 ファイルに同居していたため、デプロイ先が死んだときにテストのジョブごと失われた。**分けておくこと**
