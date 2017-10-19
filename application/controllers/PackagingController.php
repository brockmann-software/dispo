<?php
class PackagingController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	
	public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Produktgruppen');
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
		
    }

	public function indexAction()
	{
		$packagingTable = new Application_Model_packagingModel();
		$packagings = $packagingTable->fetchAll()->toArray();
		$this->view->packagings = $packagings;
	}

	public function editAction()
	{
		$packagingTable = new Application_Model_PackagingModel();
		if ($this->getRequest()->isPost()) {
			$error = array();
			isset($_POST['old_No']) ? $old_No = $_POST['old_No'] : $old_No = 0;
			isset($_POST['No']) ? $packaging['No'] = $_POST['No'] : $packaging['No'] = '';
			isset($_POST['packaging']) ? $packaging['packaging'] = $_POST['packaging'] : $packaging['packaging'] = '';
			isset($_POST['length']) ? $packaging['length'] = $_POST['length'] : $packaging['length'] = '';
			isset($_POST['width']) ? $packaging['width'] = $_POST['width'] : $packaging['width'] = '';
			isset($_POST['height']) ? $packaging['height'] = $_POST['height'] : $packaging['height'] = '';
			isset($_POST['weight']) ? $packaging['weight'] = $_POST['weight'] : $packaging['weight'] = '';
		
			if ($packaging['packaging'] == '') $error['packaging'] = 'Verpackung darf nicht leer sein';
			if ($packaging['weight'] == '') $error['weight'] = 'Es muss ein Gewicht angegeben werden';
			$this->logger->info('Verpackung: '.print_r($packaging, true));
			if (count($error)==0) {
				if ($old_No == 0) {
					try {
						$packagingTable->Insert($packaging);
					} catch (Exception $e) {
						$this->logger->err('Verpackung wurde nicht hinzugefügt! '.$e->getMessage());
						$error['all'] = 'Verpackung wurde nicht hinzugefügt! '.$e->getMessage();
					}
				} else {
					try {
						$packagingTable->update($packaging, array('No = ?' => $old_No));
					} catch (Exception $e) {
						$this->logger->err('Verpackung wurde nciht gespeichert! '.$e->getMessage());
						$error['all'] = 'Verpackung wurde nicht gespeichert! '.$e->getMessage();
					}
				}
				if (count($error)==0) $this->_redirect('/packaging/index/');
			}
		} else {
			$No = $this->getParam('No');
			$error = array();
			if (empty($No) == false) {
				$packaging = $packagingTable->find($No)->current()->toArray();
			} else {
				$packaging = array('No' => 0, 
								   'packaging' => '', 
								   'length' => 0,
								   'width'=>0,
								   'heigth'=>0,
								   'weight'=>0,
								   'DSD_type'=>0);
			}
		}
		$params['packaging'] = $packaging;
		$params['errors'] = $error;
		$params['title'] = 'Verpackung';
		//$this->view->form = $this->buildForm($product);
		$this->view->params = $params;
	}
	
}