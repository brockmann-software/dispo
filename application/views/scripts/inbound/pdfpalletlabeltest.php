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
	$palletlabel = new Zend_Pdf();
	for ($p = 0; $p<$this->data['pallets']; $p++) {
		$page = new Zend_Pdf_Page(283, 425);
		$page->setFont($fontBold, 35);
		$textwidth = getTextWidth($this->data['vendor_name'], $fontBold, 35);
		$page->drawText($this->data['vendor_name'], (283-$textwidth)/2, 375, 'utf-8');
		$textwidth = getTextWidth("{$this->variant['origin']} {$this->variant['product']}", $fontBold, 35);
		$page->drawText("{$this->variant['origin']} {$this->variant['product']}", (283-$textwidth)/2, 275, 'utf-8');
		$textwidth = getTextWidth("{$this->data['items']}X{$this->data['weight_item']}g {$this->variant['class_short']} {$this->variant['label_short']}", $fontBold, 35);
		$page->drawText("{$this->data['items']}X{$this->data['weight_item']}g {$this->variant['class_short']} {$this->variant['label_short']}", (283-$textwidth)/2, 225, 'utf-8');
		if ($this->data['lot'] == '') $this->data['lot'] = 'OLN';
		$textwidth = getTextWidth("{$this->variant['brand']} {$this->data['lot']}", $fontBold, 35);
		$page->drawText("{$this->variant['brand']} {$this->data['lot']}", (283-$textwidth)/2, 185, 'utf-8');
		$page->setFont($fontBold, 55);
		$textwidth = getTextWidth('P '.$this->data['position'], $fontBold, 55);
		$page->drawText('P '.$this->data['position'], (283-$textwidth)/2, 105, 'utf-8');
		$textwidth = getTextWidth(date('d.m.Y'), $fontBold, 55);
		$page->drawText(date('d.m.Y'), (283-$textwidth)/2, 35, 'utf-8');
//		Zend_Barcode::factory('Ean8', 'pdf', array('text'=>$this->data['position'], 'font' => realpath(APPLICATION_PATH.'/../public/fonts/arial.ttf')), array('topOffset'=>80, 'leftOffset'=>10))->setResource($palletlabel, $p)->draw();
		$palletlabel->pages[] = $page;
	}
	echo $palletlabel->render();
} catch (Exception $e) {
	Zend_Registry::get('logger')->err(print_r($e, true));
}