import React, { useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import type { ChangeEvent } from "react";

const CHECKLISTS = [
  "ヒアリングでやることと予算感をすり合わせする",
  "契約書・NDAを取り交わす",
  "着手金、納品時残金で分けて支払いを受けるようにする",
  "なるべく納期短めの案件にする",
  "デザインはロジックで説明できるようにする",
  "スケジュールは想定の1.5倍〜2倍で出しておく",
];

/**
 * 受注前確認モーダル。
 *
 * 開閉のきっかけは Blade 側のボタンが投げる CustomEvent。
 * 旧実装は Bootstrap の data-bs-toggle に依存していたが、Bootstrap の JS を
 * 外したため、Blade (Alpine) と React のどちらにも寄らない DOM イベントで
 * 受け渡している。
 */
function ProjectsModal() {
  const [open, setOpen] = useState(false);
  const [checked, setChecked] = useState<boolean[]>(
    () => CHECKLISTS.map(() => false)
  );

  const allChecked = checked.every(Boolean);

  useEffect(() => {
    const onOpen = () => setOpen(true);
    const onKeydown = (e: KeyboardEvent) => {
      if (e.key === "Escape") setOpen(false);
    };

    window.addEventListener("open-projects-modal", onOpen);
    window.addEventListener("keydown", onKeydown);

    return () => {
      window.removeEventListener("open-projects-modal", onOpen);
      window.removeEventListener("keydown", onKeydown);
    };
  }, []);

  const toggle = (index: number) => (e: ChangeEvent<HTMLInputElement>) => {
    setChecked((prev) => prev.map((v, i) => (i === index ? e.target.checked : v)));
  };

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:items-center">
      <div
        className="fixed inset-0 bg-slate-900/50"
        onClick={() => setOpen(false)}
      />

      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="projects-modal-title"
        className="relative w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-900/5 sm:max-w-lg"
      >
        <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <h2
            id="projects-modal-title"
            className="text-base font-semibold text-slate-900"
          >
            受注前確認
          </h2>
          <button
            type="button"
            aria-label="閉じる"
            onClick={() => setOpen(false)}
            className="rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
          >
            <i className="bi bi-x-lg text-sm" aria-hidden="true" />
          </button>
        </div>

        <div className="px-5 py-4">
          <p className="mb-3 text-sm text-slate-600">
            以下の確認事項を確認してください。
          </p>

          <ul className="space-y-2">
            {CHECKLISTS.map((check, index) => (
              <li key={check}>
                <label
                  htmlFor={`check_${index}`}
                  className="flex cursor-pointer items-start gap-2.5 rounded-lg px-2 py-1.5 transition hover:bg-slate-50"
                >
                  <input
                    type="checkbox"
                    id={`check_${index}`}
                    checked={checked[index]}
                    onChange={toggle(index)}
                    className="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-brand-600 focus:ring-brand-600"
                  />
                  <span className="text-sm text-slate-700">{check}</span>
                </label>
              </li>
            ))}
          </ul>
        </div>

        <div className="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-3">
          <p className="text-xs text-slate-500">
            {checked.filter(Boolean).length} / {CHECKLISTS.length} 件を確認済み
          </p>
          {/* #MainForm の内側にマウントされるため、そのまま送信できる */}
          <button
            type="submit"
            id="submit_button"
            disabled={!allChecked}
            className="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-brand-700 disabled:pointer-events-none disabled:opacity-50"
          >
            確認しました
          </button>
        </div>
      </div>
    </div>
  );
}

export default ProjectsModal;

const rootElement = document.getElementById("projects-modal");
if (rootElement) {
  createRoot(rootElement).render(<ProjectsModal />);
}
