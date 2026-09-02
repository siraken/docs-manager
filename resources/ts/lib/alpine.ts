import Alpine from "alpinejs";

/**
 * Bootstrap の JS コンポーネント (modal / dropdown / collapse / toast) の
 * 置き換え。Tailwind は CSS のみを提供するため、開閉状態は Alpine が持つ。
 *
 * Blade 側は resources/views/components/ の x-modal / x-dropdown などから
 * 使う。個別に window へ生やさず、Alpine のディレクティブだけで完結させる。
 */
(window as any).Alpine = Alpine;

Alpine.start();

export default Alpine;
