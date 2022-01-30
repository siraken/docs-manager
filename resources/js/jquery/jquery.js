const $ = require("jquery");
require("jquery-ui/ui/widgets/sortable");

$(() => {
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
