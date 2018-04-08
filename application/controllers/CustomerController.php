<?php

class CustomerController extends Zend_Controller_Action
{
	protected $db;
	
	private function buildForm($customer)
	{
	
		$hidden = new Zend_Form_Element_Hidden('old_No');
		$hidden->setValue($customer['No']);
		$name1 = new Zend_Form_Element_Text('name');
		$name1->setLabel('Name Zeile 1');
		$name1->setValue($customer['name']);
		//$name1->setAttributes(array('size'=>'40','maxlength'=>'255'));
		$name2 = new Zend_Form_Element_Text('name_2');
		$name2->setLabel('Name Zeile 2');
		$name2->setValue($customer['name_2']);
		//$name2->setAttributes(array('size'=>'40','maxlength'=>'255'));
		$street = new Zend_Form_Element_Text('street');
		$street->setLabel('Straße Zeile 1');
		$street->setValue($customer['street']);
		//$street->setAttributes(array('size'=>'40','maxlength'=>'255'));
		$street2 = new Zend_Form_Element_Text('street_2');
		$street2->setLabel('Straße Zeile 2');
		$street2->setValue($customer['street_2']);
		//$street2->setAttributes(array('size'=>'40','maxlength'=>'255'));
		$PO_Code = new Zend_Form_Element_Text('PO_code');
		$PO_Code->setLabel('PLZ');
		$PO_Code->setValue($customer['PO_code']);
		//$PO_Code->setAttributes(array('size'=>'10'));
		$city = new Zend_Form_Element_Text('city');
		$city->setLabel('Stadt');
		$city->setValue($customer['city']);
		//$city->setAttributes(array('size'=>'40','maxlength'=>'255'));
		$CC = new Zend_Form_Element_Text('country_code');
		$CC->setLabel('Land');
		$CC->setValue($customer['country_code']);
		//$CC->setAttributes(array('size'=>'5', 'maxlength'=>'2'));
		$form = new Zend_Form('Kunde');
		$form->setAction('/customer/edit/');
		$form->setMethod('post');
		$form->addElement($hidden);
		$form->addElement($name1);
		$form->addElement($name2);
		$form->addElement($street);
		$form->addElement($street2);
		$form->addElement($PO_Code);
		$form->addElement($city);
		$form->addElement($CC);
		return $form;

	}
	
