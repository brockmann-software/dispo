<?php
class VendorController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	protected $config;
	
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
	
	private function loadDependecies()
	{
		$dependencies['certificates'] = $this->db->query("SELECT * FROM certification")->fetchAll();
		$dependencies['countries'] = $this->db->query('select * from country')->fetchAll();
		$dependencies['scopes'] = $this->db->query('SELECT * FROM certificate_scope')->fetchAll();
		$dependencies['products'] = $this->db->query('SELECT * FROM product')->fetchAll();
		return $dependencies;
	}
	
	public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Lieferanten');
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
		$this->config = Zend_Registry::get('config');
    }

	public function indexAction()
	{
		$params = array();
		$order = '';
		$searching = false;
		($this->hasParam('page')) ? $page = $this->getParam('page') : $page = 1;
		if ($this->hasParam('No')) {
			$searching = true;
			$params['No']['value'] = $this->getParam('No');
			$params['No']['type'] = 'string';
		}
		if ($this->hasParam('name')) {
			$searching = true;
			$params['name']['value'] = $this->getParam('name');
			$params['name']['type'] = 'string';
		}
		if ($this->hasParam('street')) {
			$searching = true;
			$params['street']['value'] = $this->getParam('street');
			$params['street']['type'] = 'string';
		}
		if ($this->hasParam('PO_code')) {
			$searching = true;
			$params['PO_code']['value'] = $this->getParam('PO_Code');
			$params['PO_code']['type'] = 'string';
		}
		if ($this->hasParam('city')) {
			$searching = true;
			$params['city']['value'] = $this->getParam('city');
			$params['city']['type'] = 'string';
		}
		if ($this->hasParam('country_code')) {
			$searching = true;
			$params['country_code']['value'] = $this->getParam('country_code');
			$params['country_code']['type'] = 'string';
		}
		if ($this->hasParam('order')) {
			$order = ' ORDER BY '.$this->getParam('order').' '.$this->getParam('orientation');
			$this->logger->info('Order: '.print_r($order, true));
		}
		($this->hasParam('connector')) ? $connector = $this->getParam('connector') : $connector = 'AND';
		$pNo = 0;
		$sqlStr = '';
		foreach ($params as $key => $field) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' '.$connector.' ';
			$pNo++;
			if ($field['type'] == 'string') $sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$field['value'].'%")';
		}
		$this->logger->info('QSL: '.$sqlStr);
		$vendor_count = $this->db->query('SELECT COUNT(No) as VENCNT FROM v_vendor'.$sqlStr)->fetchAll()[0]['VENCNT'];
		$vendors = $this->db->query("SELECT * FROM v_vendor".$sqlStr.$order." LIMIT ".(($page-1)*25).", 25")->fetchAll();
		$countries = $this->db->query('SELECT * FROM country')->fetchAll();
		$countries[count($countries)]= array('Id' => '0', 'country' => '- alle -');
		$this->view->countries = $countries;
		$this->view->vendor_count = $vendor_count;
		$this->view->cur_page = $page;
		$this->view->searching = $searching;
		$this->view->searchparams = $params;
		$this->view->vendors = $vendors;
	}

	public function editAction()
	{
		if (($this->getRequest()->isPost()) and ($_POST['submit']=='Speichern')) {
			$error = array();
			isset($_POST['old_No']) ? $old_No = $_POST['old_No'] : $old_No = '';
			isset($_POST['No']) ? $vendor['No'] = $_POST['No'] : $vendor['No'] = '';
			isset($_POST['name']) ? $vendor['name'] = $_POST['name'] : $vendor['name'] = '';
			isset($_POST['name_2']) ? $vendor['name_2'] = $_POST['name_2'] : $vendor['name_2'] = '';
			isset($_POST['street']) ? $vendor['street'] = $_POST['street'] : $vendor['street'] = '';
			isset($_POST['street_2']) ? $vendor['street_2'] = $_POST['street_2'] : $vendor['street_2'] = '';
			isset($_POST['PO_code']) ? $vendor['PO_code'] = $_POST['PO_code'] : $vendor['PO_code'] = '';
			isset($_POST['city']) ? $vendor['city'] = $_POST['city'] : $vendor['city'] = '';
			isset($_POST['country_code']) ? $vendor['country_code'] = $_POST['country_code'] : $vendor['country_code'] = '';
		
			$vendorTable = new Application_Model_VendorModel();
			if ($vendor['No'] == '') $error['No'] = 'Es muss eine eindeutige Lieferantennummer vergeben werden!';
			if ($vendor['name'] == '') $error['name'] = 'Name darf nicht leer sein';
			if ($vendor['city']=='') $error['city'] = 'Es muss eine Stadt angegeben sein';
			if (count($error)==0) {
				if ($old_No == '') {
					try {
						$vendorTable->Insert($vendor);
					} catch (Exception $e) {
						$error['all'] = 'Lieferant wurde nicht hinzugefügt! '.$e->getMessage();
					}
				} else {
					try {
						$vendorTable->update($vendor, array('No = ?' => $old_No));
					} catch (Exception $e) {
						$error['all'] = 'Lieferant wurde nicht gespeichert! '.$e->getMessage();
					}
				}
				if (count($error)==0) $this->_redirect('/vendor/index/');
			}
		} elseif(($this->getRequest()->isPost()) and ($_POST['submit']=='Abbrechen')) {
			$this->_redirect('/vendor/index/');
		} else {
			$No = $this->getParam('id');
			$error = array();
			if ($No != '') {
				$vendorTable = new Application_Model_VendorModel();
				$vendor = $vendorTable->find($No)->current()->toArray();
				$vendor_certificates = $this->db->query("SELECT * FROM v_vendor_certificate WHERE vendor = ?", $vendor['No'])->fetchAll();
				$vendor_agreements = $this->db->query("SELECT * FROM vendor_agreement WHERE vendor = ? ORDER BY version_year DESC", $vendor['No'])->fetchAll();
			} else {
				$vendor = array('No'=>'',
								'name' => '', 
								 'name_2' => '', 
								 'street' => '',
								 'street_2' => '',
								 'PO_code' => '',
								 'city' => '',
								 'country_code' => 'DE');
				$vendor_certificates = array();
				$vendor_agreements = array();
			}			
		}
		$params = $this->loadDependecies();
		$params['vendor_certificates'] = $vendor_certificates;
		$params['vendor_agreements'] = $vendor_agreements;
		$params['data'] = $vendor;
		$params['errors'] = $error;
		$params['title'] = 'Lieferant';
		//$this->view->form = $this->buildForm($product);
		$this->view->params = $params;
	}
	
	public function editcertAction()
	{
		$today = new Zend_Date();
		$errors = array();
		if ($this->getRequest()->isPost()) {
			$this->logger->info('Post: '.print_r($_POST, true));
			isset($_POST['No']) ? $cert_vendor['No'] = $_POST['No'] : $cert_vendor['No'] = 0;
			isset($_POST['vendor']) ? $cert_vendor['vendor'] = $_POST['vendor'] : $errors['vendor'] = 'Kein Lieferant vorhanden';
			isset($_POST['certificate']) ? $cert_vendor['certificate'] = $_POST['certificate'] : $errors['certificate'] = 'Kein Zertifikat ausgewählt';
			isset($_POST['scope']) ? $cert_vendor['scope'] = $_POST['scope'] : $errors['scope'] = 'Kein Bereich erfasst!';
			isset($_POST['cert_option']) ? $cert_vendor['cert_option'] = $_POST['cert_option'] : $cert_vendor['cert_option'] = 0;
			isset($_POST['reg_id']) ? $cert_vendor['reg_id'] = $_POST['reg_id'] : $cert_vendor['reg_id'] = '';
			isset($_POST['cert_id']) ? $cert_vendor['cert_id'] = $_POST['cert_id'] : $errors['cert_id'] = 'Keine Zertifikatsnummer (z.B. COID oder GGN) erfasst';
			isset($_POST['due_date']) ? $cert_vendor['due_date'] = $_POST['due_date'] : $errors['due_date'] = 'Kein (gültiges) Datum erfsasst!';
			isset($_POST['remark']) ? $cert_vendor['remark'] = $_POST['remark'] : $cert_vendor['remark'] = '';
			isset($_POST['cert_products']) ? $new_cert_products = $_POST['cert_products'] : $new_cert_products = array();
			if (count($errors)==0) {
				if ($today->isLater($cert_vendor['due_date'], Zend_Date::DATE_MEDIUM)) $errors['due_date'] = 'Datum muß später als Heute sein!';
			}
			if (count($errors)==0) {
				$cert_vendor['due_date'] = $today->set($cert_vendor['due_date'], Zend_Date::DATES)->toString('YYYY-MM-dd HH:mm:ss');
				$cert_vendor_table = new Application_Model_CertvendorModel();
				$cert_product_table = new Application_Model_CertproductModel();
				try {
					$this->db->beginTransaction();
					if ($cert_vendor['No'] == 0) {
						unset($cert_vendor['']);
						$cert_vendor_table->insert($cert_vendor);
						$cert_vendor['No'] = $this->db->lastInsertId();
					} else {
						$cert_vendor_table->update($cert_vendor, array('No=?'=>$cert_vendor['No']));
					}
					$cert_products = $cert_product_table->fetchAll(array('certificate = ?'=>$cert_vendor['No']));
					$this->logger->info(print_r($new_cert_products, true));
					foreach ($cert_products as $cert_product) {
						$key = array_search($cert_product->product, $new_cert_products);
						$this->logger->info('Key: '.print_r($key, true));
						if ($key===false) {
							$this->logger->info("Produkt {$cert_product->product} ist nicht vorhanden, Wird aus Liste gelöscht!");
							$cert_product->delete();
						} else {
							$this->logger->info("Produkt {$cert_product->product} ist vorhanden, nichts passiert!");
							unset($new_cert_products[$key]);
						}
					}
					foreach ($new_cert_products as $new_cert_product) {
						$cert_product = array();
						$cert_product['certificate'] = $cert_vendor['No'];
						$cert_product['product'] = $new_cert_product;
						$cert_product_table->insert($cert_product);
						$this->logger->info("Produkt {$cert_product['product']} ist neu und wurde hinzugefügt!");
					}
					$this->db->commit();
				} catch (Exception $e) {
					$errors['cert_products'] = 'Produkte wurden nicht gespeichert!';
					$this->logger->err('Produkte wurden nicht gespeichert! '.$e->getMessage());
					$this->db->rollback();
				}
			}					
			if (count($errors)==0) $errors['no_error'] = 'Änderungen erfolgreich';
		}
		($this->hasParam('vendor')) ? $vendor = $this->getParam('vendor') : $errors['vendor'] = 'Keine Lieferantennummer vorhanden!';
		if ($this->hasParam('No')) {
			$cert_vendor = $this->db->query('SELECT * FROM v_vendor_certificate WHERE No = ?', $this->getParam('No'))->fetchAll()[0];
			$cert_products = $this->db->query('SELECT * FROM v_cert_product WHERE certificate = ?', $this->getParam('No'))->fetchAll();
		} else {
			$cert_vendor = array(
				'No' => 0,
				'certificate' => 0,
				'vendor' => $vendor,
				'reg_id' => '',
				'cert_id' => '',
				'scope' => 0,
				'region' => 0,
				'due_date' => date('d.m.Y'),
				'remark' => '');
			$cert_products = array();
		}
		$this->logger->info('Errors: '.print_r($errors, true));
		$params = $this->loadDependecies();
		$params['errors'] = $errors;
		$params['cert_vendor'] = $cert_vendor;
		$params['cert_products'] = $cert_products;
		$this->view->params = $params;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');			
	}
	
	public function editagreemntAction()
	{
		$date = new Zend_Date();
		$errors = array();
		$vendor_agreement_Table = new Application_Model_VendoragreementModel();
		if ($this->getRequest()->isPost()) {
			(isset($_POST['No'])) ? $vendor_agreement['No'] = $_POST['No'] : $vendor_agreement['No'] = 0;
			(isset($_POST['vendor'])) ? $vendor_agreement['vendor'] = $_POST['vendor'] : $errors['vendor'] = 'Keine Lieferantennr. übergeben!';
			(isset($_POST['version_year'])) ? $vendor_agreement['version_year'] = $_POST['version_year'] : $errors['version_year'] = 'Keine Version erfasst';
			(isset($_POST['received'])) ? ($vendor_agreement['received'] = ($_POST['received']) ? 1 : 0) : $vendor_agreement['received'] = 0;
			if (isset($_POST['date_received'])) $vendor_agreement['date_received'] = $_POST['date_received'];
			(isset($_POST['date_sent'])) ? $vendor_agreement['date_sent']=$_POST['date_sent'] : $errors['date_sent'] = 'Kein Datum für gesendet erfasst!';
			(isset($_POST['constraint_1'])) ? $vendor_agreement['constraint_1'] = $_POST['constraint_1'] : $vendor_agreement['constraint_1'] = '';
			(isset($_POST['constraint_2'])) ? $vendor_agreement['constraint_2'] = $_POST['constraint_2'] : $vendor_agreement['constraint_2'] = '';
			(isset($_POST['remark'])) ? $vendor_agreement['remark'] = $_POST['remark'] : $vendor_agreement['remark'] = '';
			try {
				$date_name = 'date_sent';
				$vendor_agreement['date_sent'] = $date->set($vendor_agreement['date_sent'])->toString('YYYY-MM-dd');
				$date_name = 'date_received';
				if (isset($vendor_agreement['date_received'])) $vendor_agreement['date_received'] = $date->set($vendor_agreement['date_received'])->toString('YYYY-MM-dd');
			} catch (Exception $e) {
				$errors[$date_name] = 'Datum nicht im richtigen Format!';
				$this->logger->err("Datum {$date_name} nicht im richtigen Format! ".$e->getMessage());
			}
			$this->logger->info(print_r($_FILES, true));
			if ($_FILES['path']['error']==0) {
				$destination = realpath($this->config->upload->qm->supplier_agreements);
				if ($destination<>'') {
					$fullname = $destination.'/'.$_FILES['path']['name'];
					if ($_FILES['path']['type'] == 'application/pdf') {
						if (!move_uploaded_file($_FILES['path']['tmp_name'], $fullname))
							$errors['path'] = 'Fehler bei Dateiupload';
						$vendor_agreement['path'] = $_FILES['path']['name'];
					} else {
						$errors['path'] = 'Datei muss ein PDF sein!';
					}
				} else {
					$errors['path'] = 'Pfad zu Erklärungen existiert nicht!';
				}
			}
			if (count($errors)==0) {
				try {
					if ($vendor_agreement['No']==0) {
						unset($vendor_agreement['No']);
						$vendor_agreement_Table->insert($vendor_agreement);
						$vendor_agreement['No'] = $this->db->lastInsertId();
						$this->logger->info('Vendor Agreement hinzugefügt: '.print_r($vendor_agreement, true));
					} else {
						$vendor_agreement_Table->update($vendor_agreement, array('No = ?'=>$vendor_agreement['No']));
						$this->logger->info('Vendor Agreement geändert: '.print_r($vendor_agreement, true));
					}
				} catch (Exception $e) {
					$errors['all'] = 'Lieferantenvereinbarung wurden nicht gespeichert!';
					$this->logger->err('Lieferantenvereinbarung wurden nicht gespeichert! '.$e->getMessage());
				}
			}
			if (count($errors)==0) $errors['no_error'] = 'Änderungen erfolgreich';
		} else {		
			($this->hasParam('vendor')) ? $vendor = $this->getParam('vendor') : $errors['vendor'] = 'Keine Lieferantennummer vorhanden!';
			if ($this->hasParam('No') && ($this->getParam('No')<>0)) {
				$this->logger->info('Agreement No: '.print_r($this->getParam('No')));
				$vendor_agreement = $this->db->query('SELECT * FROM vendor_agreement WHERE No = ?', $this->getParam('No'))->fetchAll()[0];
			} else {
				$vendor_agreement = array(
					'No' => 0,
					'vendor' => $this->getParam('vendor'),
					'version_year' => 'V_14',
					'received' => 0,
					'responsible' => '',
					'date_received' => '',
					'date_sent' => date('d.m.Y'),
					'constraint_1' => '',
					'constraint_2' => '',
					'remark' => '',
					'path' => '');
			}
		}
		$this->logger->info(print_r($vendor_agreement, true)); 	
		$params['errors'] = $errors;
		$params['vendor_agreement'] = $vendor_agreement;
		$params['path'] = '/qm/agreements';
		$this->view->params = $params;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');			
	}
	
	public function updatecertAction()
	{
		$vendor_cert_table = new Application_Model_vendorcertModel();
		$errors = array();
		$vendor_certificate = array();
		if ($this->getRequest()->isPost()) {
			$due_date = new Zend_Date();
			$this->logger->info('$_POST: '.print_r($_POST, true));
			isset($_POST['No']) ? $vendor_certificate['No'] = $_POST['No'] : $errors['No'] = 'Keine Id erfasst!';
			isset($_POST['vendor']) ? $vendor_certificate['vendor'] = $_POST['vendor'] : $errors['vendor'] = 'Kein Lieferant angegeben!';
			isset($_POST['certificate']) ? $vendor_certificate['certificate'] = $_POST['certificate'] : $errors['v_cert'] = 'keine Zertifikatsnummer angegeben!';
			isset($_POST['scope']) ? $vendor_certificate['scope'] = $_POST['scope'] : $errors['scope'] = 'Kein Wert oder Feld angegeben!';
			isset($_POST['cert_id']) ? $vendor_certificate['cert_id'] = $_POST['cert_id'] : $errors['cert_id'] = 'Kein Wert für Zertifikatsnummer erfasst!';
			isset($_POST['due_date']) ? $vendor_certificate['due_date'] = $_POST['due_date'] : $errors['due_date'] = 'Kein Ablaufdatum erfasst!';
			isset($_POST['remark']) ? $vendor_certificate['remark'] = $_POST['remark'] : $errors['remark'] = 'Keine Bemerkung erfasst!';
		} else {
			$errors['all'] = 'Action nicht per POST-Request aufgerufen!';
		}
		if (count($errors)==0) {
			try {
				$vendor_certificate['due_date'] = $due_date->set($vendor_certificate['due_date'], Zend_Date::DATES)->toString('YYYY-MM-dd');
				if ($vendor_certificate['No'] == 'new') {
					unset($vendor_certificate['No']);
					$vendor_cert_table->insert($vendor_certificate);
					$vendor_certificate['No'] = $this->db->lastInsertId();
				} else {
					$vendor_cert_table->update($vendor_certificate, array('No = ?'=>$vendor_certificate['No']));
				}
				$this->logger->info('vendor_certificate: '.print_r($vendor_certificate, true));
			} catch (Exception $e) {
				$errors['all'] = 'Fehler beim Speichern des Zertifikats';
				$this->logger->err('Fehler beim Speichern des Zertifikats! '.$e->getMessage());
			}
		}
		$this->view->errors = $errors;
		$this->view->result = $vendor_certificate;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');	
		$this->renderScript('/xml/resultxml.phtml');		
	}
	
	public function deletecertAction()
	{
		$vendor_cert_table = new Application_Model_vendorcertModel();
		$errors = array();
		$vendor_certificate = array();
		if ($this->getRequest()->isPost()) {
			isset($_POST['No']) ? $No = $_POST['No'] : $errors['No'] = 'Keine Id übergeben!';
			if (count($errors)==0) {
				try {
					$vendor_certificate = $vendor_cert_table->find($No)->current()->toArray();
					$vendor_cert_table->delete(array('No = ?'=>$No));
					$this->logger->info('Zertifikat gelöscht: '.print_r($vendor_certificate, true));
				} catch (Exception $e) {
					$errors['all'] = 'Das Zertifikat wurde nicht gelöscht!';
					$this->logger->err('Das Zertifikat wurde nicht gelöscht! '.$e->getMessage());
				}
			}
		} else {
			$errors['all'] = 'Kein Post-Request!';
		}
		$this->view->errors = $errors;
		$this->view->result = $vendor_certificate;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');	
		$this->renderScript('/xml/resultxml.phtml');		
	}
	
	public function getAction()
	{
		$params=array();
		$errors=array();
		if ($this->hasParam('No')) {
			$params['No'] = $this->getParam('No');
		}
		if ($this->hasParam('name')) {
			$params['name'] = $this->getParam('name');
		}
		$this->hasParam('connector') ? $connector = $this->getParam('connector') : $connector = 'AND';
		$sqlStr = 'select * from vendor';
		$pNo = 0;
		foreach ($params as $key => $val) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' '.$connector.' ';
			$pNo++;
			$sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val.'%")';
		}
		try {
			$vendors = $this->db->query($sqlStr)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->results = $vendors;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');	
	}
	
	public function vendordialogAction()
	{
		$errors=array();
		$params=array();
		if ($this->hasParam('No')) {
			$params['No'] = $this->getParam('No');
		}
		if ($this->hasParam('name')) {
			$params['name'] = $this->getParam('name');
		}
		$sqlStr = 'select * from vendor';
		$pNo = 0;
		foreach ($params as $key => $val) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' OR ';
			$pNo++;
			$sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val.'%")';
		}
		try {
			$vendors = $this->db->query($sqlStr)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->vendors = $vendors;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');	
	}
	
	public function importAction()
	{
		$vendorTable = new Application_Model_VendorModel();
		$config = Zend_Registry::get('config');
		$form = new Zend_Form('/vendor/import');
		$file = new Zend_Form_Element_file('file');
		$file->setLabel('Lieferanten als .csv');
		$file->setDestination('C:/www-dev/dispo/public/upload/');
		$file->addValidator('Extension', false, 'csv');
		$form->addElement($file);
		$form->setAttrib('enctype', 'multipart/form-data');
		$button = new Zend_Form_Element_Submit('upload');
		$form->addElement($button);
		$errors = array();
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				$this->logger->info('Values: '.print_r($values, true));
				$this->db->beginTransaction();
				try {
					$import = fopen('C:/www-dev/dispo/public/upload/'.$values['file'],'r');
					$data = fgetcsv($import,10000, ';');
					$vendor_count = 0;
					while (($data = fgetcsv($import,10000, ';'))!==false) {
						$this->logger->info(print_r($data, true));
						$vendor_count++;
						$vendor = array();
						$vendor['No'] = mb_convert_encoding(trim($data[2]), 'utf-8', 'auto');
						$vendor['name'] = mb_convert_encoding(trim($data[3]), 'utf-8', 'auto');
						$vendor['name_2'] = mb_convert_encoding(trim($data[4]), 'utf-8', 'auto');
						$vendor['street'] = mb_convert_encoding(trim($data[5]), 'utf-8', 'auto');
						$vendor['street_2'] = '';
						$city = explode(' ', trim($data[6]));
						if (count($city)>1) {
							$c = 0;
							$vendor['PO_code'] = mb_convert_encoding($city[$c], 'utf-8', 'auto');
							$c++;
							while ($c < count($city)) {
								$vendor['city'].= mb_convert_encoding($city[$c], 'utf-8', 'auto').' ';
								$c++;
							}
							$vendor['city'] = trim($vendor['city']);
						} else {
							$vendor['PO_code'] = '';
							$vendor['city'] = mb_convert_encoding($city[0], 'utf-8', 'auto');
						}
						$this->logger->info('City: '.print_r($city, true));
						$country = $this->db->query('SELECT * FROM country WHERE BTM_code = ?', $data[20])->fetchAll();
						$this->logger->info('Country: '.print_r($country, true));
						if (count($country)>0) $vendor['country_code'] = $country[0]['Id']; else $vendor['country_code'] = 'XX';
						$vendors = $vendorTable->find($vendor['No']);
						if ($vendors->count()>0)
							$vendorTable->update($vendor, array('No = ?' => $vendor['No']));
						else
							$vendorTable->insert($vendor);
					}
					$this->db->commit();
					$this->logger->info('Lieferantenimport war erfolgreich!');
					$this->_redirect('/vendor/index/');
				} catch (Exception $e) {
					$errors['import'] = 'Lieferanten konnten nicht importiert werden!';
					$this->logger->err('Fehler bei Lieferantenimport: '.$e->getMessage());
					$this->db->rollback();
				}
			}
		}
		$this->view->form = $form;
	}

	public function syncvendorAction()
	{
		$vendor_table = new Application_Model_VendorModel();
		$errors = array();
		$btm_vendors = array();
		$db_btm = Zend_Registry::get('db_btm');
		$this->db->beginTransaction();
		try {
			$btm_vendor_stmt = $db_btm->query('SELECT * FROM view_LIE');
			while ($btm_vendor = $btm_vendor_stmt->fetch()) {
				$vendors = $vendor_table->find(trim($btm_vendor['LIENR']));
				$vendor = ($vendors->count()>0) ? $vendors->current() : $vendor_table->createRow();
				$vendor->No = iconv('CP1252', 'UTF-8', trim($btm_vendor['LIENR']));
				$vendor->name = iconv('CP1252', 'UTF-8', trim($btm_vendor['LIENAME']));
				$vendor->name_2 = iconv('CP1252', 'UTF-8', trim($btm_vendor['LIEANSPA']));
				$vendor->street = iconv('CP1252', 'UTF-8', trim($btm_vendor['LIESTRAS']));
				$vendor->street_2 = '';
				$vendor->city = '';
				$city = explode(' ', iconv('CP1252', 'UTF-8', trim($btm_vendor['LIEPLZOR'])));
				if (count($city)>1) {
					$c = 0;
					$vendor->PO_code = $city[$c];
					$c++;
					while ($c < count($city)) {
						$vendor->city.= $city[$c];
						$c++;
					}
					$vendor->city = trim($vendor->city);
				} else {
					$vendor->PO_code = '';
					$vendor->city = $city[0];
				}
				$country = $this->db->query('SELECT * FROM country WHERE BTM_code = ?', $btm_vendor['LIELNDSL'])->fetchAll();
				if (count($country)>0) $vendor['country_code'] = $country[0]['Id']; else $vendor['country_code'] = 'XX';
				$vendor->save();
				$btm_vendors[] = $vendor->toArray();
			}
			$this->db->commit();
		} catch (Exception $e) {
			$errors['all'] = 'Synchronisation wurde abgebrochen';
			$this->db->rollback();
		}
		$this->view->errors = $errors;
		$this->view->results = $btm_vendors;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');	
		$this->renderScript('/xml/resultlistxml.phtml');
	}
	
	public function importcertAction()
	{
		$certVendorTable = new Application_Model_CertvendorModel();
		$vendoragreementTable = new Application_Model_VendoragreementModel();
		$config = Zend_Registry::get('config');
		$form = new Zend_Form('/vendor/importcert');
		$file = new Zend_Form_Element_file('file');
		$file->setLabel('Lieferanten als .csv');
		$file->setDestination('C:/www-dev/dispo/public/upload/');
		$file->addValidator('Extension', false, 'csv');
		$form->addElement($file);
		$form->setAttrib('enctype', 'multipart/form-data');
		$button = new Zend_Form_Element_Submit('upload');
		$form->addElement($button);
		$errors = array();
		$date = new Zend_Date();
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
//				$this->logger->info('Values: '.print_r($values, true));
				$this->db->beginTransaction();
				try {
					$import = fopen('C:/www-dev/dispo/public/upload/'.$values['file'],'r');
					$data = fgetcsv($import,10000, ';');
					$vendor_count = 0;
					while (($data = fgetcsv($import,10000, ';'))!==false) {
						$vendor = $this->db->query('SELECT * FROM vendor WHERE UPPER(No) LIKE UPPER("%'.$data[4].'%")')->fetchAll();
						if (count($vendor)==1) {
							$vendor_agreement['vendor'] = $vendor[0]['No'];
							if ($data[6]<>'') {
								$vendor_agreement['date'] = date('Y-m-d');
								$vendor_agreement['received'] = 1;
							} else $vendor_agreement['received'] = 0;
							$vendor_agreement['responsible'] = $data[7];
							$vendor_agreement['constraint_1'] = $data[8];
							$vendor_agreement['constraint_2'] = ($data[22]<>'') ? 'Nur Gastro, kein LEH' : '';
							$vendor_agreement['remark'] = $data[21];
							$vendoragreementTable->insert($vendor_agreement);
							if ($data[9]<>'') {
								try {
									$ifs_cert = explode(chr(10), $data[9]);
									$cert_vendor = array();
									$certificate = $this->db->query('SELECT * FROM certification WHERE certificate = "IFS"')->fetchAll();
									if (count($certificate)>0) {
										if (isset($ifs_cert[0])) {
											$scopeimp = explode(' ', $ifs_cert[0])[1];
											$scope = $this->db->query('SELECT * FROM certificate_scope WHERE UPPER(scope) LIKE UPPER("%'.substr($scopeimp, 0,4).'%")')->fetchAll();
											(count($scope)>0) ? $cert_vendor['scope'] = $scope[0]['No'] : $cert_vendor['scope'] = 0;
										}
										$ifs_id = (isset($ifs_cert[1])) ? explode(' ', $ifs_cert[1])[1] : '';
										$cert_vendor['vendor'] = $vendor[0]['No'];
										$cert_vendor['certificate'] = $certificate[0]['No'];
										$cert_vendor['cert_id'] = $ifs_id;
										try {
											$cert_vendor['due_date'] = $date->set($data[10], Zend_Date::DATE_MEDIUM)->toString('YYYY-MM-dd HH:mm:ss');
										} catch (Exception $e) {
											$this->logger->err('Kein Datum '.$e->getMessage());
											$cert_vendor['due_date'] = date('Y-m-d');
										}
										$this->logger->info('Cert_Vendor: '.print_r($cert_vendor, true));
										$certVendorTable->insert($cert_vendor);
									}
								} catch (Exception $e) {
									$this->logger->err('Zertifikat nicht gespeichert! '+$e->getMessage());
								}
							}
							if ($data[11]<>'') {
								try {
									$cert_vendor = array();
									$certificate = $this->db->query('SELECT * FROM certification WHERE certificate = "QS"')->fetchAll();
									if (count($certificate)>0) {
										$cert_vendor['vendor'] = $vendor[0]['No'];
										$cert_vendor['certificate'] = $certificate[0]['No'];
										$cert_vendor['scope'] = 4;
										try {
											$cert_vendor['due_date'] = $date->set($data[12], Zend_Date::DATE_MEDIUM)->toString('YYYY-MM-dd HH:mm:ss');
										} catch (Exception $e) {
											$this->logger->err('Kein Datum '.$e->getMessage());
											$cert_vendor['due_date'] = date('Y-m-d');
										}
										$this->logger->info('Cert_Vendor: '.print_r($cert_vendor, true));
										$certVendorTable->insert($cert_vendor);
									}
								} catch (Exception $e) {
									$this->logger->err('Zertifikat nicht gespeichert! '+$e->getMessage());
								}
							}
							if ($data[13]<>'') {
								$certificate = $this->db->query('SELECT * FROM certification WHERE certificate = "QS"')->fetchAll();
								if (count($certificate)>0) {
									try {
									$cert_vendor = array();
									$cert_vendor['vendor'] = $vendor[0]['No'];
									$cert_vendor['certificate'] = $certificate[0]['No'];
									$cert_vendor['scope'] = 1;
									$cert_vendor['cert_id'] = $data[15];
									try {
										$cert_vendor['due_date'] = $date->set($data[14], Zend_Date::DATE_MEDIUM)->toString('YYYY-MM-dd HH:mm:ss');
									} catch (Exception $e) {
										$this->logger->err('Kein Datum '.$e->getMessage());
										$cert_vendor['due_date'] = date('Y-m-d');
									}
									$this->logger->info('Cert_Vendor: '.print_r($cert_vendor, true));
									$certVendorTable->insert($cert_vendor);
									} catch (Exception $e) {
										$this->logger->err('Zertifikat nicht gespeichert! '+$e->getMessage());
									}
								}
							}
							if (($data[15]<>'') && is_numeric($data[15])) {
								$certificate = $this->db->query('SELECT * FROM certification WHERE certificate = "Global Gap"')->fetchAll();
								if (count($certificate)>0) {
									try {
										$cert_vendor = array();
										$cert_vendor['vendor'] = $vendor[0]['No'];
										$cert_vendor['certificate'] = $certificate[0]['No'];
										$cert_vendor['scope'] = 1;
										$cert_vendor['cert_id'] = $data[15];
										$cert_vendor['cert_option'] = $data[16];
										try {
											$cert_vendor['due_date'] = $date->set($data[17], Zend_Date::DATE_MEDIUM)->toString('YYYY-MM-dd HH:mm:ss');
										} catch (Exception $e) {
											$this->logger->err('Kein Datum '.$e->getMessage());
											$cert_vendor['due_date'] = date('Y-m-d');
										}
										$this->logger->info('Cert_Vendor: '.print_r($cert_vendor, true));
										$certVendorTable->insert($cert_vendor);
									} catch (Exception $e) {
										$this->logger->err('Zertifikat nicht gespeichert! '+$e->getMessage());
									}
								}
							}
							if ($data[18]<>'') {
								$certificate = $this->db->query('SELECT * FROM certification WHERE certificate = "BIO"')->fetchAll();
								if (count($certificate)>0) {
									try {
										$cert_vendor = array();
										$cert_vendor['vendor'] = $vendor[0]['No'];
										$cert_vendor['certificate'] = $certificate[0]['No'];
										$cert_vendor['scope'] = (count($scope)>0) ? $scope[0]['No'] : 8;
										$cert_vendor['cert_id'] = '';
										try {
											$cert_vendor['due_date'] = $date->set($data[19], Zend_Date::DATE_MEDIUM)->toString('YYYY-MM-dd HH:mm:ss');
										} catch (Exception $e) {
											$this->logger->err('Kein Datum '.$e->getMessage());
											$cert_vendor['due_date'] = date('Y-m-d');
										}
										$this->logger->info('Cert_Vendor: '.print_r($cert_vendor, true));
										$certVendorTable->insert($cert_vendor);
									} catch (Exception $e) {
										$this->logger->err('Zertifikat nicht gespeichert! '+$e->getMessage());
									}
								}
							}
							if ($data[20]<>'') {
								$social_cert = explode(' ', $data[20]);
								foreach ($social_cert as $key => $cert) {
									$certificate = $this->db->query('SELECT * FROM certification WHERE UPPER(certificate) = UPPER("'.$cert.'")')->fetchAll();
									try {
										if (count($certificate)>0) {
											$cert_vendor = array();
											$cert_vendor['vendor'] = $vendor[0]['No'];
											$cert_vendor['certificate'] = $certificate[0]['No'];
											$cert_vendor['scope'] = 1;
											$cert_vendor['cert_id'] = $data[15];
											try {
												$cert_vendor['due_date'] = (($cert=='GRASP') && ($data[17]<>'')) ? $date->set($data[17], Zend_Date::DATE_MEDIUM)->toString('YYYY-MM-dd HH:mm:ss') : date('Y-m-d');
											} catch (Exception $e) {
												$this->logger->err('Kein Datum '.$e->getMessage());
												$cert_vendor['due_date'] = date('Y-m-d');
											}
											$cert_vendor['remark'] = (isset($social_cert[$key+1]) && (array_search($social_cert[$key+1], array('GRASP', 'FIAS'))==false)) ? $social_cert[$key+1] : ''; 
											$this->logger->info('Cert_Vendor: '.print_r($cert_vendor, true));
										}
									} catch (Exception $e) {
										$this->logger->err('Zertifikat nicht gespeichert! '+$e->getMessage());
									}
								}
							}
						}
					}
					$this->db->commit();
				} catch (Exception $e) {
					$this->db->rollback();
					$this->logger->err($e->getMessage());
				}
			}
		}
		$this->view->form = $form;
	}
}