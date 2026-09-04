/**
 * 勤務報告のサーバー側 ViewModel と対になる型。
 *
 * PHP 側の App\Http\ViewModels\ReportView::jsonSerialize() と対応している。
 * 片方を変えたらもう片方も直すこと。
 */

/** App\Http\ViewModels\ReportView */
export type Report = {
  id: number;
  userId: number | null;
  userName: string;
  customerId: number | null;
  customerName: string;
  projectId: number | null;
  projectName: string;
  title: string;
  description: string | null;
  /** フォームの value 用 (YYYY-MM-DD) */
  date: string;
  /** 表示用 (YYYY/MM/DD) */
  dateLabel: string;
  startTime: string | null;
  endTime: string | null;
  workMinutes: number;
  workHours: number;
  /** "7:30" 形式 */
  workTimeLabel: string;
  urls: { show: string; edit: string; delete: string } | null;
};

/** ReportController::index() が渡す集計 */
export type ReportSummary = {
  totalWorkMinutes: number;
  totalWorkHours: number;
  totalWorkTimeLabel: string;
  workDays: number;
};

/** ReportController::index() が渡す絞り込み条件 */
export type ReportFilter = {
  year: number | null;
  month: number | null;
  keyword: string | null;
};
