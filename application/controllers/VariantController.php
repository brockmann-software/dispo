<?php

class variantController extends Zend_Controller_Action
{
	protected $db;
	
    public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Varianten');
		$this->view->title = 'Varianten';
		$this->db = Zend_Registry::get('db');
		//$contextSwitch = $this->_helper->getHelper('contextSwitch');
		//$contextSwitch->addActionContext('get', 'xml')
        //              ->initContext();
   }
	
	private function loadDependencies()
	{
		$db = $this->db;
		$dependencies['products'] = $db->query("select * from product")->fetchAll();
		$dependencies['products'][count($dependencies['products'])] = array('No'=>'', 'product'=>'--alle--');
		$dependencies['countries'] = $db->query("select * from country")->fetchAll();
		$dependencies['countries'][count($dependencies['countries'])] = array('Id'=>'','country'=>'--alle--');
		$dependencies['packagings'] = $db->query("select * from packaging")->fetchAll();
		$dependencies['packagings'][count($dependencies['packagings'])] = array('No'=>-1, 'packaging'=>'--alle--');
		$dependencies['t_packagings'] = $db->query("select * from tp_format")->fetchAll();
		$dependencies['t_packagings'][count($dependencies['t_packagings'])] = array('no'=>'', 'transport_packaging'=>'--alle--');
		$dependencies['labels'] = $db->query("select * from label")->fetchAll();
		$dependencies['labels'][count($dependencies['labels'])] = array('No'=>-1, 'label'=>'--alle--');
		$dependencies['qualities'] = $db->query("select * from quality")->fetchAll();
		$dependencies['qualities'][count($dependencies['qualities'])] = array('No'=>-1, 'quality'=>'--alle--');
		$dependencies['brands'] = $db->query("select * from brand")->fetchAll();
		$dependencies['quality_classes'] = $db->query("select * from quality_class")->fetchAll();
		$dependencies['quality_classes'][count($dependencies['quality_classes'])] = array('No'=>-1, 'quality_class'=>'--alle--');
		$dependencies['attributes'] = $db->query('select * from attribute')->fetchAll();
		$dependencies['attributes'][] = array('No'=>0, 'attribute'=>'');
		return $dependencies;
	}
	
    public function indexAction()
    {
        // action body
		Zend_Registry::get('logger')->info('IndexController->IndexAction called');
		$statement = $this->db->query("select * from v_variant");
		$result = $statement->fetchAll();
		$this->view->result = $result;
    }
		
	public function editAction()
	{
		$variantTable = new Application_Model_variantModel();
		$errors = array();
		if ($this->getRequest()->isPost()){
			isset($_POST['old_No']) ? $old_No = $_POST['old_No'] : $old_No = '';
			isset($_POST['No']) ? $variant['No'] = $_POST['No'] : $variant['No'] = '';
			isset($_POST['product']) ? $variant['product'] = $_POST['product'] : $variant['product'] = '';
			isset($_POST['origin']) ? $variant['origin'] = $_POST['origin'] : $variant['origin'] = '';
			isset($_POST['items']) ? $variant['items'] = $_POST['items'] : $variant['items'] = 0;
			isset($_POST['weight_item']) ? $variant['weight_item'] = $_POST['weight_item'] : $variant['weight_item'] = 0;
			isset($_POST['packaging']) ? $variant['packaging'] = $_POST['packaging'] : $variant['packaging'] = '';
			isset($_POST['label']) ? $variant['label'] = $_POST['label'] : $variant['label'] = 0;
			isset($_POST['t_packaging']) ? $variant['t_packaging'] = $_POST['t_packaging'] : $variant['t_packaging'] = '';
			isset($_POST['brand']) ? $variant['brand'] = $_POST['brand'] : $variant['brand'] = 0;
			isset($_POST['quality']) ? $variant['quality'] = $_POST['quality'] : $variant['quality'] = '';
			isset($_POST['quality_class']) ? $variant['quality_class'] = $_POST['quality_class'] : $variant['quality_class'] = 1;
			if ($variant['No'] == '') $errors['No'] = 'Die Nummer darf nicht leer sein!';
			try {
				$variant['items'] = intval($variant['items']);
				if ($variant['items'] <= 0) $errors['items'] = 'Der Wert sollte größer 0 sein. Bei loser Ware sollte "1" eingegeben werden.';
			} catch (Exception $e) {
				$errors['items'] = 'Der Wert muss eine ganze Zahl enthalten! '+$e->getMessage();
			}
			try {
				$variant['weight_item'] = intval($variant['weight_item']);
				if ($variant['weight_item'] <= 0) $errors['weight_item'] = 'Der Wert sollte größer 0 sein. Gewicht in Gramm';
			} catch (Exception $e) {
				$errors['weight_items'] = 'der Wert muss eine ganze Zahl enthalten. Die Angabe ist in Gramm! '.$e->getMessage();
			}
			if (count($errors)==0) {
				if ($old_No == '') {
					try {
						$variantTable->insert($variant);
					} catch (Exception $e) {
						$errors['all'] = 'Variante wurde nicht gespeichert! '.$e->getMessage();
					}
				} else {
					try {
						$variantTable->update($variant, array('No = ?'=>$old_No));
					} catch (Exception $e) {
						$errors['all'] = 'Die Änderung wurde nicht gespeichert! '.$e->getMessage();
					}
				}
			}
			if (count($errors)==0) $this->_redirect('/variant/index/');
		} else {
			$id = $this->getParam('id');
			if (empty($id)==false) {
				$variant = $variantTable->find($id)->current()->toArray();
			} else {
				$variant = array('No'=>'',
								'product'=>'',
								'origin'=>'',
								'variant'=>'',
								'items'=>0,
								'weight_item'=>0,
								'packaging'=>'',
								'label'=>0,
								't_packaging'=>'',
								'brand'=>0,
								'quality'=>0,
								'quality_class'=>1,
								'attribute'=>0);
			}
		}
		$params = $this->loadDependencies();
		$params['variant'] = $variant;
		$params['errors'] = $errors;
		$params['title'] = 'Variante';
		$params['action'] = '/variant/edit/';
		$this->view->params = $params;
		$this->view->subtemplate ='variant/edit.phtml';
	}
	
	public function getAction()
	{
		$errors = array();
		$params = array();
		//$params = $this->getAllParams();
		$select = $this->db->select()->from('v_variant');
		if ($this->hasParam('variant')) {
			$params['variant']['value'] = $this->getParam('variant');
			$params['variant']['type'] = 'string';
			$select->where('UPPER(variant) LIKE UPPER(?) OR UPPER(No) LIKE UPPER(?)', "%{$this->getParam('variant')}%");
		}
		if ($this->hasParam('product_no')) {
			$params['product_no']['value'] = $this->getParam('product_no');
			$params['product_no']['type'] = 'string';
			$select->where('UPPER(product_no) LIKE UPPER(?)', "%{$this->getParam('product_no')}%");
		}
		if ($this->hasParam('origin')) {
			$params['origin']['value'] = $this->getParam('origin');
			$params['origin']['type'] = 'string';
			$select->where('UPPER(origin) LIKE UPPER(?)', "%{$this->getParam('origin')}%");
		}
		if ($this->hasParam('items')) {
			$params['items']['value'] = $this->getParam('items');
			$params['items']['type'] = 'integer';
			$select->where('items = ?', $this->getParam('items'));

		}
		if ($this->hasParam('weight_item')) {
			$params['weight_item']['value'] = $this->getParam('weight_item');
			$params['weight_item']['type'] = 'integer';
			$select->where('weight_item = ?', $this->getParam('weight_item'));
		}
		if ($this->hasParam('t_packaging')) {
			$params['t_packaging']['value'] = $this->getParam('t_packaging');
			$params['t_packaging']['type'] = 'string';
			$select->where('UPPER(t_packaging) LIKE UPPER(?)', "%{$this->getParam('t_packaging')}%");
		}
		$sqlStr = 'select * from v_variant';
		$pNo = 0;
		foreach ($params as $key => $val) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' AND ';
			$pNo++;
			$val['type']=='string' ? $sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val['value'].'%")' : $sqlStr.= $key.' = '.$val['value'];
		}
		try {
			$variants = $this->db->query($select)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		// if (count($variants)==0) $errors['sql'] = $sqlStr;
		$this->view->errors = $errors;
		$this->view->results = $variants;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		//$this->view->subtemplate = '/xml/resultlistxml.php';
	}
	
	public function variantdialogAction()
	{
		$errors = array();
		$params = array();
		$select = $this->db->select()->from('v_variant');
		if ($this->hasParam('No')) {
			$select->where('UPPER(No) LIKE UPPER(?) OR UPPER(product) LIKE UPPER(?)', '%'.$this->getParam('No').'%');
		}
		try {
			$variants = $this->db->query($select)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$params = $this->loadDependencies();
		$params['variants'] = $variants;
		$params['errors'] = $errors;
		$this->view->params = $params;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');
	}
		
	public function testAction()
	{
		$dependencies = $this->loadDependencies();
		$this->view->dependencies = $dependencies;
	}
	
}