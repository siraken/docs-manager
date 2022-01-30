@extends('layouts/default')
@section('page')

<div id="app">

  <!-- website -->
  <section id="website" class="row">
    <div class="col-12">
      <h2>Webサイト制作</h2>
      <!-- サイト設計 -->
      <h3>サイト設計</h3>
      <div class="row mb-3">
        <div class="col-md-6">
          <label>サイトマップ</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>ワイヤーフレーム</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>コンテンツ案</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>コンテンツ制作</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>コンテンツ流し込み</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
      </div>
      <!-- SEO -->
      <h3>SEO</h3>
      <div class="row mb-3">
        <div class="col-md-6">
          <label>分析</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>マーケティング戦略</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
      </div>
      <!-- デザイン -->
      <h3>デザイン</h3>
      <div class="row mb-3">
        <div class="col-md-6">
          <label>デザイン</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
      </div>
      <!-- コーディング -->
      <h3>コーディング</h3>
      <div class="row mb-3">
        <div class="col-md-6">
          <label>コーディング</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
      </div>
      <!-- 環境構築 -->
      <h3>環境構築</h3>
      <div class="row mb-3">
        <div class="col-md-6">
          <label>サーバー</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>ドメイン</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>CMS</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>お問い合わせ</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>セキュリティ</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
        <div class="col-md-6">
          <label>リダイレクト</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
      </div>
      <!-- テスト -->
      <h3>テスト</h3>
      <div class="row mb-3">
        <div class="col-md-6">
          <label>テスト</label>
          <input
            type="number"
            class="form-control"
            placeholder=""
            v-model="pageNum"
          />
        </div>
      </div>
      <!-- サポート -->
      <h3>サポート</h3>
      <div class="row mb-3">
        <div class="col-md-6">
          <label>年間サポート</label>
          <select
            class="form-control"
            v-model="webSupportSelected"
          >
            <option value="" selected disabled>
                選択してください
            </option>
            <option
                v-for="webSupportOption in webSupportOptions"
                v-bind:value="webSupportOption.value"
            >
                @{{ webSupportOption.text }}
            </option>
          </select>
        </div>
      </div>
      <hr>
      <!-- total -->
      <div class="row">
        <div class="col">
          <p class="h3 mb-0">
            合計金額：<span
                class="font-weight-bold"
                >¥ @{{ webTotalPrice }}</span
            >
          </p>
          <p class="h5 mb-0">
            消費税：<span
                class="font-weight-bold"
                >¥ @{{ webTotalNoTaxPrice
                }}</span
            >
          </p>
        </div>
      </div>
    </div>

  </section><!-- website -->
  <!-- system -->
  <section id="system" class="row bg-white shadow my-3 px-3 py-4">
    <div class="col">
      <h2 class="h3">システム開発</h2>

        <div class="form-group">
            <label>基本プラン</label>
            <select
                class="form-control"
                v-model="systemSelected"
            >
                <option value="" selected disabled>
                    選択してください
                </option>
                <option
                    v-for="systemOption in systemOptions"
                    v-bind:value="systemOption.value"
                >
                    @{{ systemOption.text }}
                </option>
            </select>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>社員数</label>
                    <input
                        type="number"
                        class="form-control"
                        placeholder="¥750 ~ / 人"
                        v-model="memberNum"
                    />
                </div>
                <div class="col">
                    <label>素材作成</label>
                    <input
                        type="number"
                        class="form-control"
                        placeholder="¥1,000 ~ / 個"
                        v-model="webMaterialNum"
                    />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col">
                    <label>データのコピー/移動</label>
                    <input
                        type="number"
                        class="form-control"
                        placeholder="¥500 / 件"
                        v-model="transferNum"
                    />
                </div>
                <div class="col">
                    <label>諸経費</label>
                    <input
                        type="text"
                        class="form-control"
                        v-model="budget"
                    />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>年間サポート</label>
            <select
                class="form-control"
                v-model="webSupportSelected"
            >
                <option value="" selected disabled>
                    選択してください
                </option>
                <option
                    v-for="webSupportOption in webSupportOptions"
                    v-bind:value="webSupportOption.value"
                >
                    @{{ webSupportOption.text }}
                </option>
            </select>
        </div>
        <div class="form-group">
            <div class="form-check form-check-inline">
                <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="inHouseOption"
                    true-value="-5000"
                    false-value="0"
                />
                <label class="form-check-label"
                    >社内サーバー</label
                >
            </div>
            <div class="form-check form-check-inline">
                <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="sslOption"
                    true-value="2500"
                    false-value="0"
                />
                <label class="form-check-label"
                    >SSL化</label
                >
            </div>
            <div class="form-check form-check-inline">
                <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="responsiveOption"
                    true-value="3500"
                    false-value="0"
                />
                <label class="form-check-label"
                    >レスポンシブ</label
                >
            </div>
            <div class="form-check form-check-inline">
                <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="updateDiscount"
                    true-value="-3500"
                    false-value="0"
                />
                <label class="form-check-label"
                    >更新値引</label
                >
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col">
                    <p class="h5 text-end">
                        消費税：<span
                            class="font-weight-bold"
                            >¥ @{{ systemTotalNoTaxPrice
                            }}</span
                        >
                    </p>
                    <p class="h3 text-end">
                        合計金額：<span
                            class="font-weight-bold"
                            >¥ @{{ systemTotalPrice }}</span
                        >
                    </p>
                </div>
            </div>
        </div>
    </div>
  </section>
