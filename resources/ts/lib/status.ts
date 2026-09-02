import { http } from "./http";

/**
 * 発注書一覧 (orders/index, orders/trash) のステータス切り替え。
 *
 * Blade 側のピルが `onclick="slipSetter.status(this, ...)"` で呼ぶ。
 * 一覧の Blade は、このスクリプトがトークンを読めるように `@csrf` だけを
 * 単体で出力している。
 *
 * 次の状態は保存済みの値からサーバーが決める。以前は画面が持っている現在値
 * (currentStatus) を送ってサーバーがそれを基に遷移を決めていたため、画面が
 * 古いと保存結果がずれた。
 */

const DOCUMENT_ROOT =
  import.meta.env.VITE_APP_ENV === "local" ? "" : "/docs-manager";

type SetStatusResponse = {
  status?: number;
  is_issued?: number;
  is_ordered?: number;
};

export const slipSetter: Window["slipSetter"] = {
  status(el, type, id) {
    const token =
      document.querySelector<HTMLInputElement>('input[name="_token"]')?.value ??
      "";

    // 二度押しで多重に投げないようボタンを止めておく
    if (el instanceof HTMLButtonElement) {
      el.disabled = true;
    }

    http
      .post(`${DOCUMENT_ROOT}/orders/set-status`, {
        json: {
          type: type,
          id: id,
          _token: token,
        },
      })
      .json<SetStatusResponse>()
      .then((data) => {
        if (data.status === 200) {
          location.reload();
        } else {
          console.error("ステータスの更新に失敗しました", data);
        }
      })
      .catch((error) => {
        console.error("ステータスの更新に失敗しました", error);
      })
      .finally(() => {
        if (el instanceof HTMLButtonElement) {
          el.disabled = false;
        }
      });
  },
};

window.slipSetter = slipSetter;
