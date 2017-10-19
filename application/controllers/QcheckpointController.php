<?php

class qcheckpointController extends Zend_Controller_Action
{
	protected $db;
	
	public function init()
	{
    	$this->view->headTitle('Qualitätsmerkmale');
		$this->view->title = 'Qualitätsmerkmale';
		$this->db = Zend_Registry::get('db');
	}
	
	public function indexAction()
	{
        // action body
		Zend_Registry::get('logger')->info('qcheckpointController->IndexAction called');
		$this->view->result = $this->db->query("select * from v_qc_product")->fetchAll();
	}
	
	public function editAction()
	{
		$qc_table = new Application_Model_QualitycheckpointModel();
		$errors = array();
		if ($this->getRequest()->isPost()) {
			if ($no<>'') {
			// speichere Daten
				try {
					$qc_table->update($quality_checkpoint, $no);
				} catch (Exception $e) {
					$errors['all'] = 'Die Änderung wurde nicht gespeichert '.$e->getMessage();
				}
			} else {
			// füge Datensatz hinzu
				try {
					$qc_table->insert($quality_checkpoint);
				} catch (Exception $e) {
					$errors['all'] = 'Das Qualitätsmerkmal wurde nicht hinzugefügt '.$e->getMessage();
				}
			}
			if (count($errors)==0) $this->_redirect('qcheckpoint/index/');
		} else {
			($this->hasParam('No')) ? $no = $this->getParam('No') : $no ='';
			if ($no<>'')
				$quality_checkpoint = $qc_table->find($no)->current()->toArray();
			else
				$quality_checkpoint=array(
					'No'=>0,
					'quality_checkpoint'=>'',
					'checkpoint_class'=>'',
					'purchase_order'=>'',
					'max_good'=>0,
					'max_regular'=>0,
					'operator'=>0);
		}
		$params['data']=$quality_checkpoint;
		$params['title']='Qualitätsmerkmale';
		$params['errors']=$errors;
		$this->view->params=$params;
	}
	
	public function editqcproduct()
	{
		
	}
	
	public function getAction()
	{
		$params = array();
		$errors = array();
		if ($this->hasParam('No')) {
			$params['No']['value'] = $this->getParam('No');
			$params['No']['type'] = 'number';
		}
		if ($this->hasParam('product')) {
			$params['product']['value']=$this->getParam('product');
			$params['product']['type'] = 'string';
		}
		if ($this->hasParam('type')) {
			$params['type']['value']=$this->getParam('type');
			$params['type']['type'] = 'number';
		}
		if ($this->hasParam('qchk_class_no')) {
			$params['qchk_class_no']['value']=$this->getParam('qchk_class_no');
			$params['qchk_class_no']['type']='number';
		}
		$this->hasParam('connector') ? $connector = $this->getParam('connector') : $connector = 'AND';
		$sqlStr = 'SELECT * FROM v_qc_product';
		$pNo = 0;
		foreach ($params as $key => $val) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' AND ';
			$pNo++;
			$val['type']=='string' ? $sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val['value'].'%")' : $sqlStr.= $key.' = '.$val['value'];
		}
		$sqlStr.=' ORDER BY qchk_class_no, qck_no';
		try {
			$qc_products = $this->db->query($sqlStr)->fetchAll();
			if ((count($qc_products)==0) and (count($errors)==0)) {
				if (isset($params['product'])) unset($params['product']);
				$sqlStr = 'SELECT * FROM v_quality_checkpoint';
				$pNo = 0;
				foreach ($params as $key => $val) {
					($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' AND ';
					$pNo++;
					$val['type']=='string' ? $sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val['value'].'%")' : $sqlStr.= $key.' = '.$val['value'];
				}
				$sqlStr.=' ORDER BY qchk_class_no, qck_no';
				$qc_products = $this->db->query($sqlStr);
			}
		} catch (Exception $e) {
			$errors['sql'] = 'Daten konnten nicht geladen werden! '.$e->getMessage();
		}
		$this->view->setEscape('htmlentities');
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
}