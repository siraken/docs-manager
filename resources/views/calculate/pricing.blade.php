@extends('layouts/default')
@section('page')

<div class="row">
    <div class="col-12">
        {{-- <div id="example"></div> --}}
        <div id="calc"></div>
    </div>
</div>

<script>
// let app = new Vue({
//   el: "#app",
//   computed: {
//     /* --- --- Web Development --- --- */
//     // 消費税計算
//     webTotalNoTaxPrice: function () {
//       return (
//         (Number(this.webSelected) +
//             Number(this.pageNum) * 1800 +
//             Number(this.webMaterialNum) * 1000 +
//             Number(this.transferNum) * 250 +
//             Number(this.budget) +
//             Number(this.webSupportSelected) +
//             Number(this.seoOption) +
//             Number(this.sslOption) +
//             Number(this.responsiveOption) +
//             Number(this.updateDiscount)) *
//         0.1
//       ).toLocaleString();
//     },
//     // 税込合計金額計算
//     webTotalPrice: function () {
//       return (
//         (Number(this.webSelected) +
//             Number(this.pageNum) * 1800 +
//             Number(this.webMaterialNum) * 1000 +
//             Number(this.transferNum) * 250 +
//             Number(this.budget) +
//             Number(this.webSupportSelected) +
//             Number(this.seoOption) +
//             Number(this.sslOption) +
//             Number(this.responsiveOption) +
//             Number(this.updateDiscount)) *
//           1.1
//       ).toLocaleString();
//     },
//     /* --- --- System Developemnt --- --- */
//     // 消費税計算
//     systemTotalNoTaxPrice: function () {
//         return (
//             (Number(this.systemSelected) +
//                 Number(this.memberNum) * 750 +
//                 Number(this.webMaterialNum) * 1000 +
//                 Number(this.transferNum) * 250 +
//                 Number(this.budget) +
//                 Number(this.webSupportSelected) +
//                 Number(this.seoOption) +
//                 Number(this.sslOption) +
//                 Number(this.responsiveOption) +
//                 Number(this.updateDiscount)) *
//             0.1
//         ).toLocaleString();
//     },
//     // 税込合計金額計算
//     systemTotalPrice: function () {
//         return (
//             (Number(this.systemSelected) +
//                 Number(this.memberNum) * 750 +
//                 Number(this.webMaterialNum) * 1000 +
//                 Number(this.transferNum) * 250 +
//                 Number(this.budget) +
//                 Number(this.webSupportSelected) +
//                 Number(this.seoOption) +
//                 Number(this.sslOption) +
//                 Number(this.responsiveOption) +
//                 Number(this.updateDiscount)) *
//             1.1
//         ).toLocaleString();
//     },
//     /* --- --- Sum All --- --- */
//     allTotalPrice: function () {
//         return Number(
//             this.webTotalPrice + this.systemTotalPrice
//         ).toLocaleString();
//     },
//   },
//   data: {
//     /* --- --- Web Development --- --- */
//     // Webプラン選択
//     webSelected: "",
//     webOptions: [
//         { text: "Basic - ¥35,400", value: 35400 },
//         { text: "Standard - ¥72,400", value: 72400 },
//         { text: "Advanced - ¥142,000", value: 142000 },
//         { text: "Pro - ¥245,000", value: 245000 },
//         { text: "Enterprise - ¥385,000", value: 385000 },
//     ],
//     // オプション
//     pageNum: "",
//     webMaterialNum: "",
//     transferNum: "",
//     budget: "",
//     // Webサポート選択
//     webSupportSelected: "",
//     webSupportOptions: [
//         { text: "Basic - ¥21,600", value: 21600 },
//         { text: "Basic Plus - ¥25,200", value: 25200 },
//         { text: "Pro - ¥42,000", value: 42000 },
//         { text: "Enterprise - ¥81,600", value: 81600 },
//     ],
//     seoOption: "",
//     sslOption: "",
//     responsiveOption: "",
//     updateDiscount: "",
//     /* --- --- System Development --- --- */
//     // システムプラン
//     systemSelected: "",
//     systemOptions: [
//         { text: "Basic - ¥75,000", value: 75000 },
//         { text: "Standard - ¥128,000", value: 128000 },
//         { text: "Enterprise - ¥580,000", value: 580000 },
//     ],
//     // オプション
//     memberNum: "",
//   },
// });
</script>

@endsection
