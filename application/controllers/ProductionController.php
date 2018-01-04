<?php
class productionController extends Zend_Controller_Action
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
	
	private function loadDependecies()
	{
		$dependencies['machines'] = $this->db->query('SELECT * from machine')->fetchAll();
		$dependencies['products'] = $this->db->query('SELECT * FROM products')->fetchAll();
		$dependencies['countries'] = $db->query("select * from country")->fetchAll();
		$dependencies['packagings'] = $this->db->query('SELECT * FROM base_packaging')->fetchAll();
		$dependencies['t_packagings'] = $this->db->query('SELECT * FROM tp_format')->fetchAll();
		$dependencies['labels'] = $this->db->query('SELECT * FROM label')->fetchAll();
		$dependencies['qualities'] = $db->query("select * from quality")->fetchAll();
		$dependencies['brands'] = $db->query("select * from brand")->fetchAll();
		$dependencies['quality_classes'] = $db->query("select * from quality_class")->fetchAll();
		$dependencies['pallets']=$db->query("SELECT * FROM pallet")->fetchAll();
		return $dependencies;
	}
	
	public function indexAction()
	{
		$page = 1;
		$statement = $this->db->select();
		$statement->from('production_order');
		$statement->order('No DESC');
		$statement->limit(20, $page-1);
		$production_orders = $this->db->query($statement)->fetchAll();
//		$this->logger->info('Production_orders: '.print_r($production_orders, true));
//		Zend_Debug::dump($production_orders);
		if (count($production_orders)>0) $production_order_lines = $this->db->query('SELECT * FROM v_production_order_line WHERE production_order = ?', $production_orders[0]['No']);
		else $production_order_lines = array();
		
		$this->view->production_orders = $production_orders;
		$this->view->production_order_lines = $production_order_lines;
	}
	
	public function getlinesAction()
	{
		$errors = array();
		$select = $this->db->select();
		$select->from('v_production_order_line', '*');
		if ($this->hasParam('No')) $select->where('No=?', $this->getParam('No'));
		if ($this->hasParam('production_order')) $select->where('production_order=?', $this->getParam('production_order'));
		$production_order_lines = $this->db->query($select)->fetchAll();
		$this->view->results = $production_order_lines;
		$this->view->errors = $errors;
		$this->_helper->layout()->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');
	}
}