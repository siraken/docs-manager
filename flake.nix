{
  description = "docs-manager: Laravel が要求する PHP に揃えた開発ツールチェーン";

  inputs = {
    # Laravel 13 の要件は PHP 8.3+ で、unstable の php83 で満たせる。
    # Laravel 10 までは php81 を nixos-22.11 から引く必要があったが
    # (unstable では php81 が EOL 扱いで評価が throw される)、
    # 11 以降その別 input は不要になったので単一 nixpkgs に戻している。
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
  };

  outputs =
    { nixpkgs, ... }:
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

          # デフォルトで gd / pdo_mysql / pdo_sqlite / mbstring / iconv / curl /
          # zip / bcmath / exif が有効になっており、TCPDF による PDF 生成、
          # freee API の生 cURL、sqlite でのテストまで追加設定なしで動く。
          php = pkgs.php83;

          # Vite 5 は Node 18+ を、pnpm 11 は Node 22.13+ を要求する。
          # nodejs_18 / nodejs_20 は unstable では EOL 扱いで引けない。
          nodejs = pkgs.nodejs_22;
        in
        {
          default = pkgs.mkShell {
            packages = [
              php
              php.packages.composer
              nodejs
              pkgs.pnpm

              # 開発コマンドのランナー。レシピは justfile にある
              pkgs.just
            ];

            shellHook = ''
              # composer / pnpm でインストールしたコマンドを直接叩けるようにする
              export PATH="$PWD/vendor/bin:$PWD/node_modules/.bin:$PATH"

              echo "docs-manager dev shell"
              echo "  php   $(php -r "echo PHP_VERSION;")  (Laravel 13 要件: 8.3+)"
              echo "  node  $(node --version)"
              echo "  pnpm  $(pnpm --version)"
              echo "  just  $(just --version | cut -d' ' -f2)"
              echo ""
              echo "  コマンドの一覧: just"
              echo "  アプリの実行と MySQL は Sail 側: just up"
              if [ ! -f .env ]; then
                echo "  ! .env がありません: just init"
              fi
            '';
          };
        }
      );
    };
}
