<?php
$url_self  = $this->Html->url("/".$self_dir, true);
$url_files = WWW_ROOT.'files'.DS;
$url_js    = $this->Html->url("/js", true);

$months = array();
for ($i=1; $i <= 12; $i++) {
	$val = $i;
	$months[$val] = str_pad($val, 2, "0", STR_PAD_LEFT);
}

require_once(APP.'Vendor'.DS.'tcpdf/tcpdf.php');
require_once(APP.'Vendor'.DS.'tcpdf/fpdi/autoload.php');

// $pdf  = new setasign\Fpdi\Tcpdf\Fpdi();
// $font = new TCPDF_FONTS();

if (!file_exists($url_files.'estimates/'.$header['Estimate']['estimate_no'].'.pdf')) {

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->setSourceFile($url_files.'templates.pdf');
    $pdf->AddPage();
    $tpl = $pdf->importPage(1);
    $pdf->useTemplate($tpl);

    $default_font = $font->addTTFfont($url_files.'fonts/GenShinGothic_Medium.ttf');
    $bold_font = $font->addTTFfont($url_files.'fonts/GenShinGothic_Bold.ttf');
    $line_style = array( 'width' => 0.32, 'color' => array(0, 0, 0) );

    // タイトル
    $pdf->SetFont($default_font, '', 24);
    $pdf->Text(90, 28, '見積書');

    // 日付・番号
    $pdf->SetFont($bold_font, '', 11);
    $pdf->Text(167.8, 8.72, htmlspecialchars( date('Y年m月d日', strtotime($header['Estimate']['issued_date'])) ) );
    $pdf->Text(153.5, 16.5, '見積番号: '.htmlspecialchars( $header['Estimate']['estimate_no'] ) );

    // 宛先
    $pdf->SetFont($bold_font, '', 14);
    $line_width = $pdf->GetStringWidth( htmlspecialchars( empty($header['Estimate']['responsible']) ? $header['Estimate']['customer_id'].' '.$header['Estimate']['honor_title'] : $header['Estimate']['customer_id'].' '.$header['Estimate']['responsible'].' '.$header['Estimate']['honor_title'] ), $default_font, '', 14 );
    $pdf->Text(12, 49.8, htmlspecialchars( empty($header['Estimate']['responsible']) ? $header['Estimate']['customer_id'].' '.$header['Estimate']['honor_title'] : $header['Estimate']['customer_id'].' '.$header['Estimate']['responsible'].' '.$header['Estimate']['honor_title'] ) );
    $pdf->Line(13, 55.8, (13 + $line_width), 55.8, $line_style);
    $pdf->SetFont($default_font, '', 12);
    $pdf->Text(12, 57, $header['Estimate']['title']);
    $pdf->SetFont($default_font, '', 10);
    $pdf->Text(12, 62, '下記の通りお見積もり申し上げます。');

    // 金額
    $pdf->SetFont($default_font, '', 14);
    $pdf->Text(12, 70, 'お見積金額');
    $price = '¥ ' . number_format($header['Estimate']['price']) . ' -';
    $pdf->SetXY(23, 70);
    $pdf->Cell(70, 0, htmlspecialchars( $price ), 0, 0, 'R' );
    $pdf->Line(13, 77, (13 + 80), 77, $line_style);

    // 会社情報
    $pdf->SetFont($default_font, '', 9.5);
    $pdf->Text(112.5, 64, htmlspecialchars( $company['Company']['company_name'] ) );
    $pdf->Text(112.5, 75.5, '〒' . htmlspecialchars( $company['Company']['zipcode'] ) );
    $pdf->Text(112.5, 80.5, htmlspecialchars( $company['Company']['address1'] ) );
    $pdf->Text(112.5, 85.5, htmlspecialchars( $company['Company']['address2'] ) );
    $pdf->Text(112.5, 95.5, htmlspecialchars( 'TEL:'.$company['Company']['tel_no'] ) );
    $pdf->Image($url_files.$company['Company']['logo'], 114, 50, 58, 0, '', '', 'T', false, 300, '', false, false, false, false, false, false);
    $pdf->Image($url_files.$company['Company']['company_stamp'], 175, 64.5, 22, 0, '', '', 'T', false, 300, '', false, false, 1, false, false, false);

    // デフォルトセル幅・Yポジション
    $cellW = 0;
    $cellH = 8.85;
    $posY  = 118;

    // 税区分毎の合計金額
    $taxTotal = 0;
    $tax10p   = 0;
    $tax8sp   = 0;
    $tax8p    = 0;
    $tax5p    = 0;

    // 表ヘッダー
    $pdf->SetXY(12, $posY - $cellH);
    $pdf->SetFont($bold_font, '', 11);
    $pdf->SetFillColor(212, 210, 211);
    $pdf->Cell(110, $cellH, '品番・品名', 1, 0, 'C', 1);
    $pdf->Cell(20, $cellH, '数量', 1, 0, 'C', 1);
    $pdf->Cell(27, $cellH, '単価', 1, 0, 'C', 1);
    $pdf->Cell(30, $cellH, '金額', 1, 0, 'C', 1);

    // 内容
    $pdf->SetFont($default_font, '', 12);

    // 最低10行は表示する
    if (count($details) <= 10) {

        for ($row = 0; $row < count($details); $row++) {

            $pdf->SetXY(12, $posY);
            $pdf->Cell(110, $cellH, htmlspecialchars($details[$row]['EstimateDetail']['item_name']), 1, 0, 'L');
            $pdf->Cell(20, $cellH, htmlspecialchars($details[$row]['EstimateDetail']['qty'].$details[$row]['EstimateDetail']['unit']), 1, 0, 'R');
            $pdf->Cell(27, $cellH, htmlspecialchars(number_format($details[$row]['EstimateDetail']['cost'])), 1, 0, 'R');
            $pdf->Cell(30, $cellH, htmlspecialchars(number_format($details[$row]['EstimateDetail']['qty'] * $details[$row]['EstimateDetail']['cost'])), 1, 0, 'R');
            $taxTotal += $details[$row]['EstimateDetail']['tax'];
            switch ($details[$row]['EstimateDetail']['tax_id']) {
                case 1: $tax10p += $details[$row]['EstimateDetail']['cost']; break;
                case 2: $tax8sp += $details[$row]['EstimateDetail']['cost']; break;
                case 3: $tax8p  += $details[$row]['EstimateDetail']['cost']; break;
                case 5: $tax5p  += $details[$row]['EstimateDetail']['cost']; break;
                default: break;
            }
            if ($posY > 260) {
                $posY = 18.85;
            } else if($posY > 206.4) {
                //$pdf->AddPage();
                //$posY = 18.85;
                $posY += $cellH;
            } else {
                $posY += $cellH;
            }

        }

        $remRows = 10 - count($details);

        // 残りの行を印刷する
        for ($row = 0; $row < $remRows; $row++) {

            $pdf->SetXY(12, $posY);
            $pdf->Cell(110, $cellH, '', 1, 0, 'L');
            $pdf->Cell(20, $cellH, '', 1, 0, 'R');
            $pdf->Cell(27, $cellH, '', 1, 0, 'R');
            $pdf->Cell(30, $cellH, '', 1, 0, 'R');

            if ($posY > 260) {
                $posY = 18.85;
            } else if($posY > 206.4) {
                //$pdf->AddPage();
                //$posY = 18.85;
                $posY += $cellH;
            } else {
                $posY += $cellH;
            }

        }

    } else {

        foreach ($details as $detail) {
            $pdf->SetXY(12, $posY);
            $pdf->Cell(110, $cellH, htmlspecialchars($detail['EstimateDetail']['item_name']), 1, 0, 'L');
            $pdf->Cell(20, $cellH, htmlspecialchars($detail['EstimateDetail']['qty'].$detail['EstimateDetail']['unit']), 1, 0, 'R');
            $pdf->Cell(27, $cellH, htmlspecialchars(number_format($detail['EstimateDetail']['cost'])), 1, 0, 'R');
            $pdf->Cell(30, $cellH, htmlspecialchars(number_format($detail['EstimateDetail']['qty'] * $detail['EstimateDetail']['cost'])), 1, 0, 'R');
            $taxTotal += $detail['EstimateDetail']['tax'];
            switch ($detail['EstimateDetail']['tax_id']) {
                case 1: $tax10p += $detail['EstimateDetail']['cost']; break;
                case 2: $tax8sp += $detail['EstimateDetail']['cost']; break;
                case 3: $tax8p  += $detail['EstimateDetail']['cost']; break;
                case 5: $tax5p  += $detail['EstimateDetail']['cost']; break;
                default: break;
            }
            if ($posY > 260) {
                $posY = 18.85;
            } else if($posY > 206.4) {
                //$pdf->AddPage();
                //$posY = 18.85;
                $posY += $cellH;
            } else {
                $posY += $cellH;
            }

        }

    }

    // 小計
    $pdf->SetXY(122, $posY);
    $pdf->Cell(47, $cellH, '小計', 1, 0, 'L');
    $pdf->Cell(30, $cellH, number_format($header['Estimate']['price'] - $taxTotal), 1, 0, 'R');
    $posY += $cellH;

    // 消費税
    $pdf->SetXY(122, $posY);
    $pdf->Cell(47, $cellH, '消費税', 1, 0, 'L');
    $pdf->Cell(30, $cellH, number_format($taxTotal), 1, 0, 'R');
    $posY += $cellH;

    // 合計
    $pdf->SetXY(122, $posY);
    $pdf->Cell(47, $cellH, '合計', 1, 0, 'L');
    $pdf->Cell(30, $cellH, number_format($header['Estimate']['price']), 1, 0, 'R');
    $posY += $cellH;

    // 備考
    $pdf->SetXY(12, $posY + 2);
    $pdf->SetFont($default_font, '', 11);
    $pdf->MultiCell(108, 0, htmlspecialchars($header['Estimate']['remarks']), 0, 'L');

    // 出力
    $pdf->Output($url_files.'estimates/'.$header['Estimate']['estimate_no'].'.pdf', 'F');

}
?>

