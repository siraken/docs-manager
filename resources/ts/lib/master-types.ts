/**
 * サーバーが Inertia の props として渡す JSON の型。
 *
 * PHP 側の ViewModel の jsonSerialize() と対になっている。
 * 片方を変えたらもう片方も直すこと。
 */

/** App\Http\ViewModels\CustomerView */
export type Customer = {
  id: number;
  name: string;
  /** 取引先の担当者名。発注書フォームの担当者欄の初期値に使う */
  person: string | null;
  isCompany: boolean;
  email: string | null;
  phone: string | null;
  postCode: string | null;
  address: string | null;
  city: string | null;
  state: string | null;
  country: string | null;
  note: string | null;
  location: string;
  urls: { edit: string } | null;
};

/** App\Http\ViewModels\ProjectView */
export type Project = {
  id: number;
  name: string;
  description: string | null;
  clientId: number | null;
  /** 取引先名。サーバー側で顧客マスタから引いて渡す */
  clientName: string;
  /** Jira のキー (NOVA / NOVA-123)。旧 relatedTaskId の置き換え */
  jiraKey: string | null;
  /** Jira へのリンク。キーが無いか URL 未設定なら null */
  jiraUrl: string | null;
  startDate: string | null;
  endDate: string | null;
  paymentDate: string | null;
  startDateLabel: string;
  endDateLabel: string;
  paymentDateLabel: string;
  price: number;
  priceLabel: string;
  statusValue: number;
  status: string;
  urls: { edit: string } | null;
};

/** App\Http\ViewModels\ContractView */
export type Contract = {
  id: number;
  name: string;
  contractNo: string | null;
  customerId: number | null;
  customerName: string;
  startDate: string | null;
  endDate: string | null;
  termLabel: string;
  /** ContractStatus::label() の結果 (契約中 / 開始前 / 終了) */
  status: string;
  /** ContractStatus の値 (active / scheduled / expired) */
  statusValue: string;
  description: string | null;
  urls: { edit: string; delete: string } | null;
};

/** App\Http\ViewModels\UserView */
export type User = {
  id: number;
  name: string;
  email: string;
  nfcSerialNumber: string | null;
  walletAddress: string | null;
  hasTwoFactor: boolean;
  updatedAt: string;
  urls: { edit: string; twoFactor: string; delete: string } | null;
};

/** セレクトの選択肢 */
export type Option<T = string> = { value: T; label: string };

/** セレクトに渡すマスタ。CustomerView / UserView / ProjectView の options() */
export type MasterOption = { id: number; name: string };
