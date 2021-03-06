<?php
if (!file_exists(realpath($this->filepath.'\\'.$this->filename))) try {
	$style_body = new Zend_Pdf_Style();
	$style_body->setLineWidth(1);
	$fontBold = Zend_Pdf_Font::fontWithPath(realpath(APPLICATION_PATH.'/../public/fonts/').'/ARIALBD.ttf');
	$fontNormal = Zend_Pdf_Font::fontWithPath(realpath(APPLICATION_PATH.'/../public/fonts/').'/arial.ttf');

// Report Header
	$headerTable = new My_Pdf_Table(2);
	$headRow = new My_Pdf_Table_Row();
	$headRow->setFont($fontBold, 12);
	$headCol = array();
	
	$headCol[0] = new My_Pdf_Table_Column();
	$headCol[0]->setWidth(array(6, 'cm'));
	$headCol[0]->setImage(realpath(APPLICATION_PATH.'/../public/images/logo_mvs.jpg'), My_Pdf::LEFT, My_Pdf::CENTER, 0.5);
	
	$headCol[1] = new My_Pdf_Table_Column();
	$headCol[1]->setWidth(array(8, 'cm'));
	$headCol[1]->setFont($fontBold, 14);
	$headCol[1]->setText('Warenbestandsliste');
	
	$headCol[2] = new My_Pdf_Table_Column();
//	$headCol[2]->setWidth(array(4, 'cm'));
	$headCol[2]->setFont($fontBold, 12);
	$headCol[2]->setText("Zählung {$this->inventories[0]['inventory_head']} vom ".date('d.m.Y H:i', strtotime($this->inventories[0]['inv_date'])));

//	$headCol[3] = new My_Pdf_Table_Column();
//	$headCol[3]->setWidth(array(2, 'cm'));
//	$headCol[3]->setFont($fontBold, 12);
//	$headCol[3]->setText($this->sufix);

	$headRow->setColumns($headCol);
	$headerTable->addRow($headRow);
	
// Report Footer
	$footerTable = new My_Pdf_Table(1);
	$footerRow = new My_Pdf_Table_Row();
	$footerRow->setFont($fontNormal, 10);
	
	$footerCol = array();
	$footerCol[0] = new My_Pdf_Table_Column();
	$footerCol[0]->setAlignment(My_Pdf::RIGHT);
	$footerCol[0]->setText(' Seite @@CURRENT_PAGE');
	
	$footerRow->setColumns($footerCol);
	$footerTable->addRow($footerRow);
	
// Spaltendefinition
// Design Kopfzeile
// Design Ränder
	$borderStyle = new Zend_Pdf_Style();
	$borderStyle->setLineWidth(1);
	$borderStyle->setFillColor(new Zend_Pdf_Color_HTML('black'));
	$borderStyle->setLinedashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);
	
	$headerStyle = new My_Pdf_Table_Column_Style();
	$headerStyle->setFillColor(new Zend_Pdf_Color_HTML('black'));
	$headerStyle->setBackgroundColor(New Zend_Pdf_Color_HTML('yellow'));
	$headerStyle->setFont($fontBold, 8);
	$headerStyle->setTextAlign(My_Pdf::CENTER);
	$headerStyle->setBorder(array(My_Pdf::LEFT=>$borderStyle, My_Pdf::TOP=>$borderStyle, My_Pdf::RIGHT=>$borderStyle, My_Pdf::BOTTOM=>$borderStyle));
	$headerStyle->setPadding(array(2,2,2,2));
// Design Datenzeilen
// Wie Kopfzeile
	$bodyStyle = new My_Pdf_Table_Column_Style($headerStyle);
