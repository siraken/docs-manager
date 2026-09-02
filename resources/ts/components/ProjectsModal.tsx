import React, { useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import type { ChangeEvent } from "react";

function ProjectsModal() {
  const checklists = [
    "ヒアリングでやることと予算感をすり合わせする",
    "契約書・NDAを取り交わす",
    "着手金、納品時残金で分けて支払いを受けるようにする",
    "なるべく納期短めの案件にする",
    "デザインはロジックで説明できるようにする",
    "スケジュールは想定の1.5倍〜2倍で出しておく",
  ];
  const [checkedCount, setCheckedCount] = useState(0);
  const [buttonEnabled, setButtonEnabled] = useState(false);

  const checkOnChange = (e: ChangeEvent<HTMLInputElement>) => {
    const count = checkedCount + (e.target.checked ? 1 : -1);
    setCheckedCount(count);
    setButtonEnabled(count === checklists.length);
  };

  return (
    <div
      className="modal fade"
      id="exampleModal"
      tabIndex={-1}
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
      <div className="modal-dialog modal-dialog-centered">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title" id="exampleModalLabel">
              受注前確認
            </h5>
            <button
              type="button"
              className="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div className="modal-body">
            <p>以下の確認事項を確認してください。</p>
            {checklists.map((check, index) => (
              <div className="form-check" key={index}>
                <input
                  className="form-check-input"
                  type="checkbox"
                  id={`check_${index}`}
                  onChange={checkOnChange}
                />
                <label className="form-check-label" htmlFor={`check_${index}`}>
                  {check}
                </label>
              </div>
            ))}
          </div>
          <div className="modal-footer">
            <button
              type="submit"
              id="submit_button"
              className={`btn btn-secondary ${buttonEnabled ? "" : "disabled"}`}
              data-bs-dismiss="modal"
            >
              確認しました
            </button>
          </div>
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
