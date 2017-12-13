<?php
class WhmanagementController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	
	private function loadDependencies($filter=true)
	{
		$dependencies['rooms'] = $this->db->query('SELECT * FROM room WHERE stock_location = 1 ORDER BY room')->fetchAll();
		if ($filter) $dependencies['rooms'][count($dependencies['rooms'])] = array('No'=>0, 'room'=>'--alle--', 'stock_location'=>1);
		$dependencies['products'] = $this->db->query('SELECT * FROM product')->fetchAll();
		if ($filter) $dependencies['products'][count($dependencies['products'])] = array('No'=>'', 'product'=>'--alle--');
		$dependencies['inventories'] = $this->db->query('SELECT * FROM inventory_head')->fetchAll();
		$dependencies['packagings'] = $this->db->query("select * from packaging")->fetchAll();
		$dependencies['t_packagings'] = $this->db->query("select * from tp_format")->fetchAll();
		$dependencies['labels'] = $this->db->query("select * from label")->fetchAll();
		$dependencies['qualities'] = $this->db->query("select * from quality")->fetchAll();
		$dependencies['brands'] = $this->db->query("select * from brand")->fetchAll();
		$dependencies['quality_classes'] = $this->db->query("select * from quality_class")->fetchAll();
		return $dependencies;
	}
	
	public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Lager Management');
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
		$this->config = Zend_Registry::get('config');
    }

	public function indexAction()
	{
		$errors = array();
		if ($this->hasParam('error')) $errors['all'] = $this->getParam('error');
		if ($this->hasParam('ok')) $errors['ok'] = $this->getParam('ok');
		$inventory_heads = $this->db->query('SELECT * FROM v_inventory_head');
		$this->view->data = $inventory_heads;
		$this->view->errors = $errors;
	}

	public function editAction()
	{
	}
	
	public function printAction()
	{
		$query_blocked = '';
		$sufix = '-alle';
		if (!$this->hasParam('No')) $this->_redirect('/whmanagement/index/error/Keine%20Zählung%20übergeben');
		if ($this->hasParam('blocked')) {
			$blocked = $this->getParam('blocked');
			$query_blocked = " AND blocked='{$blocked}'";
			$sufix = ($blocked==1) ? '-gesperrte' : '-freie';
			$this->logger->info('Blocked: '.print_r($blocked, true));
		}
		$inventories = $this->db->query('SELECT * FROM v_inventory_summery WHERE state = 2 AND stock<>0'.$query_blocked.' AND inventory_head = ? ORDER BY product, items, weight_item, brand_no, packaging, quality, inb_arrival, position', $this->getParam('No'));
		while ($inv = $inventories->fetch()) {
			$inv['remarks'].= ($inv['remarks_on_inventory']!='') ? ' '.$inv['remarks_on_inventory'] : '';
			$inventory[] = $inv;
		}
		if (count($inventory)==0) $this->_redirect('/whmanagement/index/error/Die%20Zählung%20ist%20noch%20nicht%20abgeschlossen!');
		$filename = "Bestand ".date('d.m.Y H_i', strtotime($inventory[0]['inv_date'])).$sufix.".pdf";
		$filepath = realpath($this->config->report->inventory);
		$this->view->inventories = $inventory;
		$this->view->filename = $filename;
		$this->view->filepath = $filepath;
		$this->view->sufix = $sufix;
		$layout = $this->_helper->layout();		
		$layout->setLayout('pdf_layout');
		$this->renderScript('/whmanagement/pdfinventory.php');
	}
	
	public function printcountlistAction()
	{
		if (!$this->hasParam('No')) $this->_redirect('/whmanagement/index/error/Keine%20Zählung%20übergeben');
		$inventories = $this->db->query('SELECT * FROM v_inventory_summery WHERE state < 2 AND inventory_head = ? ORDER BY product, items, weight_item, brand_no, inb_arrival desc, position desc', $this->getParam('No'))->fetchAll();
		if (count($inventories)==0) $this->_redirect('/whmanagement/index/error/Die%20Zählung%20ist%20bereits%20abgeschlossen!');
		$filename = "Zaehlliste ".date('d.m.Y', strtotime($inventories[0]['inv_date']))." ".date('H_i', strtotime($inventories[0]['inv_date'])).".pdf";
		$filepath = realpath($this->config->report->inventory);
		$this->view->inventories = $inventories;
		$this->view->filename = $filename;
		$this->view->filepath = $filepath;
		$layout = $this->_helper->layout();
		
		$layout->setLayout('pdf_layout');
		$this->renderScript('/whmanagement/pdfcountlist.php');
	}
	
	public function deleteAction()
	{
		if (!$this->hasParam('No')) $this->_redirect('/whmanagement/index/error/Keine%20Zählung%20übergeben');
		$inventory_table = new Application_Model_InventoryheadModel();
		$inventories = $inventory_table->find($this->getParam('No'));
		if ($inventories->count()>0) {
			$inventories->current()->delete();
		} else $this->_redirect('/whmanagement/index/error/Zählung%20nicht%20gefunden');
		$this->_redirect('/whmanagement/index/ok/Zählung%20gelöscht');
	}
	
	public function closeinventoryAction()
	{
		if ($this->hasParam('No')) {
			$date = new Zend_Date();
			try {
				$this->db->query('UPDATE inventory_head SET state = 2, ended = ? WHERE No = ?', array($date->toString('YYYY-MM-dd HH:mm:ss'), $this->getParam('No')));
				$this->logger->info('Die Zählung wurde abgeschlossen');
				$this->_redirect('/whmanagement/index/');
			} catch (Exception $e) {
				$this->logger->err('Die Zählung wurde nicht abgeschlossen! '.$e->getMessage());
				$errors['all'] = 'Die Zählung wurde nicht abgeschlossen!';
				$this->_redirect('/whmanagement/doinventory/No/'.$this->getParam('No'));
			}
		} else {
			$errors['No'] = 'Die Funktion "Zählung abschließen" wurde ohne Parameter aufgerufen!';
			$this->logger->err('Closeinventory wurde ohne Parameter aufgerufen!');
		}
	}

	public function doinventoryAction()
	{
		$inventory_lines = array();
		$inventory_head = array();
		$inventory_head_table = new Application_Model_InventoryheadModel();
		$date = new Zend_Date();
		$errors = array();
		$state = 0;
		if ($this->hasParam('state')) $state = $this->getParam('state');
		if ($this->hasParam('No')) {
			$inventory_heads = $inventory_head_table->find($this->getParam('No'));
			if ($inventory_heads->count()==0) {
				$errors['all'] = 'Die Zählung existiert nicht!';
				$this->logger->err('Die Zählung existiert nicht! ');
			} elseif ($inventory_heads->current()->state == 2) { 
				if ($state<>3) {
					$errors['all'] = 'Die Zählung wurde bereits abgeschlossen!';
					$this->logger->err('Die Zählung wurde bereits abgeschlossen! ');
				} else {
					$inventory_head = $inventory_heads->current();
					$filepath = realpath($this->config->report->inventory);
					$sufix = '-alle';
					$filename = realpath($this->config->report->inventory.'/'."Bestand ".date('d.m.Y H_i', strtotime($inventory_head->date)).$sufix.".pdf");
					if (file_exists($filename)) unlink($filename);
					$sufix = '-gesperrte';
					$filename = realpath($this->config->report->inventory.'/'."Bestand ".date('d.m.Y H_i', strtotime($inventory_head->date)).$sufix.".pdf");
					if (file_exists($filename)) unlink($filename);
					$sufix = '-freie';
					$filename = realpath($this->config->report->inventory.'/'."Bestand ".date('d.m.Y H_i', strtotime($inventory_head->date)).$sufix.".pdf");
					if (file_exists($filename)) unlink($filename);
					$inventory_head->state = 3;
					$inventory_head->save();
					$this->logger->info('Zählung gefunden und Status auf 3 geändert');
				}
			} else $inventory_head = $inventory_heads->current();
		} else {
			$inventory_heads = $inventory_head_table->fetchAll('state <> 2');
			if ($inventory_heads->count()==0) {
				try {
					$inventory_head['date'] = $date->toString('YYYY-MM-dd HH:mm:ss');
					$inventory_head['state'] = 0;
					$inventory_head['started'] = $inventory_head['date'];
					$inventory_head_table->insert($inventory_head);
					$inventory_head['No'] = $this->db->lastInsertId();
					$sql = 'INSERT INTO inventory';
					$sql.= '(inventory_head,';
					$sql.= ' inbound_line,';
					$sql.= ' movement,';
					$sql.= ' date,';
					$sql.= ' room,';
					$sql.= ' rack,';
					$sql.= ' level,';
					$sql.= ' pallets,';
					$sql.= ' tu_pallet,';
					$sql.= ' trading_units,';
					$sql.= ' blocked)';
					$sql.= 'SELECT';
					$sql.= ' ?,';
					$sql.= ' inbound_line,';
					$sql.= ' No,';
					$sql.= ' NOW(),';
					$sql.= ' "0",';
					$sql.= ' "0",';
					$sql.= ' "0",';
					$sql.= ' "0",';
					$sql.= ' tu_pallet,';
					$sql.= ' "0",';
					$sql.= ' "0" ';
					$sql.= 'FROM v_movements_for_inventory where movement = 1 and stock<>0';
					$this->logger->info('SQL: '.$sql);
					$statement = $this->db->query($sql, $inventory_head['No']);
		//			$statement->execute();
					$this->logger->info('Inventory_Head erstellt: '.print_r($inventory_head, true));
		//			$this->logger->info(print_r($statement, true));
				} catch (Exception $e) {
					$errors['all'] = 'Ein Fehler ist bei dem Erstellen der Zählung aufgetreten!';
					$this->logger->err($e->getMessage());
				}
				$this->_redirect('/whmanagement/doinventory/No/'.$inventory_heads->current()->No);
			} else {
				$errors['all'] = 'Es gibt bereits eine offene Zählung! Bitte erst diese Zählung abschließen';
				$this->_redirect('/whmanagement/doinventory/No/'.$inventory_heads->current()->No);
			}
		}
		if (count($errors)==0) try {
			$noZero = ($inventory_head->state == 3) ? 'AND trading_units<>0 ' : '';
			$inventory_lines = $this->db->query('SELECT * from v_inventory WHERE inventory_head = ? '.$noZero.'ORDER BY product, items, weight_item, brand_no, position desc, inbound_line, No', $inventory_head->No)->fetchAll();
	//		$this->logger->info('Inventory_lines aufgerufen: '.print_r($inventory_lines, true));
		} catch (Exception $e) {
			$errors['all'] = 'Ein Fehler ist aufgetreten bei dem Laden der Zählung!';
			$this->logger->info($e->getMessage());
		}
		$params = $this->loadDependencies(true);
		$params['errors'] = $errors;
		$params['inventory_head'] = $inventory_head;
		$params['inventory_lines'] = $inventory_lines;
		$this->view->params = $params;
	}
	
	public function getlinesAction()
	
	{
		$where = array();
		$errors = array();
		$inventories = array();
		if ($this->hasParam('inventory_head')) {
			$where['inventory_head']['value'] = $this->getParam('inventory_head');
			$where['inventory_head']['type'] = 'number';
		}
		if ($this->hasParam('position')) {
			$where['position']['value'] = $this->getParam('position');
			$where['position']['type'] = 'string';
		}
		if ($this->hasParam('product')) {
			$where['product']['value'] = $this->getParam('product');
			$where['product']['type'] = 'string';
		}
		$this->logger->info('$where: '.print_r($where, true));
		
		$sqlStr = 'SELECT * FROM v_inventory';
		$pNo = 0;
		foreach ($where as $key => $val) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' AND ';
			$pNo++;
			$val['type']=='string' ? $sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val['value'].'%")' : $sqlStr.= $key.' = '.$val['value'];
		}
		$sqlStr .=' ORDER BY product, items, weight_item, brand_no, position desc, inbound_line, No';
		$this->logger->info('SQL: '.print_r($sqlStr, true));
		try {
			$inventories = $this->db->query($sqlStr)->fetchAll();
		} catch (Exception $e) {
			$errors['all'] = 'Fehler beim Auslesen der Zählung';
			$this->logger->info($e->getMessage());
		}
		$this->view->results = $inventories;
		$this->view->errors = $errors;
		$this->_helper->layout()->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');
	}
	
	public function storeinventoryAction()
	{
		$errors = array();
		$date = new Zend_Date();
		$variant_table = new Application_Model_VariantModel();
		$inb_line_table = new Application_Model_InboundlineModel();
		$inbound_table = new Application_Model_InboundModel();
		$inventory_table = new Application_Model_InventoryModel();
		$po_table = new Application_Model_ProductionorderModel();
		$po_line_table = new Application_Model_ProductionorderlineModel();
		$movement_table = new Application_Model_MovementModel();
		$stock_location_table = new Application_Model_StocklocationModel();
		if ($this->getRequest()->isPost()) {
			$this->logger->info('Post-Request mit $_POST: '.print_r($_POST, true));
			$result = $_POST;

			// inventory auslesen
			isset($_POST['No']) ? $inventory_No = $_POST['No'] : $inventory_No = 0;
			isset($_POST['inbound_line']) ? $inventory['inbound_line'] = $_POST['inbound_line'] : $inventory['inbound_line'] = 0;
			isset($_POST['movement']) ? $inventory['movement'] = $_POST['movement'] : $inventory['movement'] = 0;
			if (isset($_POST['date'])) $inventory['date'] = $_POST['date'];
			isset($_POST['room']) ? $inventory['room'] = $_POST['room'] : $inventory['room'] = 0;
			isset($_POST['rack']) ? $inventory['rack'] = $_POST['rack'] : $inventory['rack'] = 0;
			isset($_POST['level']) ? $inventory['level'] = $_POST['level'] : $inventory['level'] = 0;
			isset($_POST['pallets']) ? $inventory['pallets'] = $_POST['pallets'] : $inventory['pallets'] = 0;
			isset($_POST['tu_pallet']) ? $inventory['tu_pallet'] = $_POST['tu_pallet'] : $inventory['tu_pallet'] = 0;
			isset($_POST['trading_units']) ? $inventory['trading_units'] = $_POST['trading_units'] : $inventory['trading_units'] = 0;
			isset($_POST['blocked']) ? $inventory['blocked'] = $_POST['blocked'] : $_POST['blocked'] = 0;

			// inbound_line auslesen
			isset($_POST['qc_on_inventory']) ? $inbound_line['qc_on_inventory']=$_POST['qc_on_inventory'] : $inbound_line['qc_on_inventory']=1;
			isset($_POST['inb_lot']) ? $inbound_line['lot'] = $_POST['inb_lot'] : $inbound_line['lot'] = '';

			// variante auslesen
			isset($_POST['items']) ? $variant['items'] = $_POST['items'] : $variant['items'] = 0;
			isset($_POST['weight_item']) ? $variant['weight_item'] = $_POST['weight_item'] : $variant['weight_item'] = 0;
			isset($_POST['t_packaging']) ? $variant['t_packaging'] = $_POST['t_packaging'] : $variant['t_packaging'] = '';
			isset($_POST['packaging']) ? $variant['packaging'] = $_POST['packaging'] : $variant['packaging'] = 0;
			isset($_POST['label']) ? $variant['label'] = $_POST['label'] : $variant['label'] = 0;

			// inventory_head auslesen
			isset($_POST['inventory_head']) ? $inventory['inventory_head'] = $_POST['inventory_head'] : $inventory['inventory_head'] = 0;

			// zusätzliche Infos auslesen
			isset($_POST['default_TU']) ? $default_tradingUnits = $_POST['default_TU'] : $default_tradingUnits = 0;
			isset($_POST['overwrite']) ? $overwrite = ($_POST['overwrite'] == 'true') : $overwrite = false;
			
			//Überprüfung der Eingaben
			if ($inventory['inventory_head'] == 0) $errors['inventory_head'] = 'Inventory_Head darf nicht leer oder 0 sein!';
			if ($inventory['inbound_line'] == 0) $errors['inbound_line'] = 'Inbound_Line darf nicht leer oder 0 sein!';
			if ($inventory['movement'] == 0) $errors['movement'] = 'Movement darf nicht leer sein!';
			$this->logger->info('Fehler: '.print_r($errors, true));
			$this->logger->info('Inventory_No: '.print_r($inventory_No, true));
			if (count($errors)==0) {
				$this->db->beginTransaction();
				try {
					// movement holen
					$movement_set = $movement_table->find($inventory['movement']);
					if ($movement_set->count()==0) throw new Exception('Bewegung existiert nicht');
					$cur_movement = $movement_set->current();
					// Inbound_Line holen
					$inbound_line_set = $inb_line_table->find($inventory['inbound_line']);
					if ($inbound_line_set->count()==0) throw new Exception('Inbound_line existiert nicht');
					$cur_inbound_line = $inbound_line_set->current();
					// variante holen
					$variant_set = $variant_table->find($cur_inbound_line['variant']);
					if ($variant_set->count()==0) throw new Exception('Variante existiert nicht!');
					$cur_variant = $variant_set->current()->toArray();
					// Inbound holen
					$inbound_set = $inbound_table->find($cur_inbound_line->inbound);
					if ($inbound_set->count()==0) throw new Exception('Inbound existiert nicht!');
					$cur_inbound = $inbound_set->current();
					// stock location holen
					$stock_location_set = $stock_location_table->find($cur_movement->stock_location);
					if ($stock_location_set->count()==0) throw new Exception('Stock Location existiert nicht');
					$cur_stock_location = $stock_location_set->current();
					if ($inventory_No<>0) { // vorhandene Inventory wird bearbeitet
						if ($cur_stock_location->quarantene==$inventory['blocked']) { // Sperr-Status wurde nicht gändert
							$dev_inventory = $inventory_table->find($inventory_No)->current()->toArray();
							$this->logger->info('dev_inventory: '.print_r($dev_inventory, true));
							$this->logger->info('default_tradingUnits: '.print_r($default_tradingUnits, true));
							$this->logger->info('overwrite: '.print_r($overwrite, true));
							if (($default_tradingUnits <> $dev_inventory['trading_units']) and ($overwrite==false)) { // Ein anderer Benutzer hat in der zwischenzeit den Eintrag verändert
								$inventory['race_condition'] = $dev_inventory['trading_units'];
								$this->logger->info('Race Condition für Inventory: '.print_r($inventory, true));
								$this->logger->info('Gespeicherte Werte: '.print_r($dev_inventory, true));
								throw new Exception('Der Wert wurde bereits von einem anderen Benutzer auf '.$dev_inventory['trading_units'].'geändert!');
							}
						} else { // Der Sperr-Status wurde verändert
							// Umlagerung in oder aus dem Sperrlager
							// Erstellung des Umlagerungsauftrags
							// holen des anderen Lagerortes
							$rearangement_order_line_table = new Application_Model_RearangementOrderLineModel();
							$rearangement_order_table = new Application_Model_RearangementOrderModel();
							$rearangement_table = new Application_Model_RearangementModel();
							$rearangement_line_table = new Application_Model_RearangementLineModel();
							if ($cur_stock_location->quarantene == 1) $stock_location_set = $stock_location_table->find($cur_stock_location->quarantene_for_sl);
							else $stock_location_set = $stock_location_table->fetchAll($stock_location_table->select()->where('quarantene_for_sl = ? and quarantene = 1', $cur_stock_location->No));
							if ($stock_location_set->count()==0) throw new Exception('Anderer Lagerort nicht vorhanden');
							$inbound_sl = $stock_location_set->current();
							$rearangement_order['outbound_sl'] = $cur_stock_location->No;
							$rearangement_order['inbound_sl'] = $inbound_sl->No;
							$rearangement_order['outbound_date'] = $date->toString('YYYY-MM-dd HH:mm:ss');
							$rearangement_order['inbound_date'] = $date->toString('YYYY-MM-dd HH:mm:ss');
							$rearangement_order['transit_time'] = 0;
							$rearangement_order_table->insert($rearangement_order);
							$rearangement_order['No'] = $this->db->lastInsertId();
							$this->logger->info('Umlagerungsauftrag geschrieben: '.print_r($rearangement_order, true));
							// Umlagerungsauftragszeilen
							$rearangement_order_line['rearangement_order'] = $rearangement_order['No'];
							$rearangement_order_line['variant'] = $cur_inbound_line->variant;
							$rearangement_order_line['trading_units'] = $inventory['trading_units'];
							$rearangement_order_line['packing_units'] = $inventory['trading_units'] * $cur_variant['items'];
							$rearangement_order_line['pallet'] = $cur_inbound_line->pallet;
							$rearangement_order_line['pallets'] = $inventory['pallets'];
							$rearangement_order_line['inb_line_suggested'] = $cur_inbound_line->No;
							$rearangement_order_line_table->insert($rearangement_order_line);
							$rearangement_order_line['No'] = $this->db->lastInsertId();
							$this->logger->info('Umlagerungsauftragszeile Ausgang geschrieben! '.print_r($rearangement_order_line, true));
							// Ulagerungsbuchung
							$rearangement['rearangement_order'] = $rearangement_order['No'];
							$rearangement['outbound_date'] = $rearangement_order['outbound_date'];
							$rearangement['inbound_date'] = $rearangement_order['inbound_date'];
							$rearangement['tour'] = 0;
							$rearangement_table->insert($rearangement);
							$rearangement['No'] = $this->db->lastInsertId();
							$this->logger->info('Umlagerung geschrieben! '.print_r($rearangement, true));
							// Umlagerungsbuchungszeile
							$rearangement_line['rearangement'] = $rearangement['No'];
							$rearangement_line['inbound_line'] = $cur_inbound_line->No;
							$rearangement_line['trading_units'] = $rearangement_order_line['trading_units'];
							$rearangement_line['packing_units'] = $rearangement_order_line['packing_units'];
							$rearangement_line['pallet'] = $rearangement_order_line['pallet'];
							$rearangement_line['pallets'] = $rearangement_order_line['pallets'];
							$rearangement_line_table->insert($rearangement_line);
							$rearangement_line['No'] = $this->db->lastInsertId();
							$this->logger->info('Umlagerungszeile geschrieben! '.print_r($rearangement_line, true));
							
							// Movement outbound
							$movement['No'] = 0;
							$movement['movement'] = 2;
							$movement['date'] = $date->toString('YYYY-MM-dd HH:mm:ss');
							$movement['trading_units'] = $inventory['trading_units'];
							$movement['packing_units'] = $inventory['trading_units'] * $cur_variant['items'];
							$movement['trading_units_production'] = $inventory['trading_units'];
							$movement['packing_units_production'] = $inventory['trading_units'] * $cur_variant['items'];
							$movement['status'] = 3;
							$movement['type'] = 4;
							$movement['stock_location'] = $cur_stock_location->No;
							$movement['remarks'] = 'Umlagerung bei Zählung';
							$movement['inbound_line'] = $cur_inbound_line['No'];
							$movement['out_order_line'] = $rearangement_order_line['No'];
							$movement['outbound_line'] = $rearangement_line['No'];
							$movement['inbound_movement'] = $inventory['movement'];
							$this->logger->info('Movement: '.print_r($movement, true));
							$movement_table->insert($movement);
							$movement['No'] = $this->db->lastInsertId();
							$this->logger->info('Bewegung wurde gespeichert mit No: '.$movement['No']);
								
							// Movement inbound
							$movement['No'] = 0;
							$movement['movement'] = 1;
							$movement['date'] = $date->toString('YYYY-MM-dd HH:mm:ss');
							$movement['trading_units'] = $inventory['trading_units'];
							$movement['packing_units'] = $inventory['trading_units'] * $cur_variant['items'];
							$movement['trading_units_production'] = $inventory['trading_units'];
							$movement['packing_units_production'] = $inventory['trading_units'] * $cur_variant['items'];
							$movement['status'] = 3;
							$movement['type'] = 4;
							$movement['stock_location'] = $inbound_sl->No;
							$movement['remarks'] = 'Umlagerung bei Zählung';
							$movement['inbound_line'] = $cur_inbound_line['No'];
							$movement['out_order_line'] = 0;
							$movement['outbound_line'] = 0;
							$movement['inbound_movement'] = 0;
							$this->logger->info('Movement: '.print_r($movement, true));
							$movement_table->insert($movement);
							$movement['No'] = $this->db->lastInsertId();
							$movement['inbound_movement'] = $movement['No'];
							$movement_table->update($movement, array('No = ?'=>$movement['inbound_movement']));
							$this->logger->info('Bewegung wurde gespeichert mit No: '.$movement['No']);
							
							// inventory mit neuem inbound movement verbinden 
							$inventory['movement'] = $movement['No'];
						}
						$inventory_table->update($inventory, array('No = ?' => $inventory_No));
						$inventory['No'] = $inventory_No;
						$this->logger->info('Die Inventory wurde gespeichert: '.print_r($inventory, true));
						$inbound_line_set->current()->qc_on_inventory = $inbound_line['qc_on_inventory'];
						$inbound_line_set->current()->save();
						$this->logger->info('QC qurde gespeichert');
					} else {
						require_once('VariantController.php');
					// Inbound_line und Variante holen
						$inventory['inbound_line'] = $cur_inbound_line['No'];
					// Geänderte Variante feststellen
						$new_line = false;
						$new_variant = array_merge($cur_variant, $variant);
						$new_variant['No'] = variantController::buildNo($new_variant)['No'];
							$this->logger->info('Akt. Variante: '.print_r($cur_variant, true));
							$this->logger->info('Gem Variante: '.print_r($variant, true));
							$this->logger->info('Neue Variante: '.print_r($new_variant, true));
						if ($cur_variant['No'] <> $new_variant['No']) {
						// Prüfen, ob Variante existiert, sonst neu anlegen.
							$this->logger->info('Variante: '.print_r($new_variant, true));
							$variant_set = $variant_table->find($new_variant['No']);
							if ($variant_set->count()==0) $variant_table->insert($new_variant);
							$new_line = true;
						}
						// geänderte Variante oder geänderte Losnummer?
						$this->logger->info('new_line: '.print_r($new_line, true));
						$this->logger->info('Inbound_Line: '.print_r($inbound_line, true));
						$this->logger->info('cur_inbound_line: '.print_r($cur_inbound_line, true));
						if ($new_line or ($inbound_line['lot']<>$cur_inbound_line['lot'])) {
						// Produktionsauftrag anlegen
							$production_order['stock_location'] = 1;
							$production_order['start'] = $date->toString('YYYY-MM-dd HH:mm:ss');
							$production_order['end'] = $date->toString('YYYY-MM-dd HH:mm:ss');
							$production_order['created'] = $date->toString('YYYY-MM-dd HH:mm:ss');
							$po_table->insert($production_order);
							$production_order['No'] = $this->db->lastInsertId();
						// Eingangsmenge
							$po_line_in['production_order'] = $production_order['No'];
							$po_line_in['type'] = 1;
							$po_line_in['line'] = 10000;
							$po_line_in['product'] = $cur_inbound_line['product'];
							$po_line_in['variant'] = $cur_inbound_line['variant'];
							$po_line_in['trading_units'] = $inventory['trading_units'];
							$po_line_in['packing_units'] = $inventory['trading_units'] * $cur_variant['items'];
							$po_line_in['pallet'] = 1;
							$po_line_in['pallets'] = $inventory['pallets'];
							$po_line_in['tu_pallet'] = $cur_inbound_line['tu_pallet'];
							$po_line_in['start'] = $production_order['start'];
							$po_line_in['end'] = $production_order['end'];
							$po_line_table->insert($po_line_in);
							$po_line_in['No'] = $this->db->lastInsertId();
							$this->logger->info('po_line_in gespeichert: '.print_r($po_line_in, true));
						// Ausgangsmenge
							$po_line_out['production_order'] = $production_order['No'];
							$po_line_out['type'] = 2;
							$po_line_out['line'] = 10000;
							$po_line_out['product'] = $cur_inbound_line['product'];
							$po_line_out['variant'] = $new_variant['No'];
							$po_line_out['trading_units'] = $inventory['trading_units'];
							$po_line_out['packing_units'] = $inventory['trading_units'] * $new_variant['items'];
							$po_line_out['pallet'] = 1;
							$po_line_out['pallets'] = $inventory['pallets'];
							$po_line_out['tu_pallet'] = $inventory['tu_pallet'];
							$po_line_out['start'] = $production_order['start'];
							$po_line_out['end'] = $production_order['end'];
							$po_line_table->insert($po_line_out);
							$po_line_out['No'] = $this->db->lastInsertId();
							$this->logger->info('po_line_out gespeichert: '.print_r($po_line_out, true));
						// Neue Inbound_Line erstellen, mit Daten der ursprünglichen Inbound_Line, aber neuer Variante und Losnummer, Eingangsmenge 0
							$new_inbound_line = $cur_inbound_line;
							$count_inb_line_set = $inb_line_table->fetchAll($inb_line_table->select()->where('inbound = ?', $cur_inbound_line['inbound']));
							$new_inbound_line['line'] = ($count_inb_line_set->count()+1)*10000;
							$new_inbound_line['variant'] = $new_variant['No'];
							$new_inbound_line['trading_units'] = 0;
							$new_inbound_line['packing_units'] = 0;
							$new_inbound_line['tu_pallet'] = $inventory['tu_pallet'];
							$new_inbound_line['lot'] = $inbound_line['lot'];
							$new_inbound_line['No'] = null;
							$new_inbound_line['qc_on_inventory'] = $inbound_line['qc_on_inventory'];
							$new_inbound_line['blocked'] = $inbound_line['blocked'];
							$inb_line_table->insert($new_inbound_line);
							$new_inbound_line['No'] = $this->db->lastInsertId();
							$this->logger->info('Neue Inbound_line: '.print_r($new_inbound_line, true));
							$inventory['inbound_line'] = $new_inbound_line['No'];
						// Movement Abgang der alten Inbound_line speichern
							$movement['No'] = 0;
							$movement['movement'] = 2;
							$movement['date'] = $date->toString('YYYY-MM-dd HH:mm:ss');
							$movement['trading_units'] = $inventory['trading_units'];
							$movement['packing_units'] = $inventory['trading_units'] * $cur_variant['items'];
							$movement['trading_units_production'] = $inventory['trading_units'];
							$movement['packing_units_production'] = $inventory['trading_units'] * $cur_variant['items'];
							$movement['status'] = 3;
							$movement['type'] = 2;
							$movement['stock_location'] = 1;
							$movement['remarks'] = 'Packauftrag bei Zählung';
							$movement['inbound_line'] = $cur_inbound_line['No'];
							$movement['out_order_line'] = $po_line_in['No'];
							$movement['outbound_line'] = $po_line_in['No'];
							$movement['inbound_movement'] = $inventory['movement'];
							$this->logger->info('Movement: '.print_r($movement, true));
							$movement_table->insert($movement);
							$movement['No'] = $this->db->lastInsertId();
							$this->logger->info('Bewegung wurde gespeichert mit No: '.$movement['No']);
						// Movement Zugang der neuen Inbound_Line speichern
							$movement['No'] = 0;
							$movement['movement'] = 1;
							$movement['date'] = $date->toString('YYYY-MM-dd HH:mm:ss');
							$movement['trading_units'] = $inventory['trading_units'];
							$movement['packing_units'] = $inventory['trading_units'] * $new_variant['items'];
							$movement['trading_units_production'] = $inventory['trading_units'];
							$movement['packing_units_production'] = $inventory['trading_units'] * $new_variant['items'];
							$movement['status'] = 3;
							$movement['type'] = 2;
							$movement['stock_location'] = 1;
							$movement['remarks'] = 'Packauftrag bei Zählung';
							$movement['in_order_line'] = $po_line_out['No'];
							$movement['inbound_line'] = $new_inbound_line['No'];
							$movement['out_order_line'] = 0;
							$movement['outbound_line'] = 0;
							$this->logger->info('Movement: '.print_r($movement, true));
							$movement_table->insert($movement);
							$movement['No'] = $this->db->lastInsertId();
							$movement['inbound_movement'] = $movement['No'];
							$movement_table->update($movement, array('No = ?'=>$movement['inbound_movement']));
							$this->logger->info('Bewegung wurde gespeichert mit No: '.$movement['No']);
						} else {
							$inbound_line_set->current()->qc_on_inventory = $inbound_line['qc_on_inventory'];
							$inbound_line_set->current()->save();
							$this->logger->info('QC qurde gespeichert');
						}
					// Inventory updaten und schreiben
						$inventory['date'] = $date->toString('YYYY-MM-dd HH:mm:ss');
						$this->logger->info(print_r($inventory, true));
						$inventory_table->insert($inventory);
						$inventory['No'] = $this->db->lastInsertId();
						$this->logger->info('Die Inventory wurde neu hinzugefügt: '.print_r($inventory, true));
					}
					$this->db->query('UPDATE inventory_head SET state = 1 WHERE No = ?', $inventory['inventory_head']);
					$this->db->commit();
				} catch (Exception $e) {
						$this->db->rollBack();
						$this->logger->err('Die Inventory wurde nicht gespeichert! '.$e->getMessage());
						$errors['all'] = 'Die Zählung wurde nicht gespeichert!';
				}
			}
		} else {
			$this->logger->info('Kein Post-Request!');
			$errors['all'] = 'Kein Post-Request!';
			$inventory = array();
		}
		$this->view->result = $inventory;
		$this->view->errors = $errors;
		$this->_helper->layout()->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');
	}
}