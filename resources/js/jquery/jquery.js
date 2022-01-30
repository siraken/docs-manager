const $ = require("jquery");
require("jquery-ui/ui/widgets/sortable");

$(() => {
    // 初期状態の行数
    let rowNumber = $(".main_tbody").children().length;

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
                let price = Number(
                    $(`#qty_${i}`).val() * $(`#cost_${i}`).val()
                );
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
        isNaN(total.sub)
            ? $("#subtotal").val(0)
            : $("#subtotal").val(total.sub);
        isNaN(total.tax)
            ? $("#taxTotal").val(0)
            : $("#taxTotal").val(total.tax);
        isNaN(total.all)
            ? $("#totalPrice").val(0)
            : $("#totalPrice").val(total.all);
    }
    // 計算処理
    $(".document-table").on("input", ".calc", () => {
        calcAll();
    });

    // 行を削除
    $(".document-table").on("click", ".delete-row-button", (event) => {
        $(event.target.closest("tr")).remove();
        calcAll();
    });

    // セルの横幅を固定したままSortable
    function fixPlaceHolderWidth(event, ui) {
        ui.find(".action-cell").css("border", "none");

        ui.children().each(() => {
            $(this).width($(this).width());
        });
        return ui;
    }

    $("#sortable").sortable({
        items: "tr.sortable-tr",
        start: (event, ui) => {
            ui.placeholder.height(ui.helper.outerHeight());
        },
        helper: fixPlaceHolderWidth,
        update: (event, ui) => {
            console.log($("#sortable").sortable("toArray"));
        },
    });
});