<style>
/* --- 情報ボックス --- */
.info_box {
    display: flex;
}

/* --- メモ --- */
.note_form {
    width: 60%;
    margin: 0;
}
.note_form_area {
    float: right;
}
.form_input {
    font-size: 16px;
    background-color: #fff;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
    display: block;
    color: #162533;
    border: 1px solid #c8cfd7;
    padding: 8px 12px;
    width: 350px;
    height: 105px;
    box-sizing: border-box;
    transition: .2s;
}
.form_input:focus {
    outline: 0;
    border-color: #56ccdb;
}

/* --- 見積書情報 --- */
.estimate_info {
    padding: 10px;
    margin: 10px 0;
    width: 40%;
    line-height: 1.8;
}
.estimate_info dt {
    float: left;
}
.estimate_info dd {
    margin-left: 10px;
    padding-left: 130px;
}

/* --- プレビュー --- */
iframe {
    border-radius: .4em;
    border: solid 1px #eee;
}
</style>

<div style="display: flex; justify-content: space-between;">
    <a href="<?= $url_self;?>" class="btn btn-secondary"><i class="fa fa-reply"></i> 戻る</a>
    <div>
        <a class="btn btn-secondary" id="edit_button"><i class="fa fa-pencil-square-o"></i> 編集</a>
        <a class="btn btn-secondary" id="copy_button"><i class="fa fa-files-o"></i> 複製</a>
        <?php if (!$header['Estimate']['cvt_flg'] == 1) { echo '<a class="btn btn-secondary" id="convert_button"><i class="fa fa-refresh"></i> 変換</a>'; }; ?>
        <a class="btn btn-secondary" id="del_button"><i class="fa fa-trash-o"></i> <?= empty($header['Estimate']['del_flg']) ? '' : '完全に' ;?>削除</a>
        <a href="../../files/estimates/<?= $header['Estimate']['estimate_no']; ?>.pdf" download class="btn btn-secondary issue_pull" id="add_button"><i class="fa fa-download"></i> ダウンロード</a>
    </div>
