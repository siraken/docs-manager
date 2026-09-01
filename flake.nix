{
  description = "docs-manager: Sail / CI と揃えた開発ツールチェーン";

  inputs = {
    # mkShell と補助ツール用。現行 OS との親和性のため新しい方を使う。
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";

    # PHP 7.4 と Node 16 を同時に含む最後の nixpkgs リリース。
    # php74 は 22.11 で削除されており ("php74 has been dropped due to the lack of
    # maintanence from upstream")、nixpkgs-unstable には php82 以降しか無い。
    # docker-compose.yml (docker/7.4) が PHP 7.4 なので、本番と同じ
    # バージョンを使うにはここから引く必要がある。
    nixpkgs-2205.url = "github:NixOS/nixpkgs/nixos-22.05";
  };

  outputs =
    { nixpkgs, nixpkgs-2205, ... }:
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
          legacy = nixpkgs-2205.legacyPackages.${system};

          # デフォルトで gd / pdo_mysql / pdo_sqlite / mbstring / iconv / curl /
          # zip / bcmath / exif が有効になっており、TCPDF による PDF 生成、
          # freee API の生 cURL、CI と同じ sqlite でのテストまで追加設定なしで動く。
          php = legacy.php74;

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
              echo "  php   $(php -r "echo PHP_VERSION;")  (Sail / CI と同じ)"
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