	public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Kunden');
		$this->db = Zend_Registry::get('db');
    }

	public function indexAction()
	{
		$customerTable = new Application_Model_CustomerModel();
		$customers = $customerTable->fetchAll()->toArray();
		$this->view->customers = $customers;
	}

	public function editAction()
	{
		if ($this->getRequest()->isPost()) {
			$error = array();
			isset($_POST['old_No']) ? $old_No = $_POST['old_No'] : $old_No = '';
			isset($_POST['name']) ? $customer['name'] = $_POST['name'] : $customer['name'] = '';
			isset($_POST['name_2']) ? $customer['name_2'] = $_POST['name_2'] : $customer['name_2'] = '';
			isset($_POST['street']) ? $customer['street'] = $_POST['street'] : $customer['street'] = '';
			isset($_POST['street_2']) ? $customer['street_2'] = $_POST['street_2'] : $customer['street_2'] = '';
			isset($_POST['PO_code']) ? $customer['PO_code'] = $_POST['PO_code'] : $customer['PO_code'] = '';
			isset($_POST['city']) ? $customer['city'] = $_POST['city'] : $customer['city'] = '';
			isset($_POST['country_code']) ? $customer['country_code'] = $_POST['country_code'] : $customer['country_code'] = '';
		
			$customerTable = new Application_Model_CustomerModel();
			if ($customer['name'] == '') $error['name'] = 'Name darf nicht leer sein';
			if ($customer['city']=='') $error['city'] = 'Es muss eine Stadt angegeben sein';
			if (count($error)==0) {
				if ($old_No == '') {
					try {
						$customerTable->Insert($customer);
					} catch (Exception $e) {
						$error['all'] = 'Kunde wurde nicht hinzugefügt! '.$e->getMessage();
					}
				} else {
					try {
						$customerTable->update($customer, array('No = ?' => $old_No));
					} catch (Exception $e) {
						$error['all'] = 'Kunde wurde nicht gespeichert! '.$e->getMessage();
					}
				}
				if (count($error)==0) $this->_redirect('/customer/index/');
			}
		} else {
			$No = $this->getParam('id');
			$error = array();
			if (empty($No) == false) {
				$customerTable = new Application_Model_CustomerModel();
				$customer = $customerTable->find($No)->current()->toArray();
			} else {
				$product = array('name' => '', 
								 'name_2' => '', 
								 'street' => '',
								 'street_2' => '',
								 'PO_code' => '',
								 'city' => '',
								 'country_code' => '');
			}
		}
		$params['data'] = $customer;
		$params['pg'] = $this->buildForm($customer);
		$params['errors'] = $error;
		$params['title'] = 'Kunde';
		//$this->view->form = $this->buildForm($product);
		$this->view->params = $params;
	}
	
	public function validateAction()
	{
		isset($_POST['No']) ? $data['No'] = $_POST['No'] : $data['No'] = '';
		isset($_POST['product']) ? $data['product'] = $_POST['product'] : $data['product'] = '';
		isset($_POST['product_group']) ? $data['product_group'] = $_POST['product_group'] : $data['product_group'] = 0;
		
		$productTable = new Application_Model_ProductModel();
		if ($data['product'] == '') $error['product'] = 'Produkt darf nicht leer sein';
		if ($data['product_group'] == 0) $error['product_group'] = 'Es muss eine Produktgruppe ausgewählt sein';
		if (count($error)==0) {
			if ($data['No'] == '') {
				try {
					$productTable->Insert($data);
				} catch (Exception $e) {
					$error['all'] = 'Produkt wurde nicht gespeichert! '.$e->getMessage();
				}
			}
			$this->_redirect('/product/index/');
		} else {
			$params['data'] = $data;
			$params['error'] = $error;
			$params['title'] = 'Produkt';
			$this->view->params = $params;
			$this->view->subtemplate = '/product/edit.phtml';
		}
	}
	
	public function customerdialogAction()
	{
		$errors = array();
		$select = $this->db->select()->from('customer');
		if ($this->hasParam('No')) {
			$select->where('UPPER(No) LIKE UPPER(?) OR UPPER(name) LIKE UPPER(?)', "%{$this->getParam('No')}%");
		}
		$customer = $this->db->query($select)->fetchAll();
		$this->view->customers = $customer;
		$this->view->errors = $errors;
	}
	
	public function getAction()
	{
		$errors = array();
		$this->hasParam('connector') ? $connector = $this->getParam('connector') : $connector = '';
		$select = $this->db->select()->from('customer');
		if ($this->hasParam('No')) {
			$select->where('UPPER(No) LIKE UPPER(?)', "%{$this->getParam('No')}%");
		}
		if ($this->hasParam('name')) {
			if ($connector =='or') $select->orWhere('UPPER(name) LIKE UPPER(?)', "%{$this->getParam('name')}%");
			else $select->where('UPPER(name) LIKE UPPER(?)', "%{$this->getParam('name')}%");
		}
		if ($this->hasParam('city')) {
			if ($connector =='or') $select->orWhere('UPPER(city) LIKE UPPER(?)', "%{$this->getParam('city')}%");
			else $select->where('UPPER(city) LIKE UPPER(?)', "%{$this->getParam('city')}%");
		}
		if ($this->hasParam('street')) {
			if ($connector =='or') $select->orWhere('UPPER(street) LIKE UPPER(?)', "%{$this->getParam('street')}%");
			else $select->where('UPPER(street) LIKE UPPER(?)', "%{$this->getParam('street')}%");
		}
		try {
			$customer = $this->db->query($select)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->results = $customer;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');	
	}
}