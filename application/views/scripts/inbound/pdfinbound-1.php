<?php
// Schriften festlegen
$fontBold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
$fontNormal = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);

// Übersetzung laden und Spache festlegen

// Seitenränder und Seite festlegen
$margin = array(
	My_Pdf::LEFT=>	array(0.6, 'cm'),
	My_Pdf::TOP=>	array(0.6, 'cm'),
	My_Pdf::RIGHT=>	array(0.6, 'cm'),
	My_Pdf::BOTTOM=>array(0.6, 'cm'));
$page = new My_Pdf_Page(Zend_Pdf_Page::SIZE_A4, 'utf-8');
$page->setMargins($margin);

//Label
$page->drawImage(Zend_Pdf_image::imageWithPath(APPLICATION_PATH.'/../public/images/logo_mvs.jpg'), array(14.8, 'cm'), 0, array(5, 'cm'));
$page->setFont($fontBold, 20);
$page->drawText($this->translate->_('@header').' '.$inbound_line->getData()['No'], array(0, 'cm'), array(0.6, 'cm'));
if ($inbound_line->getData()['attribute_no']) $page->drawText($inbound_line->getData()['attribute'], array(10, 'cm'), array(0.6, 'cm'));
$page->setFont($fontBold, 11);
$page->drawText($this->translate->_('@vendor'), array(0, 'cm'), array(2.4, 'cm'));
$page->drawText($this->translate->_('@vendor_no'), array(11.5, 'cm'), array(2.4, 'cm'));
$page->drawText($this->translate->_('@order_no'), array(13.81, 'cm'), array(2.4, 'cm'));
$page->drawText($this->translate->_('@position'), array(16.12, 'cm'), array(2.4, 'cm'));

$page->drawText($this->translate->_('@sales_order_no'), array(11.5, 'cm'), array(3.42, 'cm'));
$page->drawText($this->translate->_('@v_delivery_note'), array(16.12, 'cm'), array(3.42, 'cm'));

$page->drawText($this->translate->_('@forwarder'), array(0, 'cm'), array(4.44, 'cm'));
$page->drawText($this->translate->_('@truck'), array(4.62, 'cm'), array(4.44, 'cm'));
$page->drawText($this->translate->_('@trailer'), array(7.43, 'cm'), array(4.44, 'cm'));
$page->drawText($this->translate->_('@container'), array(11.5, 'cm'), array(4.44, 'cm'));

$page->drawText($this->translate->_('@arrival_sched'), array(0, 'cm'), array(5.46, 'cm'));
$page->drawText($this->translate->_('@arrival'), array(4.62, 'cm'), array(5.46, 'cm'));
$page->drawText($this->translate->_('@temp_req'), array(11.5, 'cm'), array(5.46, 'cm'));
$page->drawText($this->translate->_('@temperature'), array(16.12, 'cm'), array(5.46, 'cm'));

$page->drawText($this->translate->_('@product'), array(0, 'cm'), array(6.99, 'cm'));
$page->drawText($this->translate->_('@origin'), array(0, 'cm'), array(8.01, 'cm'));
$page->drawText($this->translate->_('@confection'), array(0, 'cm'), array(8.52, 'cm'));
$page->drawText($this->translate->_('@packaging'), array(0, 'cm'), array(9.03, 'cm'));
$page->drawText($this->translate->_('@embalage'), array(0, 'cm'), array(9.54, 'cm'));
$page->drawText($this->translate->_('@label'), array(0, 'cm'), array(10.05, 'cm'));

$page->drawText($this->translate->_('@ordered'), array(4.62, 'cm'), array(11.07, 'cm'));
$page->drawText($this->translate->_('@delivered'), array(7.43, 'cm'), array(11.07, 'cm'));
$page->drawText($this->translate->_('@trading_units'), array(0, 'cm'), array(11.58, 'cm'));
$page->drawText($this->translate->_('@packing_units'), array(0, 'cm'), array(12.09, 'cm'));
$page->drawText($this->translate->_('@pallets'), array(0, 'cm'), array(12.60, 'cm'));

