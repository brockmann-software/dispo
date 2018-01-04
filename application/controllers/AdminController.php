<?php
class AdminController extends Zend_Controller_Action
{
	protected $db;
	protected $config;
	protected $logger;
	
	public function init()
	{
		$this->db = Zend_Registry::get('db');
		$this->config = Zend_Registry::get('config');
		$this->logger = Zend_Registry::get('logger');
	}
	
	public function indexAction()
	{
		$view = '';
		$tableDesc = array();
		if ($this->hasParam('view')) {
			$view = $this->getParam('view');
			$tableDesc = $this->db->describeTable($this->getParam('view'));
		}
		$this->view->view = $view;
		$this->view->tableDesc = $tableDesc;
	}
	
	public function showviewlabelsAction()
	{
		$labels = array();
		if ($this->hasParam('view')) {
			$params = $this->getAllParams();
			$viewPath = APPLICATION_PATH.'/views/scripts/';
			$controller = ($this->hasParam('section')) ? $this->getParam('section') : 'index';
			$view = $this->getParam('view');
			if ($content = file_get_contents($viewPath.$controller.'/'.$view)) {
				$found = preg_match_all("/\@[a-zA-Z0-9_-]*/", $content, $labels);
				$labels = array_unique($labels[0]);
			}
		}
		$this->view->view = $controller.'_'.$view;
		$this->view->labels = $labels;
	}
}