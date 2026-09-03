/**
 * サーバーが Inertia の props として渡す JSON の型。
 *
 * PHP 側の ViewModel の jsonSerialize() と対になっている。
 * 片方を変えたらもう片方も直すこと。
 */

/** App\Http\ViewModels\TravelView */
export type Travel = {
  id: number;
  relId: string;
  destination: string;
  purpose: string;
  price: number;
  priceLabel: string;
  dateFrom: string;
  dateTo: string;
  applyDate: string;
  applyPerson: string;
  shortPurpose: string;
  urls: { show: string; pdf: string } | null;
};

/** App\Http\ViewModels\TravelExpenseView */
export type TravelExpense = {
  id: number | null;
  relId: string;
  destination: string;
  purpose: string;
  applyDate: string;
  dateFrom: string;
  dateTo: string;
  payDate: string;
  applyPerson: string;
  transportationFee: number;
  accommodationFee: number;
  gasFee: number;
  dinnerFee: number;
  lunchFee: number;
  dailyAllowance: number;
  totalFee: number;
  totalFeeLabel: string;
  shortPurpose: string;
  urls: { show: string; edit: string; pdf: string } | null;
};
