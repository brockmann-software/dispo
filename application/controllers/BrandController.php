<?php

class brandController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	
    public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Marken');
		$this->view->title = 'Marken';
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
    }

    public function indexAction()
    {
        // action body
		$this->logger->Info('Brand Index');
		$statement = $this->db->query("select * from brand");
		$result = $statement->fetchAll();
		$this->view->result = $result;
    }
	
	public function editAction()
	{
		$brandTable = new Application_Model_BrandModel();
		$errors = array();
		if ($this->getRequest()->isPost()){
			isset($_POST['No']) ? $brand['no'] = $_POST['No'] : $brand['No'] = '';
			isset($_POST['brand']) ? $brand['brand'] = $_POST['brand'] : $brand['brand'] = '';
			$this->logger->info('Brand: '.print_r($brand, true));
			if ($brand['No'] == '') {
				try {
					$brandTable->insert($brand);
				} catch (Exception $e) {
					$errors['all'] = 'Marke wurde nicht gespeichert '+$e->getMessage();
				}
			} else {
				try {
					$brandTable->edit($brand, array('No = ?'=>$brand['No']));
				} catch (Exception $e) {
					$errors['all'] = 'Die Änderung wurde nicht gespeichert! '+$e->getMessage();
				}
			}
		} else {
			$id = $this->getParam('id');
			if (empty($id)==false) {
				$brand = $brandTable->find($id)->current()->toArray();
			} else {
				$brand = array('No'=>'', 'brand'=>'');
			}
		}
		$this->logger->info('Brand Edit '.print_r($brand, true));
		$params['data'] = $brand;
		$params['errors'] = $errors;
		$params['title'] = 'Marke';
		$this->view->params = $params;
		$this->view->subtemplate ='brand/edit.phtml';
	}
	
}