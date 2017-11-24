<?php

class btmtranslateController extends Zend_Controller_Action
{
	protected $db;
	protected $db_btm;
	protected $logger;
	
    public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Umsetzung BTM');
		$this->view->title = 'Umsetzungstabelle';
		$this->db = Zend_Registry::get('db');
		$this->db_btm = Zend_Registry::get('db_btm');
		$this->logger = Zend_Registry::get('logger');
    }
	
	private function loaddependencies()
	{
		$dependencies['products'] = $this->db->query('SELECT * FROM product')->fetchAll();
		return $dependencies;
	}

    public function indexAction()
    {
		$errors = array();
		$this->logger->Info('BTM Translation Index');
		$statement = $this->db->query("select * from btm_translate_article");
		$result = $statement->fetchAll();
		$params = $this->loaddependencies();
		$params['errors'] = $errors;
		$this->view->params = $params;
		$this->view->data = $result;
    }
	
	public function insertAction()
	{
		$errors = array();
		$btm_translate_article = array();
		$btmTransTable = new Application_Model_BtmtransModel();
		if ($this->getRequest()->isPost()) {
			isset($_POST['id']) ? $id = $_POST['id'] : $id = '';
			isset($_POST['btm_article']) ? $btm_translate_article['btm_article'] = $_POST['btm_article'] : $btm_translate_article['btm_article'] = '';
			isset($_POST['btm_product_group']) ? $btm_translate_article['btm_product_group'] = $_POST['btm_product_group'] : $btm_translate_article['btm_product_group'] = '';
			isset($_POST['product']) ? $btm_translate_article['product'] = $_POST['product'] : $btm_translate_article['product'] = '';
			if ($id=='') $errors['id'] = 'Es wurde keine Id übergeben!';
			if ($btm_translate_article['btm_article'] =='') $errors['btm_article'] = 'BTM Artikel darf nicht leer sein';
			if (count($errors)==0) try {
				if ($id=='new') {
					$this->logger->info('Insert Translation: '.print_r($btm_translate_article, true));
					$btmTransTable->insert($btm_translate_article);
				} else {
					$this->logger->info('Update Translation: '.print_r($btm_translate_article, true));
					$btmTransTable->update($btm_translate_article, array('btm_article=?'=>$id));
				}
			} catch (Exception $e) {
				$this->logger->err('Translation wurde nicht gespeichert! '.$e->getMessage());
				$errors['all'] = 'Artikel wurde nicht gespeichert! Wahrscheinlich bereits vorhanden.';
			}
		} else $errors['id'] = "Die Anfrage ist kein POST-Request!";
		$this->view->result = $btm_translate_article;
		$this->view->errors = $errors;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');
	}
	
	public function deleteAction()
	{
		$errors = array();
		$btm_translate_article = array();
		$btmTransTable = new Application_Model_BtmtransModel();
		if ($this->getRequest()->isPost()) {
			isset($_POST['delete']) ? $delete = $_POST['delete'] : $delete = '';
			isset($_POST['id']) ? $id = $_POST['id'] : $id = '';
			if ($delete=='') $errors['delete'] = 'Das Merkmal DELETE wurde nicht übergeben!';
			if ($id=='') $errors['id'] = 'es wurde keine Id übergeben!';
			if (count($errors)==0) {
				$DSbtm_translate_article = $btmTransTable->find($id);
				try {
					$btm_translate_article = $DSbtm_translate_article->current()->toArray();
					$DSbtm_translate_article->current()->delete();
				} catch (Exception $e) {
					$errors['all'] = 'Eintrag konnte nicht gelöscht werden!';
					$this->logger->err('Eintrag konnte nicht gelöscht werden! '.$e->getMessage());
				}
			}
		} else $errors['all'] = 'Die Aktion muss ein POST-Request sein!';
		$this->view->result = $btm_translate_article;
		$this->view->errors = $errors;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');
	}		
	
	public function btmarticledialogAction()
	{
		$errors = array();
		$this->hasParam('No') ? $No = $this->getParam('No') : $No='';
		$article = $this->db_btm->query('SELECT ARTNR, ARTTX, ARTFSTX1, ARTWARGR FROM tbl_ART WHERE UPPER(KEYIART2) LIKE UPPER(?)', '%'.$No.'%')->fetchAll();
		$this->view->data = $article;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');
	}
	
	public function getbtmarticleAction()
	{
		$errors = array();
		$article = array();
		if ($this->hasParam('No')) {
			$article = $this->db_btm->query('SELECT ARTNR, ARTTX, ARTFSTX1, ARTWARGR FROM tbl_ART WHERE UPPER(KEYIART2) LIKE UPPER(?)', '%'.$this->getParam('No').'%')->fetchAll();
		} else $errors['all'] = 'Kein Parameter für Artikel übergeben!';
		$this->view->errors = $errors;
		$this->view->results = $article;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
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
					$errors['all'] = 'Die Änderung wurde nicht gespeichert. Artikel wahrscheinlich schon vorhanden! ';
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