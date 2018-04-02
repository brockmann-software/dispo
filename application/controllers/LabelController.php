	<?php

class LabelController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	
    public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Etiketten');
		$this->view->title = 'Etiketten';
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
    }
	
	public function loadDependencies()
	{
		$dependencies['countries'] = $this->db->query('SELECT * FROM country')->fetchAll();
		$dependencies['products'] = $this->db->query('select * from product')->fetchAll();
		return $dependencies;
	}

    public function indexAction()
    {
        // action body
		$statement = $this->db->query("select * from label");
		$result = $statement->fetchAll();
		$this->view->result = $result;
    }
	
	public function editAction()
	{
		$errors = array();
		$labelCountries = array();
		$labelProducts = array();
		if ($this->getRequest()->isPost()){
			$this->logger->info('Post: '.print_r($_POST, true));
			$errors=array();
			isset($_POST['oldNo']) ? $oldNo = $_POST['oldNo'] : $oldNo = 0;
			isset($_POST['No']) ? $label['No'] = $_POST['No'] : $label['No'] = $oldNo;
			isset($_POST['label']) ? $label['label'] = $_POST['label'] : $label['label'] = '';
			isset($_POST['label_short']) ? $label['label_short'] = $_POST['label_short'] : $label['label_short'] = '';
			
			if (isset($_POST['label_country'])) {
				is_array($_POST['label_country']) ? $label_country = $_POST['label_country'] : $label_country[0] = $_POST['label_country'];
			} else {
				$label_country = array();
			}
			if (isset($_POST['label_product'])) {
				is_array($_POST['label_product']) ? $label_product = $_POST['label_product'] : $label_product[0]=$_POST['label_product'];
			} else {
				$label_product = array();
			}
			if ($label['label']=='') $errors['label'] = 'Die Bezeichnung darf nicht leer sein!';
			if ($label['label_short']=='') $errors['label_short'] = 'Die Kurzbezeichnung darf nicht leer sein!';
			if (count($errors)==0) {
				try {
					$labelModel = new Application_Model_Label($oldNo);
					$labelModel->save($label, $label_country, $label_product);
					$this->_redirect('/label/index/');
				} catch (Exception $e) {
					$this->logger->err('Label wurde nicht gespeichert! '.$e->getMessage());
					$errors['all'] = 'Das Etikett wurde nicht gespeichert!';
				}
			}
		} else {
			if ($this->hasParam('No')) $labelRec = new Application_Model_Label($this->getParam('No'));
				else $labelRec = new Application_Model_Label();
			$label = $labelRec->getData()->toArray();
			$labelCountries = $labelRec->getCountries();
			$labelProducts = $labelRec->getProducts();
		}
		$this->view->params = $this->loadDependencies();
		$this->view->errors = $errors;
		$this->view->data = $label;
		$this->view->labelCountries = $labelCountries;
		$this->view->labelProducts = $labelProducts;
	}
	
	public function getAction()
	{
		$errors = array();
		$select = $this->db->select()->from('v_label_matrix');
		if ($this->hasParam('country')) $select->where('country = ? OR country IS NULL', $this->getParam('country'));
		if ($this->hasParam('product')) $select->where('product = ? or product IS NULL', $this->getParam('product'));
		try {
			$labels = $this->db->query($select)->fetchAll();
		} catch (Exception $e) {
			$errors['all'] = $e->getMessage();
		}
		$this->view->results = $labels;
		$this->view->errors = $errors;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');
	}
}