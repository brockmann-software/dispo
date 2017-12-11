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
	$headCol[1]->setWidth(array(3, 'cm'));
	$headCol[1]->setFont($fontBold, 14);
	$headCol[1]->setText('Zählliste');
	
	$headCol[2] = new My_Pdf_Table_Column();
	$headCol[2]->setWidth(array(8, 'cm'));
	$headCol[2]->setFont($fontBold, 14);
	$headCol[2]->setText("Datum: ".date('d.m.Y', strtotime($this->inventories[0]['inv_date'])));

	$headRow->setColumns($headCol);
	$headerTable->addRow($headRow);

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
	$headerStyle->setFont($fontBold, 10);
	$headerStyle->setTextAlign(My_Pdf::CENTER);
	$headerStyle->setBorder(array(My_Pdf::LEFT=>$borderStyle, My_Pdf::TOP=>$borderStyle, My_Pdf::RIGHT=>$borderStyle, My_Pdf::BOTTOM=>$borderStyle));
	$headerStyle->setPadding(array(2,2,2,2));
	
// Design Datenzeilen
// Wie Kopfzeile
	$bodyStyle = new My_Pdf_Table_Column_Style($headerStyle);
//  Anderer Font und Ausrichtung
	$bodyStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('white'));
	$bodyStyle->setFont($fontNormal, 8);
	$bodyStyle->setTextAlign(My_Pdf::LEFT);
	
	$columns = array();
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

	$column = new My_Pdf_Report_Column('inb_arrival', 'Eingang', array(1.4, 'cm'), array('date', 'd.m.y'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('position', 'Position', array(1.1, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('@empty', 'Kol / Pal', array(0.8, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::RIGHT);
	$columns[] = $column;

	$column = new My_Pdf_Report_Column('@empty', 'Pal', array(0.8, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::RIGHT);
	$columns[] = $column;
		
	$column = new My_Pdf_Report_Column('@empty', '', array(1, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::RIGHT);
	$columns[] = $column;
		
	$column = new My_Pdf_Report_Column('@empty', '', array(1, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;
	
	$column = new My_Pdf_Report_Column('@empty', '', array(1, 'cm'));
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$column->setColumnAlign(My_Pdf::CENTER);
	$columns[] = $column;
	
	$column = new My_Pdf_Report_Column('@empty', 'Bemerkungen');
	$column->setHeaderStyle($headerStyle);
	$column->setBodyStyle($bodyStyle);
	$columns[] = $column;
	
// Gruppen definieren	
	$groupHeaderStyle = new My_Pdf_Table_Column_Style($bodyStyle);
	$groupHeaderStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('#92D050'));
	$groupColumns = array();
	
	$groupFooterStyle = new My_Pdf_Table_Column_Style($headerStyle);
	$groupFooterStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('lightgrey'));

	$groups = array();
// Gruppe 1
	$groupColumn = new My_Pdf_Report_Group_Column('product_desc', true, true);
	$groupColumn->setHeaderStyle($groupHeaderStyle);
	$groupColumns[] = $groupColumn;

	$group = new My_Pdf_Report_Group($groupColumns, $groupHeaderStyle, $groupFooterStyle);
	$groups[] = $group;
	
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

	$group = new My_Pdf_Report_Group($groupColumns, $groupHeaderStyle, $groupFooterStyle);
	$groups[] = $group;
	
	$inventoryReport = new My_Pdf_Report($this->filename, $this->filepath.'\\', $this->inventories, Zend_Pdf_Page::SIZE_A4_LANDSCAPE, 'utf-8');
	$inventoryReport->setHeader($headerTable);
	$inventoryReport->setColumns($columns);
	$inventoryReport->setGroups($groups);
	$inventoryReport->save();
	sleep(3);
//	$inventoryReport = Zend_Pdf::load(realpath($this->filepath.'\\'.$this->filename));
//	$inventoryReport->render();
	
} catch (Exception $e) {
	Zend_Registry::get('logger')->err($e->getMessage());
}
$pdf = file_get_contents(realpath($this->filepath.'\\'.$this->filename));
echo $pdf;