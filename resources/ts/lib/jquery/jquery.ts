import { marked } from "marked";

// setup が先に window.jQuery を用意してから jquery-ui を読み込む
import $ from "./setup";
// jquery-ui のモジュールは AMD の define([...]) 形式で依存を宣言しており、
// ESM (Vite) ではその依存が自動では解決されない。sortable は mouse を、
// mouse は widget を必要とするため、依存する順に明示して import する。
// (これを怠ると $.ui.mouse が undefined になり、sortable の初期化で
//  例外が出てこのファイル全体の処理が止まる)
import "jquery-ui/ui/widget";
import "jquery-ui/ui/widgets/mouse";
import "jquery-ui/ui/widgets/sortable";

$(() => {
  // 初期状態の行数
  let rowNumber = $(".main_tbody").children().length;

  // 行追加
  function addCustomRow(num: number) {
    rowNumber++;
    $(".main_tbody").append(`
        <tr class="sortable-tr">
            <td class="action-cell"><span class="delete-row-button">×</span></td>
            <td class="item-cell">
                <input type="text" name="item_name[]" class="form-control">
                <div class="items_box">
                    <ul class="items">
                    <?php foreach ($items as $item): ?>
                        <li class="items_name" data-name="<?= $item['Item']['item_name']; ?>" data-unit="<?= $item['Item']['unit']; ?>" data-cost="<?= $item['Item']['cost']; ?>" data-tax="<?= $item['Item']['tax']; ?>"><?= $item['Item']['item_name']; ?> @<?= number_format($item['Item']['cost']); ?>円</li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </td>
            <td>
                <input type="text" name="qty[]" id="qty_${num}" class="form-control text-end calc">
            </td>
            <td>
                <input type="text" name="unit[]" class="form-control text-center" placeholder="単位" value="">
            </td>
            <td>
                <input type="text" name="cost[]" id="cost_${num}" class="form-control text-end calc" value="">
            </td>
            <td>
                <select name="tax[]" id="tax_${num}" class="form-select calc">
                    <option value="1">10%</option>
                    <option value="2">軽減8%</option>
                    <option value="3">8%</option>
                    <option value="4">5%</option>
                    <option value="5">対象外</option>
                </select>
            </td>
            <td>
                <input type="text" name="price[]" id="price_${num}" class="form-control text-end readonly" tabindex="-1" readonly>
                <input type="hidden" id="tax_price_${num}" readonly>
            </td>
        </tr>
        `);
  }

  // 明細部分計算
  function calcAll() {
    let total = {
      sub: 0,
      tax: 0,
      all: 0,
    };
    for (let i = 0; i < rowNumber; i++) {
      if (
        ($(`#qty_${i}`).val() !== "" || $(`#cost_${i}`).val() !== "") &&
        $(`#price_${i}`).length
      ) {
        // 税率
        let taxPer = 0;
        switch ($(`#tax_${i}`).val()) {
          case "1":
            taxPer = 0.1;
            break;
          case "2":
            taxPer = 0.08;
            break;
          case "3":
            taxPer = 0.08;
            break;
          case "4":
            taxPer = 0.05;
            break;
          default:
            taxPer = 0;
            break;
        }
        // 税抜金額: sub
        let price = Number($(`#qty_${i}`).val() * $(`#cost_${i}`).val());
        // 税: tax
        let taxPrice = Number(price * taxPer);
        // 税込金額: all
        let taxInPrice = price + taxPrice;
        // 値セット
        isNaN(price)
          ? $(`#price_${i}`).val("")
          : $(`#price_${i}`).val(taxInPrice);
        isNaN(taxPrice)
          ? $(`#tax_price_${i}`).val("")
          : $(`#tax_price_${i}`).val(taxPrice);
        total.sub += price;
        total.tax += taxPrice;
        total.all += taxInPrice;
      }
    }
    isNaN(total.sub) ? $("#subtotal").val(0) : $("#subtotal").val(total.sub);
    isNaN(total.tax) ? $("#taxTotal").val(0) : $("#taxTotal").val(total.tax);
    isNaN(total.all)
      ? $("#totalPrice").val(0)
      : $("#totalPrice").val(total.all);
  }

  // セルの横幅を固定したままSortable
  function fixPlaceHolderWidth(this: HTMLElement, event: any, ui: any) {
    ui.find(".action-cell").css("border", "none");

    // ui.children().each(() => {
    //   $(this).width($(this).width());
    // });
    return ui;
  }

  // 入力時計算処理
  $(".document-table").on("input", ".calc", () => {
    calcAll();
  });

  // 行を削除
  $(".document-table").on("click", ".delete-row-button", (event: any) => {
    if (1 < $(".main_tbody").children().length) {
      $(event.target.closest("tr")).remove();
      calcAll();
    }
  });

  // 行並べ替え
  $("#sortable").sortable({
    items: "tr.sortable-tr",
    start: (event: any, ui: any) => {
      ui.placeholder.height(ui.helper.outerHeight());
    },
    helper: fixPlaceHolderWidth,
    update: (event: any, ui: any) => {
      // console.log($("#sortable").sortable("toArray"));
    },
  });

  // マークダウンプレビュー
  $("#description").on("input", () => {
    let html = marked($("#description").val());
    $("#preview").html(html);
  });

  // windowオブジェクトに行追加処理を追加
  (window as any).addRow = () => {
    addCustomRow(rowNumber);
  };

  // onload時計算実行
  calcAll();
});
