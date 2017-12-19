<?php

class PurchaseorderController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	
	public function init()
	{
    	$this->view->headTitle('Bestellungen');
		$this->view->title = 'Bestellungen';
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
		$this->po_head = new Application_Model_PurchaseorderModel();
		$this->po_line = new Application_Model_PurchaseorderlineModel();
	}
	
	private function loadDependencies()
	{
		$dependencies['price_allocations'] = $this->db->query('SELECT * FROM price_allocation')->fetchAll();
		return $dependencies;
	}
	
	public function indexAction()
	{
        // action body
		Zend_Registry::get('logger')->info('purchaseorderController->IndexAction called');
		$select = $this->db->select()->from('v_purchase_order', '*');
		if ($this->hasParam('No')) $select->where('UPPER(No) LIKE UPPER(?)', '%'.$this->getParam('No').'%');
		$purchase_orders = $this->db->query($select)->fetchAll();
		$purchase_order_lines = $this->db->query("select * from v_po_line where purchase_order = ?", $purchase_orders[0]['No'])->fetchAll();
		$params = $this->loadDependencies();
		$this->view->params = $params;
		$this->view->result = $purchase_orders;
		$this->view->purchase_order_lines = $purchase_order_lines;
	}
	
	private function getProductFromTranslation($article)
	{
		$article_translation = $this->db->query('SELECT * FROM btm_translate_article WHERE btm_article = ?', $article)->fetchAll();
		if (count($article_translation)>0) return $article_translation[0]; else return false;
	}
	
	public function purchaseorderbtmdialogAction()
	{
		$errors = array();
		$polinesBTM = array();
		if ($this->hasParam('purchase_order')) {
			$params['purchase_order']['value'] = $this->getParam('purchase_order');
			$params['purchase_order']['type'] = 'string';
		}
		$db_btm = Zend_Registry::get('db_btm');
		try {
			$po_head = $db_btm->query('SELECT tbl_HAN.HANLFSNR FROM tbl_HAN WHERE tbl_HAN.HANLISKZ = 1 AND tbl_HAN.HANVORNR = ?', $params['purchase_order']['value'])->fetchAll();
			$sqlStr = 'SELECT tbl_HAN.HANVORNR, tbl_HAN.HANARTNR, tbl_HAN.HANPOSNR, tbl_HAN.HANADRNR, tbl_LIE.LIENAME, tbl_LIE.LIESTRAS, tbl_LIE.LIEPLZOR, convert(varchar(25), tbl_HAN.HANPRODT, 127) AS HANLISDT, convert(varchar(25), tbl_HAN.HANPRODT, 127) AS HANPRODT, HANTABZT, tbl_HAN.HANARTTX, tbl_HAN.HANWARGR, tbl_art.ARTGEBIN, tbl_ART.ARTDURGW, tbl_HAN.HANPMENG, tbl_ART.ARTURSLD';
			$sqlStr.= ' FROM tbl_HAN';
			$sqlStr.= ' JOIN tbl_ART on (tbl_HAN.HANARTNR = tbl_ART.KEYIART1)';
			$sqlStr.= ' join tbl_LIE on (tbl_han.HANADRNR = tbl_LIE.KEYILIE1)';
			$sqlStr.= ' WHERE tbl_HAN.HANVORAR = 30';
			$sqlStr.= ' AND tbl_art.ARTAKTKZ = \'A\'';
			if (isset($params['purchase_order'])) $sqlStr.= ' AND tbl_HAN.HANVORNR = \''.$params['purchase_order']['value'].'\'';
			$this->logger->info('SQL: '.$sqlStr);
			$stmnt = $db_btm->query($sqlStr);
			while ($poline = $stmnt->fetch()) {
				$poline['LIENAME'] = iconv('CP1252', 'UTF-8', trim($poline['LIENAME']));
				$poline['LIESTRAS'] = iconv('CP1252', 'UTF-8', trim($poline['LIESTRAS']));
				$poline['LIEPLZOR'] = iconv('CP1252', 'UTF-8', trim($poline['LIEPLZOR']));
				$poline['LIESTRAS'] = iconv('CP1252', 'UTF-8', trim($poline['LIESTRAS']));
				$poline['HANARTTX'] = iconv('CP1252', 'UTF-8', trim($poline['HANARTTX']));
				$poline['HANLFSNR'] = (count($po_head)>0) ? iconv('CP1252', 'UTF-8', trim($po_head[0]['HANLFSNR'])) : '';
				if (trim($poline['HANTABZT']) =='') $poline['HANTABZT'] = date('H:i');
				if ($article_translation = $this->getProductFromTranslation($poline['HANARTNR'])) {
					$poline['HANWARGR'] = $article_translation['product'];
				}
				$polinesBTM[] = $poline;
			}
			$this->logger->info('Bestellungen: '.print_r($polinesBTM, true));
		} catch (Exception $e) {
			$errors['db'] = 'Fehler bei Abfrage von Bestellungen';
			$this->logger->err('Fehler bei Abfrage von BTM-Bestellungen! '.$e->getMessage());
		}
		$this->view->errors = $errors;
		$this->view->results = $polinesBTM;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');
	}
	
	public function editAction()
	{
		$date = new Zend_Date();
		$errors = array();
		$purchase_order=array(
			'No'=>'',
			'vendor'=>'',
			'departure_date'=>$date->get(Zend_Date::DATES),
			'departure_time'=>$date->get(Zend_Date::TIME_SHORT),
			'arrival_date'=>$date->get(Zend_Date::DATES),
			'arrival_time'=>$date->get(Zend_Date::TIME_SHORT),
			'truck'=>'',
			'trailor'=>'',
			'container'=>'',
			'vessel'=>0,
			'habour'=>0,
			);
		$params['data']=$purchase_order;
		$params['title']='Bestellung';
		$params['errors']=$errors;
		$this->view->params=$params;
	}
	
	public function getpolineAction()
	{
		$errors=array();
		$polines=array();
		$select = $this->db->select()->from('v_po_line', '*');
		if ($this->hasParam('No')) $select->where('No = ?', $this->getParam('No'));
		if ($this->hasParam('purchase_order')) $select->where('UPPER(purchase_order) LIKE (?)', '%'.$this->getParam('purchase_order'));
		$polines = $this->db->query($select);
		$this->view->errors = $errors;
		$this->view->results = $polines;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
	}
	
	public function editpolineAction()
	{
		$errors=array();
		$polines=array();
		$poLineTable = new Application_Model_PurchaseorderlineModel();
		if ($this->getRequest()->isPost()) {
			isset($_POST['No']) ? $poLine['No'] = $_POST['No'] : $poLine['No'] = 0;
			isset($_POST['price']) ? $poLine['price'] = $_POST['price'] : $poLine['price'] = 0;
			isset($_POST['price_alloc']) ? $poLine['price_allocation'] = $_POST['price_alloc'] : $poLine['price_allocation'] = 0;
			if ($poLine['No'] == 0) $errors['No'] = 'Keine Nummer übergeben!';
			if (count($errors)==0) {
				$purchase_order_lines = $poLineTable->find($poLine['No']);
				if ($purchase_order_lines->count()>0) {
					try {
						$purchase_order_line = $purchase_order_lines->current();
						$purchase_order_line->Price = $poLine['price'];
						$purchase_order_line->Price_Allocation = $poLine['price_allocation'];
						$purchase_order_line->save();
					} catch (Exception $e) {
						$errors['all'] = 'Daten konnten nicht gespeichert werden!';
						$this->logger->err('Daten konnten nicht gespeichert werden! '.$e->getMessage());
					}
				} else $errors['all'] = 'Keine Bestellzeile gefunden zum Eintrag!';
			} else $errors['all'] = 'Kein POST Request gesendet!';
		}
		$this->view->errors = $errors;
		$this->view->result = $poLine;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');		
	}
	
	public function getAction()
	{
		$errors = array();
		$purchase_orders = array();
		$select = $this->db->select()->from('v_purchase_order', '*');
		if ($this->hasParam('No')) $select->where('No = ?', $this->getParam('No'));
		$purchase_orders = $this->db->query($select);
		$this->view->errors = $errors;
		$this->view->results = $purchase_orders;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
	}
	
	public function getpolinesbtmAction()
	{
		$errors = array();
		$polinesBTM = array();
		if ($this->hasParam('purchase_order')) {
			$params['purchase_order']['value'] = $this->getParam('purchase_order');
			$params['purchase_order']['type'] = 'string';
		}
		$db_btm = Zend_Registry::get('db_btm');
		try {
			$po_head = $db_btm->query('SELECT tbl_HAN.HANLFSNR FROM tbl_HAN WHERE tbl_HAN.HANLISKZ = 1 AND tbl_HAN.HANVORNR = ?', $params['purchase_order']['value'])->fetchAll();
			$sqlStr = 'SELECT tbl_HAN.HANVORNR, tbl_HAN.HANARTNR, tbl_HAN.HANPOSNR, tbl_HAN.HANADRNR, tbl_LIE.LIENAME, tbl_LIE.LIESTRAS, tbl_LIE.LIEPLZOR, convert(varchar(25), tbl_HAN.HANPRODT, 127) AS HANLISDT, HANTABZT, tbl_HAN.HANARTTX, tbl_HAN.HANWARGR, tbl_art.ARTGEBIN, tbl_ART.ARTDURGW, tbl_HAN.HANPMENG, tbl_ART.ARTURSLD';
			$sqlStr.= ' FROM tbl_HAN';
			$sqlStr.= ' JOIN tbl_ART on (tbl_HAN.HANARTNR = tbl_ART.KEYIART1)';
			$sqlStr.= ' join tbl_LIE on (tbl_han.HANADRNR = tbl_LIE.KEYILIE1)';
			$sqlStr.= ' WHERE tbl_HAN.HANVORAR = 30';
			$sqlStr.= ' AND tbl_art.ARTAKTKZ = \'A\'';
			if (isset($params['purchase_order'])) $sqlStr.= ' AND tbl_HAN.HANVORNR = \''.$params['purchase_order']['value'].'\'';
			$this->logger->info('SQL: '.$sqlStr);
			$stmnt = $db_btm->query($sqlStr);
			while ($poline = $stmnt->fetch()) {
				$poline['LIENAME'] = iconv('CP1252', 'UTF-8', trim($poline['LIENAME']));
				$poline['LIESTRAS'] = iconv('CP1252', 'UTF-8', trim($poline['LIESTRAS']));
				$poline['LIEPLZOR'] = iconv('CP1252', 'UTF-8', trim($poline['LIEPLZOR']));
				$poline['LIESTRAS'] = iconv('CP1252', 'UTF-8', trim($poline['LIESTRAS']));
				if (trim($poline['HANTABZT']) =='') $poline['HANTABZT'] = date('H:i');
				$poline['HANLFSNR'] = (count($po_head)>0) ? iconv('CP1252', 'UTF-8', trim($po_head[0]['HANLFSNR'])) : '';
				if ($article_translation = $this->getProductFromTranslation($poline['HANARTNR'])) {
					$poline['HANWARGR'] = $article_translation['product'];
				}
				$polinesBTM[] = $poline;
			}
			$this->logger->info('Bestellungen: '.print_r($polinesBTM, true));
		} catch (Exception $e) {
			$errors['db'] = 'Fehler bei Abfrage von Bestellungen';
			$this->logger->err('Fehler bei Abfrage von BTM-Bestellungen! '.$e->getMessage());
		}
		$this->view->errors = $errors;
		$this->view->results = $polinesBTM;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
	}
}