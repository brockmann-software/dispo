<?php
class CertificateController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	
	public function init()
	{
		$this->view->headTitle = 'Certifikate';
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
	}
	
	public function vendorcertAction()
	{
		$certificate = array('IFS', 'GG', 'QS', 'BIO');
		$params = array();
		$uri = '/certificate/vendorcert';
		$searching = false;
		($this->hasParam('page')) ? $page = $this->getParam('page') : $page=1;
		if ($this->hasParam('No')) {
			$searching = true;
			$params['No']['value'] = $this->getParam('No');
			$params['No']['type'] = 'string';
		}
		if ($this->hasParam('name')) {
			$searching = true;
			$params['name']['value'] = $this->getParam('name');
			$params['name']['type'] = 'string';
		}
		if ($this->hasParam('street')) {
			$searching = true;
			$params['street']['value'] = $this->getParam('No');
			$params['street']['type'] = 'string';
		}
		if ($this->hasParam('PO_code')) {
			$searching = true;
			$params['PO_code']['value'] = $this->getParam('No');
			$params['PO_code']['type'] = 'string';
		}
		if ($this->hasParam('city')) {
			$searching = true;
			$params['city']['value'] = $this->getParam('No');
			$params['city']['type'] = 'string';
		}
		if ($this->hasParam('start')) {
			$start = $this->getParam('start');
			$uri.='/start/'.$start;
		} else $start = '';
		if ($this->hasParam('end')) {
			$end = $this->getParam('end');
			$uri.='/end/'.$end;
		} else $end = '';
		$due_qry = '';
		if ($start<>'' and $end<>'') $due_qry = "between {$start} and {$end}";
		if ($start<>'' and $end=='') $due_qry = "> {$start}";
		if ($start=='' and $end<>'') $due_qry = "< {$end}";
		$due_qry_str = "";
		$pNo = 0;		
		if ($due_qry<>'') {
			foreach ($certificate as $cert) {
				($pNo==0) ? $due_qry_str.= "({$cert} <> '' AND {$cert} {$due_qry})" : $due_qry_str.= " OR ({$cert} <> '' AND {$cert} {$due_qry})";
				$pNo++;
			}
		} else {
			foreach ($certificate as $cert) {
				($pNo==0) ? $due_qry_str.= "({$cert} <> '')" : $due_qry_str.= " OR ({$cert} <> '')";
				$pNo++;
			}
		}
		($this->hasParam('connector')) ? $connector = $this->getParam('connector') : $connector = 'AND';
		$pNo = 0;
		$sqlStr = '';
		foreach ($params as $key => $field) {
			($pNo==0) ? $sqlStr.='' : $sqlStr.=' '.$connector.' ';
			$uri.="/{$key}/{$field['value']}";
			$pNo++;
			if ($field['type'] == 'string') $sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$field['value'].'%")';
		}
		$select_cnt = $this->db->select();
		$select_cnt->from('v_vendor_cert_matrix', 'COUNT(No) AS VCCNT');
		if ($due_qry_str<>'') $select_cnt->where($due_qry_str);
		if ($sqlStr<>'') $select_cnt->where($sqlStr);
		$select = $this->db->select()->from('v_vendor_cert_matrix', '*');
		if ($due_qry_str<>'') $select->where($due_qry_str);
		if ($sqlStr<>'') $select->where($sqlStr);
		$select->limit(25, ($page-1)*25);
//		$this->logger->info($select_cnt->__toString());
//		$this->logger->info($select->__toString());
		$vc_count = $this->db->query($select_cnt)->fetchAll()[0]['VCCNT'];
		$vendor_certs = $this->db->query($select)->fetchAll();
		$this->view->vc_count = $vc_count;
		$this->view->searchparams = $params;
		$this->view->date_filter = array($start, $end);
		$this->view->vendor_certs = $vendor_certs;
		$this->view->cur_page = $page;
		$this->view->searching = $searching;
		$this->view->uri = $uri;
	}
	
	public function getAction()
	{
		$params = array();
		$errors = array();
		$results = array();
		if ($this->hasParam('No')) {
			$params['No']['value'] = $this->getParam('No');
			$params['No']['type'] = 'number';
		}
		if ($this->hasParam('certificate')) {
			$params['certificate']['value'] = $this->getParam('certificate');
			$params['certificate']['type'] = 'string';
		}
		$this->hasParam('connector') ? $connector = $this->getParam('connector') : $connector = 'AND';
		$sqlStr = 'select * from certification';
		$pNo = 0;
		foreach ($params as $key => $val) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' '.$connector.' ';
			$pNo++;
			$sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val.'%")';
		}
		try {
			$results = $this->db->query($sqlStr)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->results = $results;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');
	}
}
?>