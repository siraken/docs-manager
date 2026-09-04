/**
 * 社内研修（講座・受講記録）のサーバー側 ViewModel と対になる型。
 *
 * PHP 側の jsonSerialize() と対応している。片方を変えたらもう片方も直すこと。
 */

/** App\Http\ViewModels\CourseView */
export type Course = {
  id: number;
  title: string;
  description: string | null;
  /** 修了すると得られるポイント */
  exp: number;
  expLabel: string;
  /** 下書きの講座は受講記録の選択肢に出ない */
  isPublished: boolean;
  urls: { edit: string; delete: string } | null;
};

/** App\Http\ViewModels\CourseView::options() */
export type CourseOption = { id: number; name: string; exp: number };

/** App\Http\ViewModels\EnrollmentView */
export type Enrollment = {
  id: number;
  userId: number;
  userName: string;
  courseId: number;
  courseTitle: string;
  /** 完了していない受講は 0 になる */
  exp: number;
  /** not_started / in_progress / completed */
  statusValue: string;
  /** 状態の表示名（未受講 / 受講中 / 完了） */
  status: string;
  startedAt: string | null;
  startedAtLabel: string;
  completedAt: string | null;
  completedAtLabel: string;
  note: string | null;
  urls: { edit: string; delete: string } | null;
};

/** EnrollmentController::index() が渡す集計 */
export type LearningSummary = {
  completedCount: number;
  inProgressCount: number;
  earnedExp: number;
  earnedExpLabel: string;
};

/** 受講記録の絞り込み条件 */
export type EnrollmentFilter = {
  userId: number | null;
  courseId: number | null;
  status: string | null;
};

/** App\Http\ViewModels\AssignmentView */
export type Assignment = {
  id: number;
  courseId: number;
  courseTitle: string;
  title: string;
  description: string | null;
  dueOn: string | null;
  /** 期限が無ければ "期限なし" */
  dueOnLabel: string;
  /** 今日の時点で期限を過ぎているか */
  isOverdue: boolean;
  urls: { edit: string; delete: string } | null;
};

/** App\Http\ViewModels\AssignmentView::options() */
export type AssignmentOption = { id: number; name: string; course: string };

/** App\Http\ViewModels\SubmissionView */
export type Submission = {
  id: number;
  assignmentId: number;
  assignmentTitle: string;
  courseTitle: string;
  userId: number;
  userName: string;
  /** not_submitted / submitted / returned / approved */
  statusValue: string;
  status: string;
  submittedAt: string | null;
  submittedAtLabel: string;
  body: string | null;
  feedback: string | null;
  /** 期限に遅れて出したか */
  isLate: boolean;
  urls: { edit: string; delete: string } | null;
};

/** App\Http\ViewModels\ChatMessageView */
export type ChatMessage = {
  id: number;
  userId: number;
  userName: string;
  body: string;
  /** "2026-09-04 14:30" */
  postedAt: string;
  /** "9/4 14:30" */
  postedAtLabel: string;
  /** 自分の発言か。見た目と削除ボタンの出し分けに使う */
  isMine: boolean;
  /** 消せるのは自分の発言だけなので、他人の発言では null */
  urls: { delete: string } | null;
};
