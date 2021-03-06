<?php
class ProductgroupController extends Zend_Controller_Action
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
		$productgroupTable = new Application_Model_ProductgroupModel();
		$product_groups = $productgroupTable->fetchAll()->toArray();
		$this->view->productgroups = $product_groups;
	}

	public function editAction()
	{
		if ($this->getRequest()->isPost()) {
			$error = array();
			isset($_POST['old_No']) ? $old_No = $_POST['old_No'] : $old_No = 0;
			isset($_POST['product_group']) ? $product_group['product_group'] = $_POST['product_group'] : $product_group['product_group'] = '';
			isset($_POST['unece_link']) ? $product_group['unece_link'] = $_POST['unece_link'] : $product_group['unece_link'] = '';
			isset($_POST['storage_temp_min']) ? $product_group['storage_temp_min'] = $_POST['storage_temp_min'] : $product_group['storage_temp_min'] = 0;
			isset($_POST['storage_temp_max']) ? $product_group['storage_temp_max'] = $_POST['storage_temp_max'] : $product_group['storage_temp_max'] = 0;
			isset($_POST['transport_temp_min']) ? $product_group['transport_temp_min'] = $_POST['transport_temp_min'] : $product_group['transport_temp_min'] = 0;
			isset($_POST['transport_temp_max']) ? $product_group['transport_temp_max'] = $_POST['transport_temp_max'] : $product_group['transport_temp_max'] = 0;
			isset($_POST['ethylene_emitent']) ? $product_group['ethylene_emitent'] = ($_POST['ethylene_emitent']) ? 1 : 0 : $product_group['ethylene_emitent'] = 0;
			isset($_POST['ethylene_sensitive']) ? $product_group['ethylene_sensitive'] = ($_POST['ethylene_sensitive']) ? 1 : 0 : $product_group['ethylene_sensitive'] = 0;
			$productgroupTable = new Application_Model_ProductgroupModel();
			if ($product_group['product_group'] == '') $error['product_group'] = 'Produktgruppe darf nicht leer sein';
			if ($product_group['unece_link'] == '') $error['unece_link'] = 'Es muss eine Produktspezifikation definiert sein';
			if (count($error)==0) {
				if ($old_No == 0) {
					try {
						$productgroupTable->Insert($product_group);
					} catch (Exception $e) {
						$error['all'] = 'Produktgruppe wurde nicht hinzugefügt! '.$e->getMessage();
					}
				} else {
					try {
						$productgroupTable->update($product_group, array('No = ?' => $old_No));
					} catch (Exception $e) {
						$error['all'] = 'Produktgruppe wurde nicht gespeichert! '.$e->getMessage();
					}
				}
				if (count($error)==0) $this->_redirect('/productgroup/index/');
			}
		} else {
			$No = $this->getParam('id');
			$error = array();
			if (empty($No) == false) {
				$productgroupTable = new Application_Model_ProductgroupModel();
				$product_group = $productgroupTable->find($No)->current()->toArray();
			} else {
				$product_group = array('no' => 0,
										'product_group' => '',
										'unece_link' => '',
										'transport_temp_min' => 0,
										'transport_temp_max' => 0,
										'storage_temp_min' => 0,
										'storage_temp_max' => 0,
										'ethylene_emitent' => 0,
										'ethylene_sensitive' => 0);
			}
		}
		$params['data'] = $product_group;
		$params['errors'] = $error;
		$params['title'] = 'Produktgruppe';
		//$this->view->form = $this->buildForm($product);
		$this->view->params = $params;
	}
	
	public function validateAction()
	{
		isset($_POST['no']) ? $data['no'] = $_POST['no'] : $data['no'] = 0;
		isset($_POST['product_group']) ? $data['product_group'] = $_POST['product_group'] : $data['product_group'] = '';
		isset($_POST['unece_link']) ? $data['unece_link'] = $_POST['unece_link'] : $data['unece_link'] = '';
		
		$productgroupTable = new Application_Model_ProductgroupModel();
		$error = array();
		if ($data['product_group'] == '') $error['product_group'] = 'Produktgruppe darf nicht leer sein!';
		if ($data['unece_link'] == '') $error['unece_link'] = 'Es muss eine Produktspezifikation definiert sein';
		if (count($error)==0) {
			if ($data['No'] == '') {
				try {
					$productgroupTable->Insert($data);
				} catch (Exception $e) {
					$error['all'] = 'Produkt wurde nicht gespeichert!'.$e->getMessage();
				}
			} else {
				try {
					$productgroupTable->update($data);
				} catch (Exception $e) {
					$error['all'] = 'Produkt wurde nicht gespeichert!'.$e->getMessage();
				}
			} $this->_redirect('/product/index/');
		} else {
			$params['data'] = $data;
			$params['error'] = $error;
			$params['title'] = 'Produktgruppe';
			$this->view->params = $params;
			$this->view->subtemplate = '/product/edit.phtml';
		}
	}
}