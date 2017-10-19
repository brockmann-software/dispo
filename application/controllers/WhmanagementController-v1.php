<?php
class WhmanagementController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	
	private function loadDependencies($filter=true)
	{
		$dependencies['rooms'] = $this->db->query('SELECT * FROM room WHERE stock_location = 1')->fetchAll();
		if ($filter) $dependencies['rooms'][count($dependencies['rooms'])] = array('No'=>0, 'room'=>'--alle--', 'stock_location'=>1);
		$dependencies['products'] = $this->db->query('SELECT * FROM product')->fetchAll();
		if ($filter) $dependencies['products'][count($dependencies['products'])] = array('No'=>'', 'product'=>'--alle--');
		$dependencies['inventories'] = $this->db->query('SELECT * FROM inventory_head')->fetchAll();
		$dependencies['packagings'] = $db->query("select * from packaging")->fetchAll();
		$dependencies['t_packagings'] = $db->query("select * from tp_format")->fetchAll();
		$dependencies['labels'] = $db->query("select * from label")->fetchAll();
		$dependencies['qualities'] = $db->query("select * from quality")->fetchAll();
		$dependencies['brands'] = $db->query("select * from brand")->fetchAll();
		$dependencies['quality_classes'] = $db->query("select * from quality_class")->fetchAll();
		return $dependencies;
	}
	
	public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Lager Management');
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
    }

	public function indexAction()
	{
		$inventory_heads = $this->db->query('SELECT * FROM inventory_head');
		$this->view->data = $inventory_heads;
	}

	public function editAction()
	{
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
		if ($this->hasParam('No')) {
			$inventory_heads = $inventory_head_table->find($this->getParam('No'));
			if (($inventory_heads->count()==0) || ($inventory_heads->current()->state > 1)) {
				$errors['all'] = 'Die Zählung existiert nicht oder wurde schon abgeschlossen!';
				$this->logger->err('Die Zählung existiert nicht oder wurde schon abgeschlossen! '.print_r($inventory_heads, true));
			} else {
				$inventory_head = $inventory_heads->current()->toArray();
			}
		} else {
			try {
				$inventory_head['date'] = $date->toString('YYYY-MM-dd HH:mm:ss');
				$inventory_head['state'] = 0;
				$inventory_head['started'] = $inventory_head['date'];
				$inventory_head_table->insert($inventory_head);
				$inventory_head['No'] = $this->db->lastInsertId();
				$sql = 'INSERT INTO inventory';
				$sql.= '(inventory_head,';
				$sql.= ' inbound_line,';
				$sql.= ' date,';
				$sql.= ' room,';
				$sql.= ' rack,';
				$sql.= ' level,';
				$sql.= ' pallets,';
				$sql.= ' tu_pallet,';
				$sql.= ' trading_units)';
				$sql.= 'SELECT';
				$sql.= ' ?,';
				$sql.= ' inbound_line.No,';
				$sql.= ' NOW(),';
				$sql.= ' "0",';
				$sql.= ' "0",';
				$sql.= ' "0",';
				$sql.= ' "0",';
				$sql.= ' inbound_line.tu_pallet,';
				$sql.= ' "0" ';
				$sql.= 'FROM inbound_line';
				$this->logger->info('SQL: '.$sql);
				$statement = $this->db->query($sql, $inventory_head['No']);
	//			$statement->execute();
				$this->logger->info('Inventory_Head erstellt: '.print_r($inventory_head, true));
	//			$this->logger->info(print_r($statement, true));
			} catch (Exception $e) {
				$errors['all'] = 'Ein Fehler ist bei dem Erstellen der Zählung aufgetreten!';
				$this->logger->err($e->getMessage());
			}
		} 
		if (count($errors)==0) try {
			$inventory_lines = $this->db->query('SELECT * from v_inventory WHERE inventory_head = ? ORDER BY position, inbound_line, No', $inventory_head['No'])->fetchAll();
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
		$sqlStr .=' ORDER BY position, inbound_line, No';
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
		$inventory_table = new Application_Model_InventoryModel();
		if ($this->getRequest()->isPost()) {
			$this->logger->info('Post-Request mit $_POST: '.print_r($_POST, true));
			$result = $_POST;
			isset($_POST['No']) ? $inventory_No = $_POST['No'] : $inventory_No = 0;
			isset($_POST['inventory_head']) ? $inventory['inventory_head'] = $_POST['inventory_head'] : $inventory['inventory_head'] = 0;
			isset($_POST['inbound_line']) ? $inventory['inbound_line'] = $_POST['inbound_line'] : $inventory['inbound_line'] = 0;
			if (isset($_POST['date'])) $inventory['date'] = $_POST['date'];
			isset($_POST['room']) ? $inventory['room'] = $_POST['room'] : $inventory['room'] = 0;
			isset($_POST['rack']) ? $inventory['rack'] = $_POST['rack'] : $inventory['rack'] = 0;
			isset($_POST['level']) ? $inventory['level'] = $_POST['level'] : $inventory['level'] = 0;
			isset($_POST['pallets']) ? $inventory['pallets'] = $_POST['pallets'] : $inventory['pallets'] = 0;
			isset($_POST['tu_pallet']) ? $inventory['tu_pallet'] = $_POST['tu_pallet'] : $inventory['tu_pallet'] = 0;
			isset($_POST['trading_units']) ? $inventory['trading_units'] = $_POST['trading_units'] : $inventory['trading_units'] = 0;
			isset($_POST['default_TU']) ? $default_tradingUnits = $_POST['default_TU'] : $default_tradingUnits = 0;
			isset($_POST['overwrite']) ? $overwrite = ($_POST['overwrite'] == 'true') : $overwrite = false;
			if ($inventory['inventory_head'] == 0) $errors['inventory_head'] = 'Inventory_Head darf nicht leer oder 0 sein!';
			if ($inventory['inbound_line'] == 0) $errors['inbound_line'] = 'Inbound_Line darf nicht leer oder 0 sein!';
			$this->logger->info('Fehler: '.print_r($errors, true));
			if (count($errors)==0) {
				$this->db->beginTransaction();
				try {
					if ($inventory_No<>0) {
						$dev_inventory = $inventory_table->find($inventory_No)->current()->toArray();
						$this->logger->info('dev_inventory: '.print_r($dev_inventory, true));
						$this->logger->info('default_tradingUnits: '.print_r($default_tradingUnits, true));
						$this->logger->info('overwrite: '.print_r($overwrite, true));
						if (($default_tradingUnits <> $dev_inventory['trading_units']) and ($overwrite==false)) {
							$inventory['race_condition'] = $dev_inventory['trading_units'];
							$this->logger->info('Race Condition für Inventory: '.print_r($inventory, true));
							$this->logger->info('Gespeicherte Werte: '.print_r($dev_inventory, true));
							throw new Exception('Der Wert wurde bereits von einem anderen Benutzer auf '.$dev_inventory['trading_units'].'geändert!');
						}
						$inventory_table->update($inventory, array('No = ?' => $inventory_No));
						$inventory['No'] = $inventory_No;
						$this->logger->info('Die Inventory wurde gespeichert: '.print_r($inventory, true));
					} else {
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