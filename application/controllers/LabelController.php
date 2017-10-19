<?php

class LabelController extends Zend_Controller_Action
{
	protected $db;
	
    public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Etiketten');
		$this->view->title = 'Etiketten';
		$this->db = Zend_Registry::get('db');
    }

    public function indexAction()
    {
        // action body
		Zend_Registry::get('logger')->info('IndexController->IndexAction called');
		$statement = $this->db->query("select * from label");
		$result = $statement->fetchAll();
		$this->view->result = $result;
    }
	
	public function editAction()
	{
		if ($this->getRequest()->isPost()){
			$error=array();
			isset($_POST['oldNo']) ? $oldNo = $_POST['oldNo'] : $oldNo = 0;
			isset($_POST['No']) ? $label['No'] = $_POST['No'] : $label['No'] = '';
			isset($_POST['label'] ? $label['label'] = $_POST['label'] : $label['label'] = '';
			if (isset($_POST['labelCty']) {
				is_array($_POST['labelCty'] ? $label['labelCty'] = $_POST['LabelCty'] : $label['labelCty'][0]=$_POST['labelCty'];
			} else {
				$label['labelCty']=array();
			}
			
		} else {
			$id = $this->getParam('id');
		}
	}
	
}