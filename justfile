# Docs Manager の開発コマンド。
#
# php / composer / node / pnpm は nix devShell (flake.nix) のものを使う。
# direnv 済みならディレクトリに入るだけ、そうでなければ `nix develop`。
#
# **Docker (Laravel Sail) が要るのはアプリサーバーと MySQL だけ**。
# テストは sqlite の :memory: で回るので、Sail を起動しなくても通る。
# Sail 越しに動かしたいものには sail- を頭に付けたレシピを用意してある。

set positional-arguments

sail := "./vendor/bin/sail"

# レシピの一覧を出す
default:
    @just --list

# --- セットアップ -----------------------------------------------------------

# .env を作って APP_KEY を発行する
[group('setup')]
init:
    cp .env.example .env
    php artisan key:generate

# 依存をまとめて入れる
[group('setup')]
install:
    composer install
    pnpm install

# vendor が無く devShell も使えないときの退避路 (Docker 内の composer で入れる)
[group('setup')]
composer-init:
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd)":/var/www/html \
        -w /var/www/html \
        laravelsail/php83-composer:latest \
        composer install --ignore-platform-reqs

# --- ホストで動かすもの (devShell) ------------------------------------------

# Pest を回す (sqlite :memory: なので Docker は不要)
[group('dev')]
test *args:
    @./vendor/bin/pest "$@"

# 型チェック。現在 0 件で通るので、増やしたまま放置しないこと
[group('dev')]
tsc:
    @./node_modules/.bin/tsc --noEmit

# テストと型チェックをまとめて
[group('dev')]
check: test tsc

# Vite の開発サーバ (HMR)
[group('dev')]
dev:
    @pnpm dev

# 本番ビルド
[group('dev')]
build:
    @pnpm build

[group('dev')]
artisan *args:
    @php artisan "$@"

[group('dev')]
composer *args:
    @composer "$@"

[group('dev')]
pnpm *args:
    @pnpm "$@"

# --- Docker (Laravel Sail) --------------------------------------------------

# アプリサーバーと MySQL を起動する
[group('sail')]
up:
    @{{ sail }} up

[group('sail')]
down:
    @{{ sail }} down

# Sail のイメージをビルドし直す
[group('sail')]
sail-build *args:
    @{{ sail }} build "$@"

[group('sail')]
sail-artisan *args:
    @{{ sail }} artisan "$@"

[group('sail')]
sail-composer *args:
    @{{ sail }} composer "$@"

[group('sail')]
sail-pnpm *args:
    @{{ sail }} pnpm "$@"

[group('sail')]
sail-npx *args:
    @{{ sail }} npx "$@"

# MySQL でテストを回したいとき (既定の test は sqlite)
[group('sail')]
sail-test *args:
    @{{ sail }} test "$@"

# MySQL を作り直す (migrate:reset → migrate → db:seed)
[group('sail')]
db-reset:
    {{ sail }} artisan migrate:reset
    {{ sail }} artisan migrate
    {{ sail }} artisan db:seed
