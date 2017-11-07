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
	
	public function indexAction()
	{
        // action body
		Zend_Registry::get('logger')->info('purchaseorderController->IndexAction called');
		$this->view->result = $this->db->query("select * from v_purchase_order limit 20")->fetchAll();
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
			$sqlStr = 'SELECT tbl_HAN.HANVORNR, tbl_HAN.HANPOSNR, tbl_HAN.HANADRNR, tbl_LIE.LIENAME, tbl_LIE.LIESTRAS, tbl_LIE.LIEPLZOR, convert(varchar(25), tbl_HAN.HANLISDT, 127) AS HANLISDT, convert(varchar(25), tbl_HAN.HANPRODT, 127) AS HANPRODT, HANTABZT, tbl_HAN.HANARTTX, tbl_HAN.HANWARGR, tbl_art.ARTGEBIN, tbl_ART.ARTDURGW, tbl_HAN.HANPMENG, tbl_ART.ARTURSLD';
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
		$this->hasParam('id') ? $id = $this->getParam('id') : $id = '';
		$sqlStr = 'select * from v_po_line';
		if ($id<>'') $sqlStr.=' where purchase_order='.$id;
		try {
			$polines = $this->db->query($sqlStr)->fetchAll();
		} catch (Exception $e) {
			$errors['all']=$e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->results = $polines;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
	}
	
	public function getAction()
	{
		$errors = array();
		$purchase_orders = array();
		$this->hasParam('No') ? $no = $this->getParam('No') : $no = 0;
		$sqlStr = 'SELECT * FROM v_purchase_order';
		if ($no<>'') $sqlStr.=' WHERE No = '.$no;
		try {
			$purchase_orders = $this->db->query($sqlStr);
		} catch (Exception $e) {
			$errors['all']=$e->getMessage();
		}
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
			$sqlStr = 'SELECT tbl_HAN.HANVORNR, tbl_HAN.HANPOSNR, tbl_HAN.HANADRNR, tbl_LIE.LIENAME, tbl_LIE.LIESTRAS, tbl_LIE.LIEPLZOR, convert(varchar(25), tbl_HAN.HANLISDT, 127) AS HANLISDT, HANTABZT, tbl_HAN.HANARTTX, tbl_HAN.HANWARGR, tbl_art.ARTGEBIN, tbl_ART.ARTDURGW, tbl_HAN.HANPMENG, tbl_ART.ARTURSLD';
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