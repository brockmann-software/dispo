<?php
$fontBold = Zend_Pdf_Font::fontWithPath(realpath(APPLICATION_PATH.'/../public/fonts/').'/compact_bold.ttf');
$fontNormal = Zend_Pdf_Font::fontWithPath(realpath(APPLICATION_PATH.'/../public/fonts/').'/compact.ttf');
$textColor = new Zend_Pdf_Color_HTML('black');
try {
	$palletlabel = new My_Pdf();
	for ($p = 0; $p<$this->data['pallets']; $p++) {
		$page = new My_Pdf_Page(283, 431);
		$page->setFont($fontBold, 45);
		$page->drawText($this->data['vendor_name'], 283/2, 50, 'utf-8', true, My_Pdf::CENTER);
		$page->setFont($fontBold, 45);
		$page->drawText("{$this->variant['origin']} {$this->variant['product']}", round(283/2), 120, 'utf-8', true, My_Pdf::CENTER);
		$page->drawText("{$this->data['items']}X{$this->data['weight_item']}g {$this->variant['class_short']} {$this->variant['label_short']}", round(283/2), 170, 'utf-8', true, My_Pdf::CENTER);
		if ($this->data['lot'] == '') $this->data['lot'] = 'OLN';
		$text = $page->drawText($this->data['lot'], 283/2, 220, 'utf-8', true, My_Pdf::CENTER);
		$page->drawText($this->variant['brand'], round(283/2), 270, 'utf-8', true, My_Pdf::CENTER);
		$page->setFont($fontBold, 70);
		$page->drawText('P '.$this->data['position'], round(283/2), 350, 'utf-8', true, My_Pdf::CENTER);
		$page->drawText(date('d.m.Y'), round(283/2), 415, 'utf-8', true, My_Pdf::CENTER);
//		Zend_Barcode::factory('Ean8', 'pdf', array('text'=>$this->data['position'], 'font' => realpath(APPLICATION_PATH.'/../public/fonts/arial.ttf')), array('topOffset'=>80, 'leftOffset'=>10))->setResource($palletlabel, $p)->draw();
		$palletlabel->pages[] = $page;
	}
	echo $palletlabel->render();
} catch (Exception $e) {
	Zend_Registry::get('logger')->err(print_r($e, true));
}