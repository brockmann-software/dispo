<?php

class IndexController extends Zend_Controller_Action
{
	protected $db;
	
    public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Disposition');
		$this->view->title = 'Disposition';
		$this->db = Zend_Registry::get('db');
    }

    public function indexAction()
    {
        // action body
		Zend_Registry::get('logger')->info('IndexController->IndexAction called');
		$qm_dasboard = $this->db->query('SELECT * FROM cert_status_overview')->fetchAll();
		$qm_db_inbounds = $this->db->query("SELECT count(No) as cnt_inbqm FROM v_inb_line where (ifs<>'' and ifs<now()) or (GG<>'' and GG < now()) or (QS<>'' and QS < now()) or (BIO<>'' and BIO<now())")->fetchAll()[0];
		$ib_dashboard['inbounds_td'] = $this->db->query('SELECT COUNT(No) as cntib FROM v_inb_line WHERE DATE(inb_arrival) = CURDATE()')->fetchAll()[0];
		$whm_dashboard['last_inv_comp'] = $this->db->query('SELECT * from inventory_head WHERE state = 2 ORDER BY ended DESC')->fetchAll();
		$whm_dashboard['act_inventory'] = $this->db->query('SELECT * FROM inventory_head where state < 2 ORDER BY started DESC')->fetchAll();
		$this->view->qm_dashboard = $qm_dasboard;
		$this->view->qm_db_inbounds = $qm_db_inbounds['cnt_inbqm'];
		$this->view->ib_dashboard = $ib_dashboard;
		$this->view->whm_dashboard = $whm_dashboard;	
    }
	
	public function alternateAction()
	{
		Zend_Registry::get('logger')->info('IndexController->AlternateAction called');
		$layout = $this->_helper->layout();
		$layout->setLayout('alternate');
	}
}
