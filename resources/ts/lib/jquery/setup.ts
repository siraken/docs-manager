import $ from "jquery";

// jquery-ui のウィジェットは window.jQuery を参照する。
// ESM では import が宣言順に評価されるため、このファイルを
// jquery-ui より先に import することでグローバルを用意する。
(window as any).$ = $;
(window as any).jQuery = $;

export default $;
