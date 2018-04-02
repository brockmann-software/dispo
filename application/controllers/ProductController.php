<?php
class ProductController extends Zend_Controller_Action
{
	protected $db;
	
	private function buildForm($product)
	{
		$PGTable = new Application_Model_ProductgroupModel();
		$Pgroups = $PGTable->fetchAll();
		foreach ($Pgroups as $group) {
			$pg[$group->no] = $group->product_group;
		}
		return $pg;
/*		
		$hidden = new Zend_Form_Element_Hidden('No');
		$hidden->setValue($product['No']);
		$input = new Zend_Form_Element_Text('product');
		$input->setLabel('Produkt');
		$input->setValue($product['product']);
		$select = new Zend_Form_Element_Select('product_group');
		$select->setLabel('Produktgruppe');
		$select->setValueOptions($pg);
		$select->setValue($product['product_group']);
		$form = new Zend_Form('Produkt');
		$form->setAction('/product/edit/');
		$form->setMethod('post');
		$form->addElement($hidden);
		$form->addElement($input);
		$form->addElement($select);
		return $form;
*/
	}
	
	public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Produkte');
		$this->db = Zend_Registry::get('db');
    }

	public function indexAction()
	{
		$productTable = new Application_Model_VProductModel();
		$products = $productTable->fetchAll()->toArray();
		$this->view->products = $products;
	}

	public function editAction()
	{
		if ($this->getRequest()->isPost()) {
			$error = array();
			isset($_POST['old_No']) ? $old_No = $_POST['old_No'] : $old_No = '';
			isset($_POST['No']) ? $product['No'] = $_POST['No'] : $product['No'] = '';
			isset($_POST['product']) ? $product['product'] = $_POST['product'] : $product['product'] = '';
			isset($_POST['product_group']) ? $product['product_group'] = $_POST['product_group'] : $product['product_group'] = 0;
		
			$productTable = new Application_Model_ProductModel();
			if ($product['product'] == '') $error['product'] = 'Produkt darf nicht leer sein';
			if ($product['product_group'] == 0) $error['product_group'] = 'Es muss eine Produktgruppe ausgewählt sein';
			if (count($error)==0) {
				if ($old_No == '') {
					try {
						$productTable->Insert($product);
					} catch (Exception $e) {
						$error['all'] = 'Produkt wurde nicht hinzugefügt! '.$e->getMessage();
					}
				} else {
					try {
						$productTable->update($product, array('No = ?' => $old_No));
					} catch (Exception $e) {
						$error['all'] = 'Produkt wurde nicht gespeichert! '.$e->getMessage();
					}
				}
				if (count($error)==0) $this->_redirect('/product/index/');
			}
		} else {
			$No = $this->getParam('id');
			$error = array();
			if (empty($No) == false) {
				$productTable = new Application_Model_ProductModel();
				$product = $productTable->find($No)->current()->toArray();
			} else {
				$product = array('No' => '', 'product' => '', 'product_group' => 0);
			}
		}
		$params['data'] = $product;
		$params['pg'] = $this->buildForm($product);
		$params['errors'] = $error;
		$params['title'] = 'Produkt';
		//$this->view->form = $this->buildForm($product);
		$this->view->params = $params;
	}
	
	public function getAction()
	{
		$params=array();
		$errors=array();
		if ($this->hasParam('No')) {
			$params['No']['value'] = $this->getParam('No');
			$params['No']['type'] = 'number';
		}
		if ($this->hasParam('product')) {
			$params['product']['value'] = $this->getParam('product');
			$params['product']['type'] = 'string';
		}
		$this->hasParam('connector') ? $connector = $this->getParam('connector') : $connector = 'AND';
		$sqlStr = 'select * from v_product';
		$pNo = 0;
		foreach ($params as $key => $val) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' AND ';
			$pNo++;
			$val['type']=='string' ? $sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val['value'].'%")' : $sqlStr.= $key.' = '.$val['value'];
		}
		try {
			$products = $this->db->query($sqlStr)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->results = $products;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
	}
	
	public function getproductAction()
	{
		$product = array();
		$product_labels = array();
		$product_calibers = array();
		$checkpoints = array();
		$errors=array();
		if ($this->hasParam('No')) {
			$products = $this->db->query('SELECT * FROM v_product WHERE No = ?', $this->getParam('No'))->fetchAll();
			if (count($products)>0) {
				$product = $products[0];
				// load labels
				$select = $this->db->select()->from('v_label_matrix');
				$select->where('product = ? OR product IS NULL', $product['No']);
				if ($this->hasParam('country')) $select->where('country = ? OR country IS NULL', $this->getParam('country'));
				$product_labels = $this->db->query($select)->fetchAll();
				// load calibers
				$select = $this->db->select()->from('caliber');
				$select->where('product = ?', $product['No']);
				$product_calibers = $this->db->query($select)->fetchAll();
				// load quality_checkpoints
				$select = $this->db->select()->from('v_qc_product');
				$select->where('product_no = ?', $product['No']);
				$select->order(array('qchk_class_no', 'qck_no'));
				$product_quality_checkpoints = $this->db->query($select)->fetchAll();
				if (count($product_quality_checkpoints)==0) {
					$select = $this->db->select()->from('v_quality_checkpoint');
					$select->where('type = 0');
					$select->order(array('qchk_class_no', 'qck_no'));
					$product_quality_checkpoints = $this->db->query($select)->fetchAll();
				}
			} else $errors['all'] = "Kein Produkt mit der Nummer {$this->getParam('No')} gefunden!";
		} else $errors['No'] = 'Keine Produktnummer übergeben!';
		$this->view->product = $product;
		$this->view->labels = $product_labels;
		$this->view->calibers = $product_calibers;
		$this->view->quality_checkpoints = $product_quality_checkpoints;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
	}
}