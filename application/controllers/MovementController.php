<?php
class MovementController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	protected $config;
	
	public function init()
	{
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
		$this->config = Zend_Registry::get('config');
	}
	
	public function movementdialogAction()
	{
		$errors = array();
		$data = array();
		$inboundLineTable = new Application_Model_InboundlineModel();
		if ($this->hasParam('inbound_line')) {
			$inbound_lines = $inboundLineTable->find($this->getParam('inbound_line'));
			if ($inbound_lines->count()==1) {
				$inbound_line = $inbound_lines->current();
				$select = $this->db->select();
				$movements = $this->db->query($select->from('v_movements', '*')->where('inbound_line=?', $inbound_line->No)->order('No'));
			} else $errors['all'] = 'Kein Wareneingang zum Parameter gefunden!';
		} else $errors['all'] = 'Kein Parameter übergeben!';
		if (count($errors)==0)
			$data = $movements;
		$this->view->errors = $errors;
		$this->view->data = $data;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');		
	}
}