</div><!-- #app -->

<script>
let app = new Vue({
  el: "#app",
  computed: {
    /* --- --- Web Development --- --- */
    // 消費税計算
    webTotalNoTaxPrice: function () {
      return (
        (Number(this.webSelected) +
            Number(this.pageNum) * 1800 +
            Number(this.webMaterialNum) * 1000 +
            Number(this.transferNum) * 250 +
            Number(this.budget) +
            Number(this.webSupportSelected) +
            Number(this.seoOption) +
            Number(this.sslOption) +
            Number(this.responsiveOption) +
            Number(this.updateDiscount)) *
        0.1
      ).toLocaleString();
    },
    // 税込合計金額計算
    webTotalPrice: function () {
      return (
        (Number(this.webSelected) +
            Number(this.pageNum) * 1800 +
            Number(this.webMaterialNum) * 1000 +
            Number(this.transferNum) * 250 +
            Number(this.budget) +
            Number(this.webSupportSelected) +
            Number(this.seoOption) +
            Number(this.sslOption) +
            Number(this.responsiveOption) +
            Number(this.updateDiscount)) *
          1.1
      ).toLocaleString();
    },
    /* --- --- System Developemnt --- --- */
    // 消費税計算
    systemTotalNoTaxPrice: function () {
        return (
            (Number(this.systemSelected) +
                Number(this.memberNum) * 750 +
                Number(this.webMaterialNum) * 1000 +
                Number(this.transferNum) * 250 +
                Number(this.budget) +
                Number(this.webSupportSelected) +
                Number(this.seoOption) +
                Number(this.sslOption) +
                Number(this.responsiveOption) +
                Number(this.updateDiscount)) *
            0.1
        ).toLocaleString();
    },
    // 税込合計金額計算
    systemTotalPrice: function () {
        return (
            (Number(this.systemSelected) +
                Number(this.memberNum) * 750 +
                Number(this.webMaterialNum) * 1000 +
                Number(this.transferNum) * 250 +
                Number(this.budget) +
                Number(this.webSupportSelected) +
                Number(this.seoOption) +
                Number(this.sslOption) +
                Number(this.responsiveOption) +
                Number(this.updateDiscount)) *
            1.1
        ).toLocaleString();
    },
    /* --- --- Sum All --- --- */
    allTotalPrice: function () {
        return Number(
            this.webTotalPrice + this.systemTotalPrice
        ).toLocaleString();
    },
  },
  data: {
    /* --- --- Web Development --- --- */
    // Webプラン選択
    webSelected: "",
    webOptions: [
        { text: "Basic - ¥35,400", value: 35400 },
        { text: "Standard - ¥72,400", value: 72400 },
        { text: "Advanced - ¥142,000", value: 142000 },
        { text: "Pro - ¥245,000", value: 245000 },
        { text: "Enterprise - ¥385,000", value: 385000 },
    ],
    // オプション
    pageNum: "",
    webMaterialNum: "",
    transferNum: "",
    budget: "",
    // Webサポート選択
    webSupportSelected: "",
    webSupportOptions: [
        { text: "Basic - ¥21,600", value: 21600 },
        { text: "Basic Plus - ¥25,200", value: 25200 },
        { text: "Pro - ¥42,000", value: 42000 },
        { text: "Enterprise - ¥81,600", value: 81600 },
    ],
    seoOption: "",
    sslOption: "",
    responsiveOption: "",
    updateDiscount: "",
    /* --- --- System Development --- --- */
    // システムプラン
    systemSelected: "",
    systemOptions: [
        { text: "Basic - ¥75,000", value: 75000 },
        { text: "Standard - ¥128,000", value: 128000 },
        { text: "Enterprise - ¥580,000", value: 580000 },
    ],
    // オプション
    memberNum: "",
  },
});
</script>

@endsection
