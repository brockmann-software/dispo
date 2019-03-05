<?php

class ContactController extends Zend_Controller_Action
{
	protected $db;
	protected $config;
	protected $logger;
	protected $locale;
	
	public function init()
	{
		$this->db = Zend_Registry::get('db');
		$this->config = Zend_Registry::get('config');
		$this->logger = Zend_Registry::get('logger');
		$this->locale = Zend_Registry::get('Zend_Locale');
		$this->translate = Zend_Registry::get('Zend_Translate');
	}
	
	public function indexAction()
	{
		$this->translate->addTranslation(array(
			'adapter'=>'My_Translate_Adapter_Mysql', 
			'content'=>'view_label', 
			'locale'=>'de',
			'columns'=>array('language', 'column_name', 'label'),
			'view'=>'contact_index.phtml',
			'clear'=>true));
		if (!$this->translate->isAvailable($this->locale->getLanguage())) $this->translate->setLocale('de'); else $this->translate->setLocale('auto');
		$contacts = $this->db->query('SELECT * from v_contact')->fetchAll();
		
		$this->view->contacts = $contacts;
	}
	
	public function editAction()
	{
		$contact = array();
		$errors = array();
		if ($this->getRequest()->isPost() and ($_POST['submit']=='Speichern')) {
			$this->logger->info('POST: '.print_r($_POST, true));
			//Params auslesen
			isset($_POST['old_No']) ? $contact['No'] = $_POST['old_No'] : $contact['No'] = 0;
			isset($_POST['title']) ? $contact['title'] = $_POST['title'] : $contact['title'] = '';
			isset($_POST['first_name']) ? $contact['first_name'] = $_POST['first_name'] : $contact['first_name'] = '';
			isset($_POST['surname']) ? $contact['surname'] = $_POST['surname'] : $contact['surname'] = '';
			isset($_POST['company_type']) ? $contact['company_type'] = $_POST['company_type'] : $contact['company_type'] = 0;
			isset($_POST['company']) ? $contact['company'] = $_POST['company'] : $contact['company'] = '';
			isset($_POST['office_phone']) ? $contact['office_phone'] = $_POST['office_phone'] : $contact['office_phone'] = '';
			isset($_POST['office_mobile']) ? $contact['office_mobile'] = $_POST['office_mobile'] : $contact['office_mobile'] = '';
			isset($_POST['private_phone']) ? $contact['private_phone'] = $_POST['private_phone'] : $contact['private_phone'] = '';
			isset($_POST['private_mobile']) ? $contact['private_mobile'] = $_POST['private_mobile'] : $contact['private_mobile'] = '';
			isset($_POST['email']) ? $contact['email'] = $_POST['email'] : $contact['email'] = '';
			isset($_POST['hideCompany']) ? $paramCompany = $_POST['hideCompany'] : $paramCompany = '';
			//validieren
			if (trim($contact['surname']) == '') $errors['surname'] = 'Name darf nicht leer sein!';
			if ($contact['company_type'] == 0) $errors['type'] = 'Es wurde kein Firmentyp ausgewählt!';
			switch($contact['company_type']) {
				case 1: $companyTable = new Application_Model_CustomerModel();
						break;
				case 2: $companyTable = new Application_Model_VendorModel();
						break;
			}
			$companies = $companyTable->find($contact['company']);
			if (!$companies->count()>0) {
				$contact['company_name'] = '';
				$errors['company'] = "Unternehmen existiert nicht!";
			} else {
				$company = $companies->current();
			}
			if (count($errors)==0) {
				$contactTable = new Application_Model_ContactModel();
				if ($contact['No'] == 0) {
					try {
						unset($contact['No']);
						$contactTable->insert($contact);
						$errors['noError'] = 'NoError';
					} catch (Exception $e) {
						$errors['all'] = 'Der Kontakt wurde nicht hinzugefügt';
						$this->logger->info('Der Kontakt wurde nicht hinzugefügt '.$e->getMessage());

					}
				} else {
					try {
						$contactTable->update($contact, array('No = ?'=>$contact['No']));
						$errors['noError'] = 'NoError';
					} catch (Exception $e) {
						$errors['All'] = 'Der Kontakt wurde ncht geändert';
						$this->logger->info('Der Kontakt wurde nicht geändert '.$e->getMessage());
					}
				}
				$contact['company_name'] = $company->name;
			}

			// if (count($errors)==0) $this->_redirect('/contact/index');
		} else {
			$No = ($this->hasParam('No')) ? $this->getParam('No') : '';
			$paramCompany = ($this->hasParam('company')) ? $this->getParam('company') : '';
			$parmCType = ($this->hasParam('company_type')) ? $this->getParam('company_type') : 1;
			if (($No == '' || $No =='0')) {
				$contact['No'] = 0;
				$contact['title'] = '';
				$contact['first_name'] = '';
				$contact['surname'] = '';
				$contact['company_type'] = $parmCType;
				$contact['company'] = $paramCompany;
				$contact['company_name'] = '';
				$contact['office_phone'] = '';
				$contact['office_mobile'] = '';
				$contact['private_phone'] = '';
				$contact['private_mobile'] = '';
				$contact['email'] = 0;
			} else {
				// Kontakt laden
				$contactTable = new Application_Model_ContactModel();
				$contacts = $contactTable->find($No);
				if ($contacts->count()==0) $errors['all'] = 'Kontakt nicht gefunden!';
				else {
					$contact = $contacts->current()->toArray();
					// Company laden
					switch($contact['company_type']) {
						case 1: $companyTable = new Application_Model_CustomerModel();
								break;
						case 2: $companyTable = new Application_Model_VendorModel();
								break;
					}
					$companies = $companyTable->find($contact['company']);
					if (!$companies->count()>0) $errors['company'] = "Unternehmen existiert nicht!";
					else $company = $companies->current();
				}
				// if (count($errors)==0) $this->_redirect('contact/index/');
				// auf die Maskenfelder umsetzen.
				$contact['company_name'] = $company->name;
			}
		}
		$this->translate->addTranslation(array(
			'adapter'=>'My_Translate_Adapter_Mysql', 
			'content'=>'view_label', 
			'locale'=>'de',
			'columns'=>array('language', 'column_name', 'label'),
			'view'=>'contact_index.phtml',
			'clear'=>true));
		if (!$this->translate->isAvailable($this->locale->getLanguage())) $this->translate->setLocale('de'); else $this->translate->setLocale('auto');
		$this->view->errors = $errors;
		$this->view->contact = $contact;
		$this->view->hideCompany = ($paramCompany<>'');
		if ($paramCompany<>'') {
			$layout = $this->_helper->layout();
			$layout->setLayout('dialog_layout');	
		}
	}
	
