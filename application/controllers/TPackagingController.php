<?php
class TPackagingController extends Zend_Controller_Action
{
	protected $db;
	
	public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Produktgruppen');
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
    }
	
	private function loadDependencies()
	{
		$dependencies['tp_types'] = $this->db->query("SELECT * FROM tp_type");
		return $dependencies;
	}

	public function indexAction()
	{
		$t_packagings=$this->db->query("SELECT * FROM v_t_packaging")->fetchAll();
		$this->view->t_packagings = $t_packagings;
	}

	public function editAction()
	{
		$t_packagingTable = new Application_Model_TPackagingModel();
		if ($this->getRequest()->isPost()) {
			$error = array();
			isset($_POST['old_No']) ? $old_No = $_POST['old_No'] : $old_No = '';
			isset($_POST['no']) ? $t_packaging['no'] = $_POST['no'] : $t_packaging['no'] = '';
			isset($_POST['transport_packaging']) ? $t_packaging['transport_packaging'] = $_POST['transport_packaging'] : $t_packaging['transport_packaging'] = '';
			isset($_POST['tp_type']) ? $t_packaging['tp_type'] = $_POST['tp_type'] : $t_packaging['tp_type'] = 1;
			isset($_POST['length']) ? $t_packaging['length'] = $_POST['length'] : $t_packaging['length'] = '';
			isset($_POST['width']) ? $t_packaging['width'] = $_POST['width'] : $t_packaging['width'] = '';
			isset($_POST['height']) ? $t_packaging['height'] = $_POST['height'] : $t_packaging['height'] = '';
			isset($_POST['weight']) ? $t_packaging['weight'] = $_POST['weight'] : $t_packaging['weight'] = '';
			isset($_POST['items_pallet']) ? $t_packaging['items_pallet'] = $_POST['items_pallet'] : $t_packaging['items_pallet'] = '';
			isset($_POST['items_level']) ? $t_packaging['items_level'] = $_POST['items_level'] : $t_packaging['items_level'] = '';
			$this->logger->info(print_r($_POST, true));
			$this->logger->info('Transportverpackung: '.print_r($t_packaging, true));
			if ($t_packaging['transport_packaging'] == '') $error['transport_packaging'] = 'Verpackung darf nicht leer sein';
			if ($t_packaging['weight'] == '') $error['weight'] = 'Es muss ein Gewicht angegeben werden';
			$this->logger->info('Fehler: '.print_r($error, true));
			if (count($error)==0) {
				if ($old_No == '') {
					try {
						$t_packagingTable->Insert($t_packaging);
						$this->logger->info('Transportverpackung wurde hinzugefügt!');
					} catch (Exception $e) {
						$error['all'] = 'Transportverpackung wurde nicht hinzugefügt! '.$e->getMessage();
						$this->logger('Transportverpackung wurde nicht hinzugefügt! '.$e->getMessage());
					}
				} else {
					try {
						$t_packagingTable->update($t_packaging, array('no = ?' => $old_No));
						$this->logger->info('Transportverpackung wurde gespeichert!');
					} catch (Exception $e) {
						$error['all'] = 'Transportverpackung wurde nicht gespeichert! '.$e->getMessage();
						$this->logger->info('Transportverpackung wurde nicht gespeichert! '.$e->getMessage());
					}
				}
				if (count($error)==0) $this->_redirect('/tpackaging/index/');
			}
		} else {
			$No = $this->getParam('No');
			$error = array();
			if (empty($No) == false) {
				$t_packaging = $t_packagingTable->find($No)->current()->toArray();
			} else {
				$t_packaging = array('no' => '', 
								   'transport_packaging' => '',
								   'tp_type' => 1,
								   'length' => 0,
								   'width'=>0,
								   'heigth'=>0,
								   'weight'=>0,
								   'items_pallet'=>0,
								   'items_level'=>0);
			}
		}
		$params = $this->loadDependencies();
		$params['t_packaging'] = $t_packaging;
		$params['errors'] = $error;
		$params['title'] = 'Transportverpackung';
		//$this->view->form = $this->buildForm($product);
		$this->view->params = $params;
	}
	
}