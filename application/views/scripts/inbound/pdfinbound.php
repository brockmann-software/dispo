<?php
function getTextWidth($text, $font, $font_size) {
    $drawing_text = iconv('', 'UTF-8', $text);
    $characters    = array();
    for ($i = 0; $i < strlen($drawing_text); $i++) {
        $characters[] = ord ($drawing_text[$i]);
    }
    $glyphs        = $font->glyphNumbersForCharacters($characters);
    $widths        = $font->widthsForGlyphs($glyphs);
    $text_width   = (array_sum($widths) / $font->getUnitsPerEm()) * $font_size;
    return $text_width;
}

$inbound_protokoll = Zend_Pdf::load($this->pathToTemplate);
$page = $inbound_protokoll->pages[0];
$fontBold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
$fontNormal = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
$page->setFont($fontNormal, 12);
$page->drawText($this->inbound_line['vendor_name'], 22, 730, 'utf-8');
$page->drawText($this->inbound_line['vendor_street'], 22, 716, 'utf-8');
$page->drawText($this->inbound_line['vendor_PO_code'].' '.$this->inbound_line['vendor_city'], 22, 702, 'utf-8');
$page->drawText($this->inbound_line['vendor_no'], 349, 730, 'utf-8');
$page->drawText($this->inbound_line['purchase_order'], 414, 730, 'utf-8');
$page->drawText($this->inbound_line['position'], 476, 730, 'utf-8');
$page->drawText($this->inbound_line['sales_order'], 349, 686, 'utf-8');
$page->drawText($this->inbound_line['v_delivery_note'], 476, 686, 'utf-8');
$page->drawText($this->inbound_line['inb_forwarder'], 22, 642, 'utf-8');
$page->drawText($this->inbound_line['inb_truck'], 150, 642, 'utf-8');
$page->drawText($this->inbound_line['inb_trailor'], 236, 642, 'utf-8');
$page->drawText($this->inbound_line['inb_container'], 349, 642, 'utf-8');
$page->drawText($this->inbound_line['po_arrival_date'].' '.$this->inbound_line['po_arrival_time'], 22, 598, 'utf-8');
$page->drawText($this->inbound_line['inb_arrival_date'].' '.$this->inbound_line['inb_arrival_time'], 150, 598, 'utf-8');
$page->drawText("{$this->inbound_line['po_transport_temp_min']}°C - {$this->inbound_line['po_transport_temp_max']}°C", 349, 598, 'utf-8');
$page->drawText("{$this->inbound_line['inb_transport_temp']}°C", 476, 598, 'utf-8');
$page->drawText($this->inbound_line['product_desc'], 150, 556, 'utf-8');
$page->drawText("{$this->inbound_line['items']} x {$this->inbound_line['weight_item']}g", 150, 541, 'utf-8');
$page->drawText($this->inbound_line['packaging'], 150, 526, 'utf-8');
$page->drawText($this->inbound_line['t_packaging'], 150, 511, 'utf-8');
$page->drawText($this->inbound_line['label'], 150, 496, 'utf-8');
$text = $this->inbound_line['po_trading_units'];
$twidth = getTextWidth($text, $fontNormal, 12);
$page->drawText($text, 150-$twidth, 438, 'utf-8');
$text = $this->inbound_line['po_packing_units'];
$twidth = getTextWidth($text, $fontNormal, 12);
$page->drawText($text, 150-$twidth, 424, 'utf-8');
$text = $this->inbound_line['po_pallets'];
$twidth = getTextWidth($text, $fontNormal, 12);
$page->drawText($text, 150-$twidth, 410, 'utf-8');
$text = $this->inbound_line['inb_trading_units'];
$twidth = getTextWidth($text, $fontNormal, 12);
$page->drawText($text, 350-$twidth, 438, 'utf-8');
$text = $this->inbound_line['inb_packing_units'];
$twidth = getTextWidth($text, $fontNormal, 12);
$page->drawText($text, 350-$twidth, 424, 'utf-8');
$text = $this->inbound_line['inb_pallets'];
$twidth = getTextWidth($text, $fontNormal, 12);
$page->drawText($text, 350-$twidth, 410, 'utf-8');
// draw table for qc_log
$count_qc_log = count($this->qc_inb_lines_log);
//$page->drawLine(20, 350, 575, 350)   // HEAD Bottom
//->drawLine(20, 350, 20, (350-(($count_qc_log+1)*14)))    // MOST Left
//->drawLine(575, 350, 575, (350-(($count_qc_log+1)*14)))  // MOST Right
//->drawLine(20, (350-(($count_qc_log+1)*14)), 575, (350-(($count_qc_log+1)*14)));       // MOST Bottom
$headerColor = new Zend_Pdf_Color_HTML('#8CB73C');
$headerTextColor = new Zend_Pdf_Color_HTML('black');
$page->setFillColor($headerColor);
$page->drawRectangle(20,350, 160, 336);
$page->drawRectangle(160, 350, 230, 336);
$page->drawRectangle(230, 350, 300, 336);
$page->drawRectangle(300, 350, 575, 336);
$page->setFont($fontBold, 12);
$page->setFillColor($headerTextColor);
$page->drawText('Prüfung', 22, 339, 'utf-8');
$page->drawText('Ergebnis', 162, 339, 'utf-8');
$page->drawText('Abweichung', 232, 339, 'utf-8');
$page->drawText('Bemerkungen', 302, 339, 'utf-8');
$line = 0;
foreach($this->qc_inb_lines_log as $qc_inb_log) {
	$line++;
	$cellColor = new Zend_Pdf_Color_HTML('white');
	$page->setFillColor($cellColor);
	$page->drawRectangle(20,350-($line*14), 160, 336-($line*14));
	$page->drawRectangle(160, 350-($line*14), 230, 336-($line*14));
	$page->drawRectangle(300, 350-($line*14), 575, 336-($line*14));
	switch ($qc_inb_log['res_level']) {
		case 'check_green' : $levelColor = new Zend_Pdf_Color_HTML('green'); break;
		case 'check_yellow' : $levelColor = new Zend_Pdf_Color_HTML('yellow'); break;
		case 'check_red' : $levelColor = new Zend_Pdf_Color_HTML('red'); break;
	}
	$page->setFillColor($levelColor);
	$page->drawRectangle(230, 350-($line*14), 300, 336-($line*14));
	$page->setFont($fontNormal, 12);
	$page->setFillColor($headerTextColor);
	$page->drawText($qc_inb_log['quality_checkpoint'], 22, 339-($line*14), 'utf-8');
	$page->drawText($qc_inb_log['value'], 162, 339-($line*14), 'utf-8');
	$page->drawText($qc_inb_log['res_percent'], 232, 339-($line*14), 'utf-8');
}
$qc_start = 310-($line*14);
$qc_no = 0;
foreach($this->qc_classes as $qc_class) {
	if ($qc_class['No']<4) {
		$headerColor = new Zend_Pdf_Color_HTML('#8CB73C');
		$headerTextColor = new Zend_Pdf_Color_HTML('black');
		$page->setFillColor($headerColor);
		$page->drawRectangle(20+($qc_no*185), $qc_start, 120+($qc_no*185), $qc_start-14);
		$page->drawRectangle(120+($qc_no*185), $qc_start, 160+($qc_no*185), $qc_start-14);
		$page->drawRectangle(160+($qc_no*185), $qc_start, 205+($qc_no*185), $qc_start-14);
		$page->setFont($fontBold, 12);
		$page->setFillColor($headerTextColor);
		$page->drawText('Prüfung', 22+($qc_no*185), $qc_start-11, 'utf-8');
		$page->drawText('Erg.', 122+($qc_no*185), $qc_start-11, 'utf-8');
		$page->drawText('Abw', 162+($qc_no*185), $qc_start-11, 'utf-8');
		$line = 0;
		foreach ($this->qc_inb_lines_quality as $qcheckpoint) {
			if ($qcheckpoint['qc_class_no'] == $qc_class['No']) {
				$line++;
				$cellColor = new Zend_Pdf_Color_HTML('white');
				$page->setFillColor($cellColor);
				$page->drawRectangle(20+($qc_no*185), $qc_start-($line*14), 120+($qc_no*185), ($qc_start-14)-($line*14));
				$page->drawRectangle(120+($qc_no*185), $qc_start-($line*14), 160+($qc_no*185), ($qc_start-14)-($line*14));
				switch ($qcheckpoint['res_level']) {
					case 'check_green' : $levelColor = new Zend_Pdf_Color_HTML('green'); break;
					case 'check_yellow' : $levelColor = new Zend_Pdf_Color_HTML('yellow'); break;
					case 'check_red' : $levelColor = new Zend_Pdf_Color_HTML('red'); break;
				}
				$page->setFillColor($levelColor);
				$page->drawRectangle(160+($qc_no*185), $qc_start-($line*14), 205+($qc_no*185), ($qc_start-14)-($line*14));
				$page->setFont($fontNormal, 12);
				$page->setFillColor($headerTextColor);
				$page->drawText($qcheckpoint['quality_checkpoint'], 22+($qc_no*185), ($qc_start-11)-($line*14), 'utf-8');
				$page->drawText($qcheckpoint['value'], 122+($qc_no*185), ($qc_start-11)-($line*14), 'utf-8');
				$page->drawText($qcheckpoint['res_percent'], 162+($qc_no*185), ($qc_start-11)-($line*14), 'utf-8');
			}
		}
		$qc_no++;
	}
}
$page->drawText($this->inbound_line['remarks'], 22, 114, 'utf-8');
$page->drawText('Kontrolliert von: '.$this->inbound_line['checked_by'], 22, 60, 'utf-8');
$img_count = count($this->pictures);
$picPerPage = 0;
Zend_Registry::get('logger')->info('Attachments: '.print_r($this->attachments, true));
$extractor = new Zend_Pdf_Resource_Extractor();
try {
	foreach ($this->attachments as $attachment) {
		$attPdf = Zend_Pdf::load($this->att_path.'/'.$attachment['path']);
		foreach ($attPdf->pages as $att_page) {
			$inbound_protokoll->pages[] = $extractor->clonePage($att_page);
		}	
	}
} catch (Exception $e) {
	Zend_Registry::get('logger')->info('Fehler beim laden vom Anhang! '.$e->getMessage());
}
Zend_Registry::get('logger')->info('Pictures: '.print_r($this->pictures, true));
foreach ($this->pictures as $image) {
	$picPerPage++;
	if ($picPerPage==1) {
		$newPage = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
		$inbound_protokoll->pages[] = $newPage;
	}
	$image_attr = getimagesize($this->picture_path.'/'.$image['path']);
	$img_relation = $image_attr[0]/$image_attr[1];
	Zend_Registry::get('logger')->info('Image Path: '.print_r($this->picture_path.'/'.$image['path'], true));
	Zend_Registry::get('logger')->info('Image Attr.:'.print_r($image_attr, true));
	if ($img_relation<1) {
		$height = 381;
		$width = $height * $img_relation;
	} else {
		$width = 268;
		$height = $width * $img_relation;
	}
	$cur_image = Zend_Pdf_image::imageWithPath($this->picture_path.'/'.$image['path']);
	switch ($picPerPage) {
		case 1 : $x1=20; $y1=20; break;
		case 2 : $x1=20; $y1=308; break;
		case 3 : $x1=401; $y1=20; break;
		case 4 : $x1=401; $y1=308; break;
	}
	Zend_Registry::get('logger')->info("Position: {$x1}:{$y1}");
	$newPage->drawImage($cur_image, $x1, $y1, $x1+round($height), $y1+round($width));
	if ($picPerPage == 4) $picPerPage = 0;
}
echo $inbound_protokoll->render();
