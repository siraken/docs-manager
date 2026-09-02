import ky from "ky";

/**
 * Laravel 向けに設定した ky インスタンス。
 *
 * axios から移行する際、axios が暗黙に行っていた 2 つの処理をここで明示的に
 * 再現している。
 *
 * 1. **XSRF-TOKEN クッキーを X-XSRF-TOKEN ヘッダに載せる**
 *    このアプリの JSON エンドポイント (login-nfc / login-metamask /
 *    orders/set-status) はいずれも `routes/web.php` にあり、CSRF ミドルウェア
 *    (Laravel 13 の PreventRequestForgery) の対象。ヘッダが無いと 419 になる
 * 2. **X-Requested-With: XMLHttpRequest**
 *    Laravel がリクエストを AJAX と判定するために見るヘッダ
 *
 * fetch は同一オリジンならクッキーを既定で送る (`credentials: "same-origin"`)
 * ので、クッキー送信のための指定は不要。
 */

/**
 * document.cookie から XSRF-TOKEN を取り出す。
 * Laravel は値を URL エンコードして入れるのでデコードして返す。
 */
function xsrfToken(): string | undefined {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);

  return match ? decodeURIComponent(match[1]) : undefined;
}

export const http = ky.create({
  headers: {
    "X-Requested-With": "XMLHttpRequest",
  },
  hooks: {
    // ky 2.x のフックは引数を 1 つのオブジェクトで受け取る (1.x とは非互換)
    beforeRequest: [
      ({ request }) => {
        const token = xsrfToken();

        if (token !== undefined) {
          request.headers.set("X-XSRF-TOKEN", token);
        }
      },
    ],
  },
});

export default http;
