<?php

class qcheckpointController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	protected $config;
	
	public function init()
	{
    	$this->view->headTitle('Qualitätsmerkmale');
		$this->view->title = 'Qualitätsmerkmale';
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
		$this->config = Zend_Registry::get('config');
	}
	
	private function loadDependencies()
	{
		$dependencies['products'] = $this->db->query('SELECT * FROM product')->fetchAll();
		$dependencies['qcheckpoint_classes'] = $this->db->query('SELECT * FROM qcheckpoint_class')->fetchAll();
		$dependencies['qcheckpoint_types'] = $this->db->query('select * FROM qcheckpoint_type')->fetchAll();
		$dependencies['quality_checkpoints'] = $this->db->query('SELECT * FROM v_quality_checkpoint WHERE type=0')->fetchAll();
		$dependencies['certificates'] = $this->db->query('SELECT * FROM certification WHERE traceability = 1')->fetchAll();
		$dependencies['certificates'][] = array('No'=>0, 'certificate'=>'-- alle --');
		$dependencies['operators'] = array(	0=>array('No'=>0, 'operator'=>'Differenz'),
											1=>array('No'=>1, 'operator'=>'Prozent'),
											2=>array('No'=>2, 'operator'=>'Logisch'));
		return $dependencies;
	}
	
	public function indexAction()
	{
		$quality_checkpoints = $this->db->query("SELECT * FROM v_quality_checkpoint ORDER BY type, No")->fetchAll();
		$this->view->data = $quality_checkpoints;
	}
	
	public function editgeneralAction()
	{
		$errors = array();
		$this->logger->info('POST: '.print_r($_POST, true));
		$qcheckpointTable = new Application_Model_QualitycheckpointModel();
		if ($this->getRequest()->isPost()) {
			isset($_POST['curNo']) ? $curNo = $_POST['curNo'] : $curNo = 0;
			isset($_POST['quality_checkpoint']) ? $qcheckpoint['quality_checkpoint'] = $_POST['quality_checkpoint'] : $qcheckpoint['quality_checkpoint']='';
			isset($_POST['type']) ? $qcheckpoint['type'] = $_POST['type'] : $qcheckpoint['type'] = 0;
			isset($_POST['checkpoint_class']) ? $qcheckpoint['checkpoint_class'] = $_POST['checkpoint_class'] : $qcheckpoint['checkpoint_class'] = 0;
			isset($_POST['max_good']) ? $qcheckpoint['max_good'] = $_POST['max_good'] : $qcheckpoint['max_good'] = 0;
			isset($_POST['max_regular']) ? $qcheckpoint['max_regular'] = $_POST['max_regular'] : $qcheckpoint['max_regular'] = 0;
			isset($_POST['operator']) ? $qcheckpoint['operator'] = $_POST['operator'] : $qcheckpoint['operator'] = 0;
			if (isset($_POST['certificate'])) if ($_POST['certificate']<>0) $qcheckpoint['certificate'] = $_POST['certificate']; 
			if ($qcheckpoint['quality_checkpoint']=='') $errors['quality_checkpoint'] = 'Prüfpunkt darf nicht leer sein!';
			if (($qcheckpoint['operator']<2) && ($qcheckpoint['max_good'] == 0)) $errors['max_good']='Der Maximalwert für Gut darf nicht 0 sein!';
			if (($qcheckpoint['operator']<2) && ($qcheckpoint['max_regular'] == 0)) $errors['max_regular']='Der Maximalwert für Gut darf nicht 0 sein!';
			if (($qcheckpoint['operator']<2) && ($qcheckpoint['max_regular']<=$qcheckpoint['max_good'])) $errors['max_regular']='Der Wert muss größer sein als der Maximalwert für Gut!';
			if (count($errors)==0) try {
				if ($curNo<>0) {
					$qcheckpointTable->update($qcheckpoint, array('No=?'=>$curNo));
					$this->logger->info('Änderungen für qcheckpoint wurden gespeichert');
				} else {
					$qcheckpointTable->insert($qcheckpoint);
					$this->logger->info('Qcheckpoint wurde hinzugefügt');
				}
				$this->_redirect('/qcheckpoint/index');
			} catch (Exception $e){
				$this->logger->err('Fehler beim speichern des Checkpoints '.print_r($qcheckpoint, true).' '.$e->getMessage());
			}
			$this->logger->err('Fehler: '.print_r($errors, true));
			$this->logger->err('Qcheckpoint: '.print_r($qcheckpoint, true));
			$qcheckpoint['No'] = $curNo;
		} else {
			if ($this->hasParam('No')) {
				$RSqcheckpoints = $qcheckpointTable->find($this->getParam('No'));
				if ($RSqcheckpoints->count()>0) $Rqcheckpoint = $RSqcheckpoints->current(); else $Rqcheckpoint = $qcheckpointTable->createRow();
			} else $Rqcheckpoint = $qcheckpointTable->createRow();
			$qcheckpoint = $Rqcheckpoint->toArray();
			if (!isset($qcheckpoint['certificate'])) $qcheckpoint['certificate']=0;
		}
		$params = $this->loadDependencies();
		$params['errors'] = $errors;
		$this->view->params = $params;
		$this->view->data = $qcheckpoint;
	}

	public function editqcproductAction()
	{
		$errors = array();
		$this->hasParam('product') ? $product = $this->getParam('product') : $product = '';
		$qc_product = $this->db->query('SELECT * FROM product WHERE No LIKE ?', $product.'%')->fetchAll();
		if (count($qc_product)>0) {
			$qc_products = $this->db->query('SELECT * FROM v_qc_product WHERE product_no=?', $qc_product[0]['No']);
		} else $qc_products = array();
		$params = $this->loadDependencies();
		$params['errors'] = $errors;
		$this->view->params = $params;
		$this->view->data=$qc_products;
		$this->view->qc_product = $qc_product[0];
	}
	
	public function deleteqcheckpointAction()
	{
		$qcheckpointTable = new Application_Model_QcheckpointproductModel();
		$this->logger->info('POST: '.print_r($_POST, true));
		$errors = array();
		$qc_product = array();
		if ($this->getRequest()->isPost()) {
			if (isset($_POST['delete']) && $_POST['delete']=='DELETE') $delete = true; else $errors['all'] = 'Parameter fehlt!';
			isset($_POST['No']) ? $No = $_POST['No'] : $errors['No'] = 'Kein eindeutiger Schlüssel übergeben!';
			if (count($errors)==0) {
				try {
					$RSqcheckpoints = $qcheckpointTable->find($No);
					$this->logger->info('Anzahl Checkpoints gefunden: '.print_r($RSqcheckpoints->count(), true));
					if ($RSqcheckpoints->count()>0) {
						$qc_product = $RSqcheckpoints->current()->toArray();
						$RSqcheckpoints->current()->delete();
						$this->logger->info('Prüfpunkt wurde gelöscht. '.print_r($qc_product, true));
					} else $errors['No'] = "Prüfpunkt mit Schlüssel $No nicht vorhanden!";
				} catch (Exception $e) {
					$this->logger->err('Qcheckpoint kann nicht gelöscht werden! '.$e->getMessage());
					$errors['all'] = 'Prüfpunkt konnte nicht gelöscht werden!';
				}
			}
		} else $errors['all'] = 'Es wurde nicht aus dem Formular heraus aufgerufen!';
		$this->view->errors = $errors;
		$this->view->result = $qc_product;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');		
	}
		
	
	public function updateqcproductAction()
	{
		$qc_product_table = new Application_Model_QcheckpointproductModel();
		$errors = array();
		if ($this->getRequest()->isPost()) {
			$this->logger->info('POST: '.print_r($_POST, true));
			isset($_POST['No']) ? $qc_No = $_POST['No'] : $qc_No = 0;
			isset($_POST['product']) ? $qc_product['product'] = $_POST['product'] : $qc_product['product'] = '';
			isset($_POST['quality_checkpoint']) ? $qc_product['quality_checkpoint'] = $_POST['quality_checkpoint'] : $qc_product['quality_checkpoint'] = 0;
			isset($_POST['max_good']) ? $qc_product['max_good'] = $_POST['max_good'] : $qc_product['max_good'] = 0;
			isset($_POST['max_regular']) ? $qc_product['max_regular'] = $_POST['max_regular'] : $qc_product['max_regular'] = 0;
			isset($_POST['operator']) ? $qc_product['operator'] = $_POST['operator'] : $qc_product['operator'] = 0;
			$this->logger->info('qc_product: '.print_r($qc_product, true));
			if ($qc_No === 0) $errors['No'] = 'Nummer darf nicht 0 oder leer sein!';
			if ($qc_product['product'] =='') $errors['product'] = 'Produkt darf nicht leer sein!';
			if ($qc_product['quality_checkpoint'] == 0) $errors['qc'] = 'Prüfpunkt darf nicht 0 oder leer sein!';
			if (count($errors)==0) try {
				if ($qc_No == 'new') {
					$qc_product_table->insert($qc_product);
					$qc_No = $this->db->lastInsertId();
				} else {
					unset($qc_product['product']);
					unset($qc_product['quality_checkpoint']);
					$qc_product_table->update($qc_product, array('No'=>$qc_No));
				}
				$qc_product = $this->db->query('SELECT * FROM v_qc_product WHERE No = ?', $qc_No)->fetchAll()[0];
			} catch (Exception $e) {
				$errors['all'] = 'Der Prüfpunkt wurde nicht gespeichert';
				$this->logger->err('Prüfpunkt nicht gespeichert! '.$e->getMessage());
			}	
		} else $errors['all'] = 'Die Seite muss als POST-Request aufgerufen werden!';
		$this->view->errors = $errors;
		$this->view->result = $qc_product;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');		
	}
	
	public function setallgeneralforproductAction()
	{
		$qc_product_table = new Application_Model_QcheckpointproductModel();
		if ($this->hasParam('product')) {
			$product = $this->getParam('product');
			$quality_checkpoints = $this->db->query('SELECT * FROM v_quality_checkpoint WHERE type=0')->fetchAll();
			foreach ($quality_checkpoints as $quality_checkpoint) {
				$qc_products = $qc_product_table->fetchAll($qc_product_table->select()->where('product = ?', $product)->where('quality_checkpoint = ?', $quality_checkpoint['No']));
				if ($qc_products->count()==0) {
					$qc_product = $qc_product_table->createRow();
					$qc_product->product = $product;
					$qc_product->quality_checkpoint = $quality_checkpoint['No'];
					$qc_product->max_good = $quality_checkpoint['max_good'];
					$qc_product->max_regular = $quality_checkpoint['max_regular'];
					$qc_product->operator = $quality_checkpoint['operator'];
					$qc_product->save();
				}
			}
		} else $product = '';
		$this->_redirect('/qcheckpoint/editqcproduct/product/'.$product);
	}
	
	private function createWhere($select, $product=true)
	{
		if ($this->hasParam('No')) {
			$select->where('No = ?', $this->getParam('No'));
		}
		if ($product && $this->hasParam('product')) {
			$select->where('UPPER(product) LIKE UPPER(?)', '%'.$this->getParam('product').'%');
		}
		if ($this->hasParam('type')) {
			$select->where('type = ?', $this->getParam('type'));
		}
		if ($this->hasParam('qchk_class_no')) {
			$select->where('qchk_class_no = ?', $this->getParam('qchk_class_no'));
		}
		if ($this->hasParam('certificate')) {
			$select->where('certificate = ? OR certificate is null', $this->getParam('certificate'));
		} else {
			$select->where('certificate is null');
		}
		return $select;
	}
	
	
	public function getAction()
	{
				
		$params = array();
		$errors = array();
		$select = $this->db->select()->from('v_qc_product');
		$select = $this->createWhere($select);
		try {
			$qc_products = $this->db->query($select)->fetchAll();
			if ((count($qc_products)==0) and (count($errors)==0)) {
				$select = $this->db->select()->from('v_quality_checkpoint');
				$select = $this->createWhere($select, false);
				$qc_products = $this->db->query($select)->fetchAll();
			}
		} catch (Exception $e) {
			$errors['sql'] = 'Daten konnten nicht geladen werden! '.$e->getMessage();
		}
//		$this->view->setEscape('htmlentities');
		$this->view->errors = $errors;
		$this->view->results = $qc_products;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');		
	}
	
	public function getbasicrecAction()
	{
		$errors = array();
		$results = $this->db->query("SELECT * FROM v_quality_checkpoint")->fetchAll();
		$this->view->errors = $errors;
		$this->view->results = $results;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
	}
	
	public function getqcproductAction()
	{
		$errors=array();
		if ($this->hasParam('product')) {
			$results = $this->db->query("SELECT * FROM v_qc_product WHERE product_no = ?", $this->getParam('product'))->fetchAll();
		} else $errors['product'] = "Kein Produkt übergeben!";
		$this->view->results = $results;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');
	}
}