</div>

<div class="info_box">
    <dl class="estimate_info">
        <dt>見積番号</dt>
        <dd><?= $header['Estimate']['estimate_no'];?><?= empty($header['Estimate']['del_flg']) ? '' : '（削除済み）';?></dd>
        <dt>取引先</dt>
        <dd><?= empty($header['Estimate']['responsible']) ? $header['Estimate']['customer_id'].' '.$header['Estimate']['honor_title'] : $header['Estimate']['customer_id'].' '.$header['Estimate']['responsible'].' '.$header['Estimate']['honor_title']; ?></dd>
        <dt>件名</dt>
        <dd><?= !empty($header['Estimate']['title']) ? $header['Estimate']['title'] : '-' ; ?></dd>
        <dt>見積金額</dt>
        <dd><?= number_format($header['Estimate']['price']); ?>円</dd>
        <dt>発行日</dt>
        <dd><?= date('Y/m/d', strtotime($header['Estimate']['issued_date'])); ?></dd>
        <dt>有効期限</dt>
        <dd><?= !empty($header['Estimate']['exp_date']) ? date('Y/m/d', strtotime($header['Estimate']['exp_date'])) : '-';?></dd>
    </dl>
    <div class="note_form">
        <div class="note_form_area">
            <form method="post">
                <textarea class="form_input" placeholder="社内メモ" name="note" id="noteBox" spellcheck="false"><?= $header['Estimate']['note']; ?></textarea>
                <button type="submit" class="btn btn-secondary" id="saveNote" style="margin: 3px 0;">メモを保存</button>
            </form>
        </div>
    </div>
</div>

<!-- --- --- 見積書プレビュー --- --- -->
<iframe id="pdfFrame" src="../../files/estimates/<?= $header['Estimate']['estimate_no']; ?>.pdf" frameborder="0" width="100%" height="100%" toolbar="1"></iframe>

<script>
$(function(){

    $('#edit_button').on('click', function(event) {
        location.href="../edit/<?= $header['Estimate']['estimate_no'];?>";
    });

    // 複製
    $('#copy_button').on('click', function(event) {
        location.href="../copy/<?= $header['Estimate']['estimate_no'];?>";
    });

    // 変換
    $('#convert_button').on('click', function(event) {
        if (confirm('請求書に変換します。')) {
            location.href="../convert/<?= $header['Estimate']['estimate_no'];?>";
        }
    });

    $('#del_button').on('click', function(event) {
		if (confirm('削除します。よろしいですか？')) {
            location.href="../delete/<?= $header['Estimate']['estimate_no'];?>";
        }
	});

});

$(document).ready(function() {

    $('#saveNote').click(function() {
        $.ajax({
            type: 'POST',
            datatype:'json',
            url: '<?= $url_self;?>/saveNote',
            data: {
                txt: $('#noteBox').val(),
                num: '<?= $header['Estimate']['estimate_no'];?>'
            },
            success: function(data, dataType)
            {
                alert('保存しました');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown)
            {
                alert('Error : ' + errorThrown);
            }
        });
        return false;
    });

    // iframeのキャッシュ対策
    $('#pdfFrame').each(function() {
        this.contentWindow.location.reload(true);
    });
});
</script>
