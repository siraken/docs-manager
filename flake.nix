{
  description = "docs-manager: Laravel が要求する PHP に揃えた開発ツールチェーン";

  inputs = {
    # mkShell と補助ツール用。現行 OS との親和性のため新しい方を使う。
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";

    # Laravel 10 が要求する PHP 8.1+ を満たす php81 (8.1.19) の供給元。
    # php81 は nixpkgs-unstable では EOL 扱いで評価が throw されるため
    # (unstable には php82 以降しか無い)、ここから引く必要がある。
    # Node と pnpm は unstable 側から取るので、この input は PHP 専用。
    nixpkgs-2211.url = "github:NixOS/nixpkgs/nixos-22.11";
  };

  outputs =
    { nixpkgs, nixpkgs-2211, ... }:
    let
      systems = [
        "x86_64-linux"
        "aarch64-linux"
        "x86_64-darwin"
        "aarch64-darwin"
      ];
      forAllSystems = nixpkgs.lib.genAttrs systems;
    in
    {
      devShells = forAllSystems (
        system:
        let
          pkgs = nixpkgs.legacyPackages.${system};
          legacy = nixpkgs-2211.legacyPackages.${system};

          # デフォルトで gd / pdo_mysql / pdo_sqlite / mbstring / iconv / curl /
          # zip / bcmath / exif が有効になっており、TCPDF による PDF 生成、
          # freee API の生 cURL、sqlite でのテストまで追加設定なしで動く。
          php = legacy.php81;

          # Vite 5 は Node 18+ を、pnpm 11 は Node 22.13+ を要求する。
          # nodejs_18 / nodejs_20 は unstable では EOL 扱いで引けないため
          # nodejs_22 を使う。ネイティブビルドを伴う bcrypt は
          # Vite 移行時に (未使用だったため) 削除済み。
          nodejs = pkgs.nodejs_22;
        in
        {
          default = pkgs.mkShell {
            packages = [
              php
              php.packages.composer
              nodejs
              pkgs.pnpm
            ];

            shellHook = ''
              # composer / pnpm でインストールしたコマンドを直接叩けるようにする
              export PATH="$PWD/vendor/bin:$PWD/node_modules/.bin:$PATH"

              echo "docs-manager dev shell"
              echo "  php   $(php -r "echo PHP_VERSION;")  (Laravel 10 要件: 8.1+)"
              echo "  node  $(node --version)"
              echo "  pnpm  $(pnpm --version)"
              echo ""
              echo "  アプリの実行と MySQL は Sail 側: ./runner up"
              if [ ! -f .env ]; then
                echo "  ! .env がありません: cp .env.example .env && php artisan key:generate"
              fi
            '';
          };
        }
      );
    };
}
