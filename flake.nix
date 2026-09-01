{
  description = "docs-manager: Laravel が要求する PHP に揃えた開発ツールチェーン";

  inputs = {
    # mkShell と補助ツール用。現行 OS との親和性のため新しい方を使う。
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";

    # Laravel 9 が要求する PHP 8.0.2+ を満たす php81 (8.1.19) と、
    # フロントエンドビルド用の Node 16 を同時に含む nixpkgs リリース。
    # php81 は nixpkgs-unstable では EOL 扱いで評価が throw されるため
    # (unstable には php82 以降しか無い)、ここから引く必要がある。
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

          # package.json の bcrypt は node-gyp を伴うネイティブモジュールで、
          # Node 17 以降向けの prebuilt が存在しない。削除前の CI の
          # Node.js ジョブ (コメントアウト済み) も 16.x を想定していた。
          nodejs = legacy.nodejs-16_x;
        in
        {
          default = pkgs.mkShell {
            packages = [
              php
              php.packages.composer
              nodejs
              legacy.yarn
            ];

            shellHook = ''
              # composer / yarn でインストールしたコマンドを直接叩けるようにする
              export PATH="$PWD/vendor/bin:$PWD/node_modules/.bin:$PATH"

              echo "docs-manager dev shell"
              echo "  php   $(php -r "echo PHP_VERSION;")  (Laravel 9 要件: 8.0.2+)"
              echo "  node  $(node --version)"
              echo "  yarn  $(yarn --version)"
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
