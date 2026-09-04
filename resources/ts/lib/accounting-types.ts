/**
 * 仕訳帳・勘定科目のサーバー側 ViewModel と対になる型。
 *
 * PHP 側の jsonSerialize() と対応している。片方を変えたらもう片方も直すこと。
 */

/** App\Http\ViewModels\AccountView */
export type Account = {
  id: number;
  name: string;
  code: string | null;
  /** 「101 現金」のような表示。コードが無ければ名前だけ */
  displayName: string;
  /** asset / liability / equity / revenue / expense */
  typeValue: string;
  /** 区分の表示名 (資産 / 負債 / …) */
  type: string;
  /** 残高が立つ側 (借方 / 貸方)。区分から決まる */
  normalBalance: string;
  isActive: boolean;
  note: string | null;
  urls: { edit: string; delete: string } | null;
};

/** App\Http\ViewModels\AccountView::options() */
export type AccountOption = { id: number; name: string; type: string };

/** App\Http\ViewModels\JournalEntryView */
export type JournalEntry = {
  id: number;
  /** フォームの value 用 (YYYY-MM-DD) */
  date: string;
  /** 表示用 (YYYY/MM/DD) */
  dateLabel: string;
  debitAccountId: number;
  debitAccountName: string;
  creditAccountId: number;
  creditAccountName: string;
  amount: number;
  amountLabel: string;
  description: string;
  note: string | null;
  urls: { edit: string; delete: string } | null;
};

/** App\Http\ViewModels\TrialBalanceRowView */
export type TrialBalanceRow = {
  accountId: number;
  accountName: string;
  typeValue: string;
  type: string;
  debitTotal: number;
  debitTotalLabel: string;
  creditTotal: number;
  creditTotalLabel: string;
  debitBalance: number;
  /** 0 のときは "-" */
  debitBalanceLabel: string;
  creditBalance: number;
  creditBalanceLabel: string;
};

/** JournalController::trialBalance() が渡す縦計 */
export type TrialBalanceTotals = {
  debitTotal: string;
  creditTotal: string;
  debitBalance: string;
  creditBalance: string;
  /** 貸借が一致しているか。単一仕訳しか作れないので常に true のはず */
  isBalanced: boolean;
};

/** 仕訳帳の絞り込み条件 */
export type JournalFilter = {
  year: number | null;
  month: number | null;
  keyword: string | null;
  accountId: number | null;
};
