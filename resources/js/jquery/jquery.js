const $ = require("jquery");
require("jquery-ui/ui/widgets/sortable");

$(() => {
    // 計算処理
    $(".document-table").on("input", ".calc", () => {
        let total = {
            sub: 0,
            tax: 0,
            all: 0,
        };
        for (let i = 0; i < $(".main_tbody").children().length; i++) {
            if ($(`#qty_${i}`).val() !== "" || $(`#cost_${i}`).val() !== "") {
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
        $("#subtotal").val(total.sub);
        $("#taxTotal").val(total.tax);
        $("#totalPrice").val(total.all);
    });

    // 行を削除
    $(".document-table").on("click", ".delete-row-button", (event) => {
        $(event.target.closest("tr")).remove();
        lines = $(".main_tbody").children().length;
        lines -= 1;
        rowRemain.innerHTML = "(残り" + (29 - lines) + "行)";
        // $(".table-input").change(); // 計算処理onchange発火用
    });

    // セルの横幅を固定したままSortable
    function fixPlaceHolderWidth(event, ui) {
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
