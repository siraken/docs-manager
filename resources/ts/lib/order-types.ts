/**
 * サーバー (App\Http\ViewModels\OrderView) が JSON で渡してくる形。
 *
 * PHP 側の jsonSerialize() と対になっているので、片方を変えたらもう片方も
 * 直すこと。ここに書いてあるのは「画面が受け取るもの」の定義で、
 * 業務ルール上の型ではない。
 */

export type OrderLineItem = {
  itemName: string;
  quantity: number;
  unit: string | null;
  unitCost: number;
  taxId: number;
  taxLabel: string;
  total: number;
};

export type OrderUrls = {
  show: string;
  edit: string;
  pdf: string;
  csv: string;
  trash: string;
  restore: string;
};

export type OrderListItem = {
  id: number;
  orderNo: string;
  title: string | null;
  customerId: number;
  customerName: string;
  displayName: string;
  issuedDateLabel: string;
  expDateLabel: string;
  totalLabel: string;
  issueStatus: number;
  orderStatus: number;
  isDeleted: boolean;
  note: string | null;
  urls: OrderUrls;
};

export type OrderDetail = OrderListItem & {
  responsible: string | null;
  honorTitle: string | null;
  issuedDate: string;
  expDate: string | null;
  subtotal: number;
  tax: number;
  total: number;
  remarks: string | null;
  addressee: string;
  lines: OrderLineItem[];
};

export type CustomerOption = {
  id: number;
  name: string;
  isCompany: boolean;
  email: string | null;
  location: string;
};

export type TaxOption = { value: number; label: string };
