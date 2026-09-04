# 開発環境とコマンド

ツールチェーンの入れ方、`just` のレシピ、DB を立てずに画面を見る方法。テストの書き方は [テスト](testing.md) を参照。

## 開発環境 (nix flake)

`flake.nix` + `.envrc` (`use flake`) で、Sail と同じバージョンのツールがホストに入る。direnv 済みならディレクトリに入るだけ、そうでなければ `nix develop`。

| ツール | バージョン |
| --- | --- |
| php | 8.3.33 |
| composer | 2.10.2 |
| node | 22.23.2 |
| pnpm | 11.22.0 |
| just | 1.58.0 |

すべて `nixpkgs-unstable` の単一 input から取っている。

**input が 1 つに戻っている経緯**: Laravel 10 までは PHP 8.1 が要件で、`php81` が unstable では EOL 扱いで評価が throw されるため `nixos-22.11` を別 input として pin していた。Laravel 11 で要件が PHP 8.2+ になり、unstable の `php82` で満たせるようになったのでその input を削除した。

Node 22 なのは、pnpm 11 が Node 22.13+ を、Vite 8 が Node 20.19+ / 22.12+ を要求するため（`nodejs_18` / `nodejs_20` は unstable では EOL 扱いで引けない）。

devShell が担うのはホスト側ツールチェーンのみ。**アプリの実行と MySQL は従来通り Sail (Docker)**。`shellHook` で `vendor/bin` と `node_modules/.bin` に PATH を通してある。

php83 はデフォルトで `gd` / `pdo_mysql` / `pdo_sqlite` / `mbstring` / `iconv` / `curl` / `zip` / `bcmath` / `exif` が有効で、追加設定なしで TCPDF の PDF 生成・freee API の cURL・sqlite でのテストまで動く。

この devShell がある場合、Docker 越しに composer を回す `just composer-init` は不要で、`composer install`（または `just install`）を直接叩ける。

## 開発コマンド

**`just`**（レシピは `justfile`）。引数なしで叩くとレシピ一覧が出る。以前は `runner` という bash スクリプトだったが、just に置き換えて削除した。

**Docker (Sail) が要るのはアプリサーバーと MySQL だけ**。テストもビルドも型チェックも devShell のツールでホストのまま回る。Sail 越しに動かしたいものには `sail-` を頭に付けたレシピを用意してある。

```bash
# ホストで動く (devShell)
just test [args]       # ./vendor/bin/pest
just tsc               # tsc --noEmit (.ts)
just svelte-check      # svelte-check (.svelte)
just check             # test → tsc → svelte-check
just dev               # pnpm dev (HMR)
just build             # pnpm build
just artisan <cmd>     # php artisan
just composer <cmd>
just pnpm <cmd>

# Docker (Laravel Sail)
just up                # sail up
just down              # sail down
just sail-build [args]
just sail-artisan <cmd>
just sail-composer <cmd>
just sail-pnpm <cmd>
just sail-npx <cmd>
just sail-test [args]  # MySQL でテストを回したいとき
just db-reset          # migrate:reset → migrate → db:seed

# セットアップ
just init              # .env 作成 + APP_KEY 発行
just install           # composer install + pnpm install
just composer-init     # devShell が使えないときの退避路 (Docker 内の composer)
```

**`just dev` は Vite しか起動しない**。アセット配信と HMR だけなので、画面を見るには別のターミナルで `just up`（Sail のアプリ + MySQL）が要る。開くのは **`http://localhost`**（`docker-compose.yml` の `APP_PORT` 既定値 80）であって、Vite が起動時に表示する `http://localhost:5173` ではない。後者はアセット用で、ルートを開いても何も返さない。

```bash
# ターミナル 1
just up            # アプリ + MySQL (http://localhost)
# ターミナル 2
just dev           # Vite の HMR
```

`just dev` を動かすと `public/hot` が作られ、`app.blade.php` の `@vite` がアセットの参照先を Vite の dev サーバーに切り替える。ページのオリジン（`http://localhost`）と Vite のオリジン（`http://127.0.0.1:5173`）は別になるが、`laravel-vite-plugin` が CORS を通すので問題なく読める。**Vite を止めたら `public/hot` が消えることを確認すること**（残っているとビルド成果物ではなく止まった dev サーバーを見にいくため、画面が真っ白になる）。

`set positional-arguments` を使い、レシピ側では `"$@"` で受けている。そのため `just artisan make:model "My Model"` のように**空白を含む引数もそのまま渡せる**（旧 `runner` は `ARGS=${@:2}` を単語分割される形で展開していたため、空白入りの引数が分裂した）。

**`prod:migrate` は移していない**。`ssh` 先で `migrate:fresh`（＝全テーブル削除）を走らせるうえ、接続先のサーバーは廃止済みだった（[CI とデプロイ](ci.md) を参照）。

## ローカルでの動作確認 (sqlite)

MySQL を立てずに画面を確認したいときは sqlite に向けられる。ただし **`php artisan serve` は任意の環境変数を子プロセスに渡さない**（`ServeCommand` の passthrough 許可リストにあるものだけ）。`DB_CONNECTION=sqlite artisan serve` は効かず `.env` の MySQL を見にいくので、PHP 内蔵サーバーを直接叩くこと。

```bash
php -S 127.0.0.1:8123 -t public <ルーターPHP>   # DB_CONNECTION / DB_DATABASE を env で渡す
```

**ルーター PHP を省いて `public/index.php` を直接渡してはいけない**。ルータースクリプトを指定すると静的ファイルもそれを通るため、ビルド済みの JS/CSS まで Laravel に流れ、`login` ミドルウェアが 302 を返す。HTML は普通に返るので curl での確認は通ってしまい、**ブラウザで開くと画面が真っ白**になる。ルーターは実ファイルがあるパスで `false` を返して内蔵サーバーに配信させること。

```php
$root = __DIR__ . '/public';
$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($path !== '/' && is_file($root . $path)) {
    return false;   // 静的ファイルは内蔵サーバーに任せる
}

require_once $root . '/index.php';
```

`config:cache` していなければ、実プロセスの環境変数は `.env` より優先される（Laravel は `Dotenv::createImmutable` を使う）。

## PHP バージョンの注意

**PHP 8.3**（`composer.json` は `^8.3`、nix devShell は 8.3.33）。

Sail のイメージは **`vendor/laravel/sail/runtimes/8.3` を直接参照**している（`docker-compose.yml` の `context` と `image` がどちらも 8.3）。以前はリポジトリ内の `docker/{7.4,8.0,8.1}` に Sail のコピーを抱えていたが、Ubuntu 21.10（EOL）ベースで独自カスタマイズも無かったため、Laravel 11 化の際に削除して公式の runtime に委譲した。PHP を上げるときは `docker-compose.yml` の `context` と `image` のバージョンを変えるだけでよい（vendor 内には 8.0〜8.5 が揃っている）。

`vendor/` は gitignore されているので、`sail up` の前に `composer install` が必要。

PHP 8.1 で **PDO SQLite が integer / float を native type で返すようになった**（7.4 までは文字列）。テストは sqlite、本番は MySQL なので、型に依存するコードは両者で挙動が変わりうる。実際 `ProjectController::index()` のステータス表示はこの影響を受けている（後述）。