$page->drawText($this->translate->_('@items_checked'), array(0, 'cm'), array(13.62, 'cm'));

// Daten
$page->setFont($fontNormal, 11);

$page->drawText($inbound_line->getData()['vendor_name'], array(0, 'cm'), array(2.91, 'cm'));
$page->drawText($inbound_line->getData()['vendor_street'], array(0, 'cm'), array(3.41, 'cm'));
$page->drawText($inbound_line->getData()['vendor_PO_code'].' '.$inbound_line->getData()['vendor_city'], array(0, 'cm'), array(3.91, 'cm'));
$page->drawText($inbound_line->getData()['vendor_no'], array(11.5, 'cm'), array(2.91, 'cm'));
$page->drawText($inbound_line->getData()['purchase_order'], array(13.81, 'cm'), array(2.91, 'cm'));
$page->drawText($inbound_line->getData()['position'], array(16.12, 'cm'), array(2.91, 'cm'));
$page->drawText($inbound_line->getData()['sales_order'], array(11.5, 'cm'), array(3.93, 'cm'));
$page->drawText($inbound_line->getData()['v_delivery_note'], array(16.12, 'cm'), array(3.93, 'cm'));
$page->drawText($inbound_line->getData()['inb_forwarder'], array(0, 'cm'), array(4.95, 'cm'));
$page->drawText($inbound_line->getData()['inb_truck'], array(4.64, 'cm'), array(4.95, 'cm'));
$page->drawText($inbound_line->getData()['inb_trailor'], array(7.43, 'cm'), array(4.95, 'cm'));
$page->drawText($inbound_line->getData()['inb_container'], array(11.5, 'cm'), array(4.95, 'cm'));
$page->drawText($inbound_line->getData()['po_arrival_date'].' '.$inbound_line->getData()['po_arrival_time'], array(0, 'cm'), array(5.97, 'cm'));
$page->drawText($inbound_line->getData()['inb_arrival_date'].' '.$inbound_line->getData()['inb_arrival_time'], array(4.62, 'cm'), array(5.97, 'cm'));
$page->drawText("{$inbound_line->getData()['po_transport_temp_min']}°C - {$inbound_line->getData()['po_transport_temp_max']}°C", array(11.5, 'cm'), array(5.97, 'cm'));
$page->drawText("{$inbound_line->getData()['inb_transport_temp']}°C", array(16.12, 'cm'), array(5.97, 'cm'));
$page->drawText($inbound_line->getData()['product_local'], array(4.62, 'cm'), array(6.99, 'cm'));
$page->drawText($inbound_line->getData()['origin_long'], array(4.62, 'cm'), array(8.01, 'cm'));
$page->drawText("{$inbound_line->getData()['items']} x {$inbound_line->getData()['weight_item']}g", array(4.62, 'cm'), array(8.52, 'cm'));
$page->drawText($inbound_line->getData()['packaging_local'], array(4.62, 'cm'), array(9.03, 'cm'));
$page->drawText($inbound_line->getData()['t_packaging_local'], array(4.62, 'cm'), array(9.54, 'cm'));
$page->drawText($inbound_line->getData()['label'], array(4.62, 'cm'), array(10.03, 'cm'));

