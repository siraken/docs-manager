import React, { useState } from "react";
import ReactDOM from "react-dom";

function WorksModal() {
  const checklists = [
    "ヒアリングでやることと予算感をすり合わせする",
    "契約書・NDAを取り交わす",
    "着手金、納品時残金で分けて支払いを受けるようにする",
    "なるべく納期短めの案件にする",
    "デザインはロジックで説明できるようにする",
    "スケジュールは想定の1.5倍〜2倍で出しておく",
  ];
  const checks = document.getElementsByName(
    "checklist[]"
  ) as NodeListOf<HTMLInputElement>;
  const [checkedCount, setCheckedCount] = useState(0);
  const [buttonEnabled, setButtonEnabled] = useState(false);

  // TODO: this does not work
  for (let i = 0; i < checks.length; i++) {
    checks[i].addEventListener("change", () => {
      console.log(checkedCount);
      if (checks[i].checked) {
        setCheckedCount(checkedCount + 1);
        console.log(checkedCount, checks.length);
        if (checkedCount === checks.length) {
          setButtonEnabled(true);
        }
      } else {
        setCheckedCount(checkedCount - 1);
        setButtonEnabled(false);
      }
    });
  }
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
                  name="checklist[]"
                  className="form-check-input"
                  type="checkbox"
                  value=""
                  id={`checklist-${index}`}
                />
                <label
                  className="form-check-label"
                  htmlFor={`checklist-${index}`}
                >
                  {check}
                </label>
              </div>
            ))}
          </div>
          <div className="modal-footer">
            <button
              type="submit"
              id="submit_button"
              className={`btn btn-primary ${buttonEnabled ? "" : "disabled"}`}
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

export default WorksModal;

if (document.getElementById("works-modal")) {
  ReactDOM.render(<WorksModal />, document.getElementById("works-modal"));
}
