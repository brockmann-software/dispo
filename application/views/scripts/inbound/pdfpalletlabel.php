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

$fontBold = Zend_Pdf_Font::fontWithPath(realpath(APPLICATION_PATH.'/../public/fonts/').'/ARIALBD.ttf');
$fontNormal = Zend_Pdf_Font::fontWithPath(realpath(APPLICATION_PATH.'/../public/fonts/').'/arial.ttf');
$textColor = new Zend_Pdf_Color_HTML('black');
try {
	$palletlabel = new My_Pdf();
	for ($p = 0; $p<$this->data['pallets']; $p++) {
		$page = new My_Pdf_Page(283, 425);
		$page->setFont($fontBold, 35);
		$text = $page->getTextProperties($this->data['vendor_name'], 283);
		$page->drawText($text['lines'][0], 283/2, 50, 'utf-8', true, My_Pdf::CENTER);
		if (isset($text['lines'][1])) $page->drawText($text['lines'][1], 283/2, 100, 'utf-8', true, My_Pdf::CENTER);
		$page->drawText("{$this->variant['origin']} {$this->variant['product']}", 283/2, 150, 'utf-8', true, My_Pdf::CENTER);
		$page->drawText("{$this->data['items']}X{$this->data['weight_item']}g {$this->variant['class_short']} {$this->variant['label_short']}", 283/2, 200, 'utf-8', true, My_Pdf::CENTER);
		if ($this->data['lot'] == '') $this->data['lot'] = 'OLN';
		$page->drawText("{$this->variant['brand']} {$this->data['lot']}", 283/2, 250, 'utf-8', true, My_Pdf::CENTER);
		$page->setFont($fontBold, 55);
		$page->drawText('P '.$this->data['position'], 283/2, 320, 'utf-8', true, My_Pdf::CENTER);
		$page->drawText(date('d.m.Y'), 283/2, 390, 'utf-8', true, My_Pdf::CENTER);
//		Zend_Barcode::factory('Ean8', 'pdf', array('text'=>$this->data['position'], 'font' => realpath(APPLICATION_PATH.'/../public/fonts/arial.ttf')), array('topOffset'=>80, 'leftOffset'=>10))->setResource($palletlabel, $p)->draw();
		$palletlabel->pages[] = $page;
	}
	echo $palletlabel->render();
} catch (Exception $e) {
	Zend_Registry::get('logger')->err(print_r($e, true));
}