$page->drawText($inbound_line->getData()['po_trading_units'], array(7, 'cm'), array(11.58, 'cm'), '', true, My_Pdf::RIGHT);
$page->drawText($inbound_line->getData()['po_packing_units'], array(7, 'cm'), array(12.09, 'cm'), '', true, My_Pdf::RIGHT);
$page->drawText($inbound_line->getData()['po_pallets'], array(7, 'cm'), array(12.60, 'cm'), '', true, My_Pdf::RIGHT);
$page->drawText($inbound_line->getData()['inb_trading_units'], array(10, 'cm'), array(11.58, 'cm'), '', true, My_Pdf::RIGHT);
$page->drawText($inbound_line->getData()['inb_packing_units'], array(10, 'cm'), array(12.09, 'cm'), '', true, My_Pdf::RIGHT);
$page->drawText($inbound_line->getData()['inb_pallets'], array(10, 'cm'), array(12.60, 'cm'), '', true, My_Pdf::RIGHT);
$page->drawText($inbound_line->getData()['items_checked'], array(7, 'cm'), array(13.62, 'cm'), '', true, My_Pdf::RIGHT);

// Tabelle für Loistische Prüfung
$borderStyle = new Zend_Pdf_Style();
$borderStyle->setLineWidth(1);
$borderStyle->setFillColor(new Zend_Pdf_Color_HTML('black'));
$borderStyle->setLinedashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);

$style = new My_Pdf_Table_Column_Style();
$style->setFont($fontBold, 11);
$style->setFillColor(new Zend_Pdf_Color_HTML('black'));
$style->setBackgroundColor(new Zend_Pdf_Color_HTML('#8CB73C'));
$style->setPadding(array(2,2,6,2));
$style->setBorder(array(My_Pdf::LEFT=>$borderStyle, My_Pdf::TOP=>$borderStyle, My_Pdf::RIGHT=>$borderStyle, My_Pdf::BOTTOM=>$borderStyle));

$headstyle = new My_Pdf_Table_Column_Style($style);
$bodyStyle = new My_Pdf_Table_Column_Style($style);
$bodyStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('white'));
$bodyStyle->setFont($fontNormal, 11);
$levelStyle = new My_Pdf_Table_Column_Style($bodyStyle);

$colls = array();
$colCP = new My_Pdf_Table_Column();
$colCP->setStyle($headstyle);
$colCP->setWidth(array(4, 'cm'));
$colCP->setText($this->translate->_('@checkpoint'));
$colls[] = $colCP;

$colRes = new My_Pdf_Table_Column();
$colRes->setStyle($headstyle);
$colRes->setWidth(array(2.5, 'cm'));
$colRes->setText($this->translate->_('@checkresult'));
$colls[] = $colRes;

$colPercent = new My_Pdf_Table_Column();
$colPercent->setStyle($headstyle);
$colPercent->setWidth(array(2.5, 'cm'));
$colPercent->setText($this->translate->_('@checkpercent'));
$colls[] = $colPercent;

$colRemarks = new My_Pdf_Table_Column();
$colRemarks->setStyle($headstyle);
$colRemarks->setText($this->translate->_('@remarks'));
$colls[] = $colRemarks;

$headerRow = new My_Pdf_Table_Row();
$headerRow->setColumns($colls);

$table = new My_Pdf_Table(1);
$table->addRow($headerRow);

$logRow = 0;
foreach($inbound_line->getQcLogistic() as $qc_inb_log) {
	Zend_Registry::get('logger')->info('QC '.print_r($qc_inb_log, true));
	switch ($qc_inb_log['res_level']) {
		case 'check_green' : $levelColor = new Zend_Pdf_Color_HTML('green'); break;
		case 'check_yellow' : $levelColor = new Zend_Pdf_Color_HTML('yellow'); break;
		case 'check_red' : $levelColor = new Zend_Pdf_Color_HTML('red'); break;
	}
	$colls = array();
	$colCP = new My_Pdf_Table_Column();
	$colCP->setStyle($bodyStyle);
	$colCP->setWidth(array(4, 'cm'));
	$colCP->setText($qc_inb_log['quality_checkpoint_local']);
	$colls[] = $colCP;

	$colRes = new My_Pdf_Table_Column();
	$colRes->setStyle($bodyStyle);
	$colRes->setWidth(array(2.5, 'cm'));
//	$colRes->setAlignment(My_Pdf::RIGHT);
	$colRes->setText(sprintf('%01.1f', $qc_inb_log['value']));
	$colls[] = $colRes;

	$colPercent = new My_Pdf_Table_Column();
	$levelStyle->setBackgroundColor($levelColor);
	$colPercent->setStyle($levelStyle);
	$colPercent->setWidth(array(2.5, 'cm'));
//	$colPercent->setAlignment(My_Pdf::RIGHT);
	$colPercent->setText($qc_inb_log['res_percent']);
	$colls[] = $colPercent;

	$colRemarks = new My_Pdf_Table_Column();
	$colRemarks->setStyle($bodyStyle);
	$colRemarks->setText($qc_inb_log['remarks']);
	$colls[] = $colRemarks;

	$row = new My_Pdf_Table_Row();
	$row->setColumns($colls);
	$table->addRow($row);
	$logRow++;
}
$page->addTable($table, array(0.0, 'cm'), array(14.13, 'cm'));