//  Anderer Font und Ausrichtung
	$bodyStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('white'));
	$bodyStyle->setFont($fontNormal, 7);
	$bodyStyle->setTextAlign(My_Pdf::LEFT);
	$bodyStyle->setVerticalAlign(My_Pdf::MIDDLE);
	
	$columns = array();
	$column = new My_Pdf_Report_Column('stock_location_desc', '', array(0.01, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$columns[] = $column;
	
	$column = new My_Pdf_Report_Column('product_desc', 'Produkt', array(2.2, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$columns[] = $column;
	
	$column = new My_Pdf_Report_Column('origin', 'Urs', array(0.85, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('inb_lot', 'Los-Nr', array(1, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$columns[] = $column;
		
	$column = new My_Pdf_Report_Column('label', 'Auszeichn', array(3.5, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$columns[] = $column;
		
	$column = new My_Pdf_Report_Column('t_packaging', 'Verp', array(1, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('items', 'Pkst', array(0.8, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('weight_item', 'g Pkst', array(1, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('brand', 'Marke', array(1.5, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('vendor_name', 'Lieferant', array(4.0, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('inb_arrival', 'Eingang', array(1.2, 'cm'), array('date', 'd.m.y'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('position', 'Position', array(1.1, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('room_short', 'KH', array(0.4, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;
	
	$column = new My_Pdf_Report_Column('rack', 'Rg', array(0.4, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;
	
	$column = new My_Pdf_Report_Column('tu_pallet', 'Kol / Pal', array(0.8, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::RIGHT);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('stock_pallets', 'Pal', array(0.8, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::RIGHT);
	$columns[] = $column;
		
	$column = new My_Pdf_Report_Column('stock', 'Bestand', array(1.3, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::RIGHT);
	$columns[] = $column;
	
	$condStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$condStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('red'));
	$condFormat = new My_Pdf_Report_ConditionalFormat(My_Pdf_Report::GREATER_EQUAL, 4, null, $condStyle);
	$condFormats[] = $condFormat;
	
	$condStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$condStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('#FFC000'));
	$condFormat = new My_Pdf_Report_ConditionalFormat(My_Pdf_Report::SMALLER, 4, null, $condStyle);
	$condFormats[] = $condFormat;

	$condStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$condStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('yellow'));
	$condFormat = new My_Pdf_Report_ConditionalFormat(My_Pdf_Report::SMALLER, 3, null, $condStyle);
	$condFormats[] = $condFormat;
	
	$condStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$condStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('#92D050'));
	$condFormat = new My_Pdf_Report_ConditionalFormat(My_Pdf_Report::SMALLER, 2, null, $condStyle);
	$condFormats[] = $condFormat;
			
	$column = new My_Pdf_Report_Column('grade_weighted', 'QC', array(0.5, 'cm'), null, '', $condFormats);
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;
	
	$condStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$condStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('red'));
	$condFormat = new My_Pdf_Report_ConditionalFormat(My_Pdf_Report::GREATER_EQUAL, 4, null, $condStyle);
	$condFormats[] = $condFormat;
	
	$condStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$condStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('#FFC000'));
	$condFormat = new My_Pdf_Report_ConditionalFormat(My_Pdf_Report::SMALLER, 4, null, $condStyle);
	$condFormats[] = $condFormat;

	$condStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$condStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('yellow'));
	$condFormat = new My_Pdf_Report_ConditionalFormat(My_Pdf_Report::SMALLER, 3, null, $condStyle);
	$condFormats[] = $condFormat;
	
	$condStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$condStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('#92D050'));
	$condFormat = new My_Pdf_Report_ConditionalFormat(My_Pdf_Report::SMALLER, 2, null, $condStyle);
	$condFormats[] = $condFormat;
			
	$column = new My_Pdf_Report_Column('qc_on_inventory', 'QC', array(0.5, 'cm'), null, '', $condFormats);
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;
	
	$column = new My_Pdf_Report_Column('blocked', 'X', array(0.3, 'cm'), array('boolean', 'X', ''));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;
	
	$condFormats = array();
	$condFormat = new My_Pdf_Report_ConditionalFormat(My_Pdf_Report::EQUAL, 2, null, $bodyStyle, 'P');
	$condFormats[] = $condFormat;
	$condFormat = New My_Pdf_Report_ConditionalFormat(My_Pdf_Report::NOT_EQUAL, 2, null, $bodyStyle, '');
	$condFormats[] = $condFormat;
	
	$column = new My_Pdf_Report_Column('type', 'P', array(0.3, 'cm'), null, '', $condFormats);
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;
	
	$column = new My_Pdf_Report_Column('remarks', 'Bemerkungen');
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$columns[] = $column;
	
// Gruppen definieren	
	$groupHeaderStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$groupHeaderStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('#92D050'));
	
	$groupFooterStyle = new My_Pdf_Table_Column_Style($headerStyle);
	$groupFooterStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('lightgrey'));

	$groups = array();
// Gruppe 1
	$groupColumns = array();
	$groupColumn = new My_Pdf_Report_Group_Column('stock_location_desc', true, false);
	$groupColumn->setHeaderColSpan(20);
	$groupColumn->setFooterColSpan(20);
	$thisGroupHeaderStyle = new My_Pdf_Table_Column_Style($groupHeaderStyle);
	$thisGroupHeaderStyle->setFont($fontBold, 10);
	$thisGroupHeaderStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('#95B3D7'));
	
	$groupColumn->setHeaderStyle($thisGroupHeaderStyle);
	$groupColumns[] = $groupColumn;

	$group = new My_Pdf_Report_Group($groupColumns, $thisGroupHeaderStyle, $groupFooterStyle);
	$group->setForceNewPage();
	$groups[] = $group;

// Gruppe 2
	$groupColumns = array();
	$groupColumn = new My_Pdf_Report_Group_Column('product_desc', true, true);
	$groupColumn->setHeaderStyle($groupHeaderStyle);
	$groupColumns[] = $groupColumn;

	$groupColumn = new My_Pdf_Report_Group_Column('stock', false, true, My_Pdf_Report_Group::COMPUTE);
	$groupColumn->setHeaderStyle($groupHeaderStyle);
	$groupColumn->setFooterStyle($groupFooterStyle);
	$groupColumn->getFooterStyle()->setTextAlign(My_Pdf::RIGHT);
	$groupColumns[] = $groupColumn;

	$group = new My_Pdf_Report_Group($groupColumns, $groupHeaderStyle, $groupFooterStyle);
	$groups[] = $group;

// Gruppe 3	
	$groupColumns = array();

	$groupColumn = new My_Pdf_Report_Group_Column('product_desc', false, true);
	$groupColumn->setHeaderStyle($groupHeaderStyle);
	$groupColumns[] = $groupColumn;

	$groupColumn = new My_Pdf_Report_Group_Column('items', false, true);
	$groupColumn->setHeaderStyle($groupHeaderStyle);
	$groupColumns[] = $groupColumn;

	$groupColumn = new My_Pdf_Report_Group_Column('weight_item', false, true);
	$groupColumn->setHeaderStyle($groupHeaderStyle);
	$groupColumns[] = $groupColumn;

	$groupColumn = new My_Pdf_Report_Group_Column('brand', false, true);
	$groupColumn->setHeaderStyle($groupHeaderStyle);
	$groupColumns[] = $groupColumn;

	$groupColumn = new My_Pdf_Report_Group_Column('stock', false, true, My_Pdf_Report_Group::COMPUTE);
	$groupColumn->setHeaderStyle($groupHeaderStyle);
	$groupColumn->setFooterStyle($groupFooterStyle);
	$groupColumn->getFooterStyle()->setTextAlign(My_Pdf::RIGHT);
	$groupColumns[] = $groupColumn;
	$group = new My_Pdf_Report_Group($groupColumns, $groupHeaderStyle, $groupFooterStyle);
	$group->setEmptyLines(2,1);
	$groups[] = $group;
	
	$inventoryReport = new My_Pdf_Report($this->filename, $this->filepath.'\\', $this->inventories, Zend_Pdf_Page::SIZE_A4_LANDSCAPE, 'utf-8');
	$inventoryReport->setHeader($headerTable);
	$inventoryReport->setFooter($footerTable);
	$inventoryReport->setColumns($columns);
	$inventoryReport->setGroups($groups);
	$inventoryReport->save();
} catch (Exception $e) {
	Zend_Registry::get('logger')->err($e->getMessage());
}
$pdf = file_get_contents(realpath($this->filepath.'\\'.$this->filename));
echo $pdf;