<?php
class CountryController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	protected $global_settings;
	
	public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Länder');
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
		$this->global_settings = $this->db->query("SELECT * FROM global_settings")->fetchAll()[0];
    }

	public function indexAction()
	{
		$countryTable = new Application_Model_CountryModel();
		$countries = $countryTable->fetchAll()->toArray();
		$this->view->countries = $countries;
	}

	public function editAction()
	{
		$countryTable = new Application_Model_CountryModel();
		$errors = array();
		$country = array();
		if ($this->getRequest()->isPost()) {
			$this->logger->info('$_POST: '.print_r($_POST, true));
			isset($_POST['old_Id']) ? $old_Id = $_POST['old_Id'] : $old_Id = '';
			isset($_POST['Id']) ? $country['Id'] = strtoupper($_POST['Id']) : $country['Id'] = '';
			isset($_POST['country']) ? $country['country'] = $_POST['country'] : $country['country'] = '';
			isset($_POST['ISO_3']) ? $country['ISO_3'] = strtoupper($_POST['ISO_3']) : $country['ISO_3'] = '';
			isset($_POST['currency']) ? $country['currency'] = $_POST['currency'] : $country['currency'] = '';
			isset($_POST['currency_code']) ? $country['currency_code'] = $_POST['currency_code'] : $country['currency_code'] = '';
			isset($_POST['CC']) ? $country['CC'] = $_POST['CC'] : $country['CC'] = '';
			isset($_POST['BTM_code']) ? $country['BTM_code'] = $_POST['BTM_code'] : $country['BTM_code'] = '';
			if ($country['Id'] == '') $errors['Id'] = 'Id darf nicht leer sein';
			if ($country['ISO_3'] =='') $errors['ISO_3'] = 'Der ISO_3 Code sollte nicht leer sein!';
			if ($country['country'] == '') $errors['country'] = 'Das Land darf nicht leer sein!';
			if (in_array($country['BTM_code'], array('','0')) and ($this->global_settings['connect_to_BTM']==1)) $errors['BTM_code'] = 'BTM_code darf nicht leer sein!';
			$this->logger->info('Fehler: '.print_r($errors, true));
			$this->logger->info('$country: '.print_r($country, true));
			if (count($errors)==0) {
				if ($old_Id == '') {
					try {
						$countryTable->Insert($country);
						$this->logger->info('Land hinzugefügt: '.print_r($country, true));
					} catch (Exception $e) {
						$this->logger->err('Land wurde nicht hinzugefügt! '.$e->getMessage());
						$errors['all'] = 'Produktgruppe wurde nicht hinzugefügt!';
					}
				} else {
					try {
						$countryTable->update($country, array('Id = ?' => $old_Id));
						$this->logger->info('Land wurde geändert: '.print_r($country, true));
					} catch (Exception $e) {
						$errors['all'] = 'Land wurde nicht gespeichert!';
						$this->logger->err('Land wurde nicht gespeichert! '.$e->getMessage());
					}
				}
				if (count($error)==0) $this->_redirect('/country/index/');
			}
		} else {
			$Id = $this->getParam('id');
			if (empty($Id) == false) {
				$country = $countryTable->find($Id)->current()->toArray();
			} else {
				$country = $countryTable->createRow()->toArray();
			}
		}
		$params['data'] = $country;
		$params['errors'] = $errors;
		$params['title'] = 'Land';
		//$this->view->form = $this->buildForm($product);
		$this->view->params = $params;
	}
	
}