// Tabellen für qualitative Prüfung
$qc_no = 0;
foreach($inbound_line->getClasses() as $qc_class) {
	if ($qc_class['No']<4) {
		
		$colls = array();
		$colCP = new My_Pdf_Table_Column();
		$colCP->setStyle($headstyle);
		$colCP->setWidth(array(3.52, 'cm'));
		$colCP->setText($this->translate->_('@checkpoint'));
		$colls[] = $colCP;

		$colRes = new My_Pdf_Table_Column();
		$colRes->setStyle($headstyle);
		$colRes->setWidth(array(1.41, 'cm'));
		$colRes->setText($this->translate->_('@checkresult'));
		$colls[] = $colRes;

		$colPercent = new My_Pdf_Table_Column();
		$colPercent->setStyle($headstyle);
		$colPercent->setWidth(array(1.59, 'cm'));
		$colPercent->setText($this->translate->_('@checkpercent'));
		$colls[] = $colPercent;

		$headerRow = new My_Pdf_Table_Row();
		$headerRow->setColumns($colls);

		$table = new My_Pdf_Table(1);
		$table->addRow($headerRow);
		
		$line = 0;
		foreach ($inbound_line->getQcQuality() as $qcheckpoint) {
			if ($qcheckpoint['qc_class_no'] == $qc_class['No']) {
				$line++;
				switch ($qcheckpoint['res_level']) {
					case 'check_green' : $levelColor = new Zend_Pdf_Color_HTML('green'); break;
					case 'check_yellow' : $levelColor = new Zend_Pdf_Color_HTML('yellow'); break;
					case 'check_red' : $levelColor = new Zend_Pdf_Color_HTML('red'); break;
				}
				$colls = array();
				$colCP = new My_Pdf_Table_Column();
				$colCP->setStyle($bodyStyle);
				$colCP->setWidth(array(3.52, 'cm'));
				$colCP->setText($qcheckpoint['quality_checkpoint_local']);
				$colls[] = $colCP;

				$colRes = new My_Pdf_Table_Column();
				$colRes->setStyle($bodyStyle);
				$colRes->setWidth(array(1.41, 'cm'));
				// $colRes->setAlignment(My_Pdf::RIGHT);
				$colRes->setText(sprintf('%01.1f', $qcheckpoint['value']));
				$colls[] = $colRes;

				$colPercent = new My_Pdf_Table_Column();
				$levelStyle->setBackgroundColor($levelColor);
				$colPercent->setStyle($levelStyle);
				$colPercent->setWidth(array(1.59, 'cm'));
				//	$colPercent->setAlignment(My_Pdf::RIGHT);
				$colPercent->setText($qcheckpoint['res_percent']);
				$colls[] = $colPercent;

				$row = new My_Pdf_Table_Row();
				$row->setColumns($colls);
				$table->addRow($row);
			}
		}
		$page->addTable($table, ($qc_no*189), array(14.13+(($logRow+1)*0.71428), 'cm'));
		$qc_no++;
	}
}

