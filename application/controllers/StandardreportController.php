<?php
class StandardreportController extends Zend_Controller_Action
{
	protected $db;
	protected $config;
	protected $logger;
	
	public function init()
	{
		$this->db = Zend_Registry::get('db');
		$this->config = Zend_Registry::get('config');
		$this->logger = Zend_Registry::get('logger');
	}
	
	public function indexAction()
	{
	}
	
	public function getstatusAction()
	{
		$errors = array();
		$report_status = array();
		
		$select = $this->db->select()->from('v_report_log');
		($this->hasParam('document')) ? $select->where('document = ?', $this->getParam('document')) : $errors['all'] = 'Kein Dokument übergeben!';
		($this->hasParam('standard_report')) ? $select->where('standard_report = ?', $this->getParam('standard_report')) : $errors['all'] = 'Kein Report übergeben!';
		if (count($errors)==0) {
			$report_status = $this->db->query($select)->fetchAll();
		}
		$this->view->errors = $errors;
		$this->view->results = $report_status;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');	
		$this->renderScript('/xml/resultlistxml.phtml');		
	}
}