	public function contactdialogAction()
	{
		$params=array();
		$errors=array();
		
		$select = $this->db->select()->from('v_contact');
		if ($this->hasParam('No')) {
			$select->where('No=?', $this->getParam('No'));
		}
		if ($this->hasParam('surname')) {
			$select->where('surname LIKE ?', '%'.$this->getParam('surname').'%');
		}
		if ($this->hasParam('company')) {
			$select->where('company=?', $this->getParam('company'));
		}
		try {
			$contacts = $this->db->query($select)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->contacts = $contacts;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');
	}
	
	public function srcontacteditAction()
	{
		$errors = array();
		$sr_contacts = array();
		if ($this->getRequest()->isPost()) {
			isset($_POST['No']) ? $sr_contact['No'] = $_POST['No'] : $sr_contact['No'] = '';
			isset($_POST['contact']) ? $sr_contact['contact'] = $_POST['contact'] : $sr_contact['contact'] = 0;
			isset($_POST['standard_report']) ? $sr_contact['standard_report'] = $_POST['standard_report'] : $sr_contact['standard_report'] = 0;
			isset($_POST['company']) ? $company = $_POST['company'] : $company='';
			isset($_POST['company_type']) ? $company_type = $_POST['company_type'] : $company_type = 0;
			if ($sr_contact['contact'] == 0) $errors['contact'] = 'Kein Kontakt ausgewählt!';
			if ($sr_contact['standard_report'] == 0) $errors['standard_report'] = 'Kein Bericht ausgewählt!';
			if ($sr_contact['No'] == '') $errors['No'] = 'Id darf nicht leer sein!';
			if ($company=='') $errors['company'] = 'Kein Unternehmen ausgewählt!';
			if ($company_type==0) $errors['company_type'] = 'Kein Typ ausgewählt!';
			if (count($errors)==0) {
				$sr_contactTable = new Application_Model_SrcontactModel();
				if ($sr_contact['No']=='new') {
					try {
						$srSelect = $this->db->select()->from('sr_contact')->where('standard_report=?', $sr_contact['standard_report'])->where('contact=?', $sr_contact['contact']);
						$sr_contactsExisting = $this->db->query($srSelect)->fetchAll();
						if (count($sr_contactsExisting)>0) throw new Exception('Kontakt schon dem Report zugeordnet');
						unset($sr_contact['No']);
						$sr_contactTable->insert($sr_contact);
					} catch (Exception $e) {
						$errors['all'] = 'Kontakt wurde nicht hinzugefügt!';
						$this->logger->err('Kontakt wurde nicht hinzugefügt!: '.$e->getMessage());
					}
				} else {
					try {
						$sr_contactTable->update($sr_contact, array('No = ?'=>$sr_contact['No']));
					} catch (Exception $e) {
						$errors['all'] = 'Kontakt wurde nicht geändert!';
						$this->logger->err('Kontakt wurde nicht geändert!: '.$e->getMessage());
					}
				}
				try {
					$select = $this->db->select()->from('v_sr_contact');
					$select->where('standard_report=?', $sr_contact['standard_report']);
					$select->where('company=?', $company);
					$select->where('company_type=?', $company_type);
					$sr_contacts = $this->db->query($select)->fetchAll();
				} catch (Exception $e) {
					$errors['all'] = 'Fehler beim laden der Kontakte!';
					$this->logger->err('Fehler beim laden der Kontakte, '.$e->getMessage());
				}
			}
		} else {
			$errors['all'] = 'Request ist kein POST!';
		}
		$this->view->errors = $errors;
		$this->view->results = $sr_contacts;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');		
	}
	
	public function srcontactdeleteAction()
	{
		$errors = array();
		$sr_contacts = array();
		if ($this->getRequest()->isPost()) {
			isset($_POST['No']) ? $sr_contact['No'] = $_POST['No'] : $sr_contact['No'] = '';
			isset($_POST['standard_report']) ? $sr_contact['standard_report'] = $_POST['standard_report'] : $sr_contact['standard_report'] = 0;
			isset($_POST['company']) ? $company = $_POST['company'] : $company='';
			isset($_POST['company_type']) ? $company_type = $_POST['company_type'] : $company_type = 0;
			if ($sr_contact['standard_report'] == 0) $errors['standard_report'] = 'Kein Bericht ausgewählt!';
			if ($sr_contact['No'] == '') $errors['No'] = 'Id darf nicht leer sein!';
			if ($company=='') $errors['company'] = 'Kein Unternehmen ausgewählt!';
			if ($company_type==0) $errors['company_type'] = 'Kein Typ ausgewählt!';
			if (count($errors)==0) {
				$sr_contactTable = new Application_Model_SrcontactModel();
				try {
					$sr_contactTable->delete(array('No = ?'=>$sr_contact['No']));
				} catch (Exception $e) {
					$errors['all'] = 'Kontakt wurde nicht gelöscht!';
					$this->logger->err('Kontakt wurde nicht geändert!: '.$e->getMessage());
				}
				try {
					$select = $this->db->select()->from('v_sr_contact');
					$select->where('standard_report=?', $sr_contact['standard_report']);
					$select->where('company=?', $company);
					$select->where('company_type=?', $company_type);
					$sr_contacts = $this->db->query($select)->fetchAll();
				} catch (Exception $e) {
					$errors['all'] = 'Fehler beim laden der Kontakte!';
					$this->logger->err('Fehler beim laden der Kontakte, '.$e->getMessage());
				}
			}
		} else {
			$errors['all'] = 'Request ist kein POST!';
		}
		$this->view->errors = $errors;
		$this->view->results = $sr_contacts;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');		
	}
	
	public function getAction()
	{
		$params=array();
		$errors=array();
		
		$select = $this->db->select()->from('v_contact');
		if ($this->hasParam('No')) {
			$select->where('No=?', $this->getParam('No'));
		}
		if ($this->hasParam('surname')) {
			$select->where('surname LIKE ?', '%'.$this->getParam('surname').'%');
		}
		if ($this->hasParam('company')) {
			$select->where('company=?', $this->getParam('company'));
		}
		try {
			$contacts = $this->db->query($select)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->results = $contacts;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');
	}
	
	public function getsrAction()
	{
		$params=array();
		$errors=array();
		
		$select = $this->db->select()->from('v_sr_contact');
		if ($this->hasParam('No')) {
			$select->where('No=?', $this->getParam('No'));
		}
		if ($this->hasParam('contact')) {
			$select->where('contact = ?', $this->getParam('contact'));
		}
		if ($this->hasParam('company')) {
			$select->where('company=?', $this->getParam('company'));
		}
		if ($this->hasParam('company_type')) {
			$select->where('company_type = ?', $this->getParam('company_type'));
		}
		try {
			$contacts = $this->db->query($select)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->results = $contacts;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');
	}
		
}

	