$page->drawText($this->translate->_('@remarks'), array(0, 'cm'), array(20+(($logRow+1)*0.71428), 'cm'));
$page->drawText($this->translate->_('@checked_by'), array(0, 'cm'), array(22.5+(($logRow+1)*0.71428), 'cm'));


// Feld für Bemerkungen als Tabelle mit einer Zelle
$cols = array();
$colRemarks = new My_Pdf_Table_Column();
$colRemarks->setStyle($bodyStyle);
$colRemarks->setHeight(array(1.53, 'cm'));
$colRemarks->setText($inbound_line->getData()['remarks']);
$cols[] = $colRemarks;

$row = new My_Pdf_Table_Row();
$row->setColumns($cols);

$table = new My_Pdf_Table(1);
$table->addRow($row);
$page->addTable($table, array(0, 'cm'), array(19.5+(($logRow+1)*0.71428), 'cm'));

$page->drawText($inbound_line->getData()['checked_by'], array(4.62, 'cm'), array(22.5+(($logRow+1)*0.71428), 'cm'));

$report = new My_Pdf();
$report->pages[] = $page;

$img_count = count($inbound_line->getImages());
$picPerPage = 0;
Zend_Registry::get('logger')->info('Attachments: '.print_r($inbound_line->getAttachments(), true));
$extractor = new Zend_Pdf_Resource_Extractor();
try {
	foreach ($inbound_line->getAttachments() as $attachment) {
		$attPdf = Zend_Pdf::load(realpath($this->config->upload->quality->pictures.$attachment['path']));
		foreach ($attPdf->pages as $att_page) {
			$inbound_protokoll->pages[] = $extractor->clonePage($att_page);
		}
	}
} catch (Exception $e) {
//			Zend_Registry::get('logger')->info('Fehler beim laden vom Anhang! '.$e->getMessage());
}
//		Zend_Registry::get('logger')->info('Pictures: '.print_r($this->pictures, true));
try {
	foreach ($inbound_line->getImages() as $image) {
		$picPerPage++;
		if ($picPerPage==1) {
			$newPage = new My_Pdf_Page(Zend_Pdf_Page::SIZE_A4_LANDSCAPE, 'utf-8');
			$report->pages[] = $newPage;
		}
//		$image_attr = getimagesize(realpath($this->config->upload->quality->pictures.$image['path']));
//		$this->logger->info('Bild '.print_r(realpath($this->config->upload->quality->pictures.$image['path']), true));
//		$img_relation = $image_attr[0]/$image_attr[1];
//				Zend_Registry::get('logger')->info('Image Path: '.print_r($this->picture_path.'/'.$image['path'], true));
//				Zend_Registry::get('logger')->info('Image Attr.:'.print_r($image_attr, true));
		$cur_image = Zend_Pdf_image::imageWithPath($this->config->upload->quality->pictures.$image['path']);
		$height = $cur_image->getPixelHeight();
		$width = $cur_image->getPixelWidth();
		$img_relation = $width / $height;
		if ($img_relation<1) {
			$height = 381;
			$width = $height * $img_relation;
		} else {
			$width = 268;
			$height = $width * $img_relation;
		}
		switch ($picPerPage) {
			case 1 : $x1=20; $y1=20; break;
			case 2 : $x1=20; $y1=308; break;
			case 3 : $x1=401; $y1=20; break;
			case 4 : $x1=401; $y1=308; break;
		}
		Zend_Registry::get('logger')->info("Position: {$x1}:{$y1} Maße: {$width}x{$height}");
		$newPage->drawImage($cur_image, $x1, $y1, $height, $width);
		if ($picPerPage == 4) $picPerPage = 0;
	}
} catch (Exception $e) {
	Zend_Registry::get('logger')->info('Fehler beim laden vom Anhang! '.$e->getMessage());
}

?>