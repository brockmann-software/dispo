<?php
class languageController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	
	public function init()
	{
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
	}
	
	private function loadDependencies()
	{
		$dependencies['languages'] = $this->db->query('SELECT * FROM language WHERE prefered = 1')->fetchAll();
		return $dependencies;
	}
	
	public function languagedialogAction()
	{
		$errors = array();
		$lang_entries = array();
		$statement = $this->db->select();
		$statement->from('localization', '*');
		if ($this->hasParam('No'))
			$statement->where('No = ?', $this->getParam('No'));
		if ($this->hasParam('table'))
			$statement->where('table_name = ?', $this->getParam('table'));
		else $errors['table'] = 'Keine Tabelle übergeben';
		if ($this->hasParam('column'))
			$statement->where('column_name = ?', $this->getParam('column'));
		else $errors['column'] = 'Keine Spalte übergeben';
		if ($this->hasParam('key'))
			$statement->where('key_val = ?', $this->getParam('key'));
		else $errors['key'] = 'Identifier übergeben';
		if ($this->hasParam('language'))
			$statement->where('language = ?', $this->getParam('language'));
		if (count($errors)==0) {
			$checkAssoc = $this->db->select();
			$checkAssoc->from($this->getParam('table'), $this->getParam('column'));
			$checkAssoc->where('No = ?', $this->getParam('key'));
			$this->logger->info($checkAssoc->__toString());
			$assoc = array();
			try {
				$assoc = $this->db->query($checkAssoc)->fetch();
			} catch (Exception $e) {
				$errors['all'] = 'Parameter ergeben keinen gültigen Verweis!';
				$this->logger->err('Fehler bei Abfrage: '.$e->getMessage());
			}
			if ($assoc) $lang_entries = $this->db->query($statement)->fetchAll();
			else $errors['all'] = 'Keine Assoziation gefunden!';
		}
		$params = $this->loadDependencies();
		$params['errors'] = $errors;
		$params['table'] = $this->getParam('table');
		$params['column'] = $this->getParam('column');
		$params['key'] = $this->getParam('key');
		$params['value'] = $assoc[$this->getParam('column')];
		$this->view->params = $params;
		$this->view->data = $lang_entries;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');
	}
	
	public function editAction()
	{
		$errors = array();
		$localization = array();
		if ($this->getRequest()->isPost()) {
			$this->logger->info('POST: '.print_r($_POST, true));
			$localization_table = new Application_Model_LocalizationModel();
			isset($_POST['No']) ? $localization['No'] = $_POST['No'] : $localization['No'] = 0;
			isset($_POST['table_name']) ? $localization['table_name'] = $_POST['table_name'] : $localization['table_name'] = 0;
			isset($_POST['column_name']) ? $localization['column_name'] = $_POST['column_name'] : $localization['column_name'] = '';
			isset($_POST['key_val']) ? $localization['key_val'] = $_POST['key_val'] : $localization['key_val'] = '';
			isset($_POST['language']) ? $localization['language'] = $_POST['language'] : $localization['language'] = '';
			isset($_POST['entry']) ? $localization['entry'] = $_POST['entry'] : $localization['entry'] = '';
			if ($localization['No']===0) $errors['No'] = 'Kein Identifier übergeben!';
			if ($localization['table_name']=='') $errors['table_name'] = 'Keine Tabelle übergeben!';
			if ($localization['column_name']=='') $errors['column_name'] = 'Keine Spalte übergeben!';
			if ($localization['key_val']=='') $errors['key_val'] = 'Es wurde kein Identifier übergeben!';
			if ($localization['language']=='') $errors['language'] = 'Es wurde keine Sprache übergeben!';
//			if ($localization['entry']=='') $errors['entry'] = 'Der Eintrag darf nicht leer sein!';
			if (count($errors)==0) {
				try {
					if ($localization['No']=='new') {
						unset($localization['No']);
						$localization_table->insert($localization);
						$localization['No'] = $this->db->LastInsertId();
					} else {
						$this->logger->info('Localization: '.print_r($localization, true));
						$localization_table->update($localization, array('No=?'=>$localization['No']));
					}
				} catch (Exception $e) {
					$this->logger->err('Sprachversion nicht gespeichert '.$e->getMessage());
				}
			}	
		} else {
			$errors['all'] = 'Es wurde kein POST-Request gesendet!';
		}
		$this->view->errors = $errors;
		$this->view->result = $localization;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');		
	}
	
	public function deleteAction()
	{
		$errors = array();
		$localization = array();
		if ($this->getRequest()->isPost()) {
			$localization_table = new Application_Model_LocalizationModel();
			isset($_POST['No']) ? $localization['No'] = $_POST['No'] : $localization['No'] = 0;
			isset($_POST['table_name']) ? $localization['table_name'] = $_POST['table_name'] : $localization['table_name'] = 0;
			isset($_POST['column_name']) ? $localization['column_name'] = $_POST['column_name'] : $localization['column_name'] = '';
			isset($_POST['key_val']) ? $localization['key_val'] = $_POST['key_val'] : $localization['key_val'] = '';
			isset($_POST['language']) ? $localization['language'] = $_POST['language'] : $localization['language'] = '';
			if ($localization['No']===0) $errors['No'] = 'Kein Identifier übergeben!';
			if ($localization['table_name']=='') $errors['table_name'] = 'Keine Tabelle übergeben!';
			if ($localization['column_name']=='') $errors['column_name'] = 'Keine Spalte übergeben!';
			if ($localization['key_val']=='') $errors['key_val'] = 'Es wurde kein Identifier übergeben!';
			if ($localization['language']=='') $errors['language'] = 'Es wurde keine Sprache übergeben!';
			if (count($errors)==0) {
				try {
					$localization_table->delete(array('No=?'=>$localization['No']));
				} catch (Exception $e) {
					$errors['all'] = 'Sprache konnte nicht gelöscht werden!';
					$this->logger->err('Sprache konnte nicht gelöscht werden! '.$e->getMessage());
				}
			}
		} else $errors['all'] = 'Kein POST request gesendet!';
		$this->view->errors = $errors;
		$this->view->result = $localization;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');		
	}
	
	public function getAction()
	{
		$errors = array();
		$statement = $this->db->select();
		$statement->from('localization', '*');
		if ($this->hasParam('No'))
			$statement->where('No = ?', $this->getParam('No'));
		if ($this->hasParam('table'))
			$statement->where('table = ?', $this->getParam('table'));
		if ($this->hasParam('column'))
			$statement->where('column = ?', $this->getParam('column'));
		if ($this->hasParam('language'))
			$statement->where('language = ?', $this->getParam('language'));
		$lang_entries = $this->db->query($statement)->fetchAll();
		$this->view->errors = $errors;
		$this->view->results = $lang_entries;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');
	}
	
	public function labeldialogAction()
	{
		$type = '';
		$view = '';
		$column = '';
		$errors = array();
		$labels = array();
		$No = null;
		$select = $this->db->select()->from('view_label', '*');
		if ($this->hasParam('No')) {
			$No = $this->getParam('No');
			$elect->where('No=?', $No);
		}
		if ($this->hasParam('type')) {
			$select->where('type = ?', $this->getParam('type'));
			$type = $this->getParam('type');
		} elseif ($No==null) $errors['type'] = 'Es wurde kein Typ aufgerufen!';
		if ($this->hasParam('table')) {
			$select->where('view_name = ?', $this->getParam('table'));
			$view = $this->getParam('table');
		} elseif ($No==null) $errors['view'] = 'Keine Tabelle/Ansicht aufgerufen!';
		if ($this->hasParam('column')) {
			$select->where('column_name = ?', $this->getParam('column'));
			$column = $this->getParam('column');
		} elseif ($No==null) $errors['column'] = 'Keine Spalte aufgerufen!';
		if ($this->hasParam('language')) {
			$select->where('language = ?', $this->getParam('language'));
		}
		if (count($errors)==0) {
			$labels = $this->db->query($select)->fetchAll();
		}
		if ($No) {
			if (count($labels)>0) {
				$type = $labels[0]['type'];
				$view = $labels[0]['view_name'];
				$column = $labels[0]['column_name'];
			} else $errors['No'] = 'Eintrag nicht gefunden!';
		} elseif (count($errors)==0) {
			if ($this->getParam('type')==0) {
				try {
					$this->db->query($this->db->select()->from($view, $column))->fetch();
				} catch (Exception $e) {
					$errors['all'] = 'Ansicht oder Spalte existiert nicht!';
				}
			} elseif($this->getParam('type')==1) {
				$view_section = explode('_', $view);
				if (!file_exists(APPLICATION_PATH.'/views/scripts/'.$view_section[0].'/'.$view_section[1])) $errors['all'] = 'Ansicht existiert nicht';
			}
		}
		$params = $this->loadDependencies();
		$params['type'] = $type;
		$params['view'] = $view;
		$params['column'] = $column;
		$params['errors']  = $errors;
		$this->view->params = $params;
		$this->view->data = $labels;
		$layout = $this->_helper->layout();
		$layout->setLayout('dialog_layout');
	}
	
	public function editlabelAction()
	{
		$errors = array();
		$localization = array();
		if ($this->getRequest()->isPost()) {
			$this->logger->info('POST: '.print_r($_POST, true));
			$localization_table = new Application_Model_ViewLabelModel();
			isset($_POST['No']) ? $localization['No'] = $_POST['No'] : $localization['No'] = 0;
			isset($_POST['type']) ? $localization['type'] = $_POST['type'] : $localization['type']=-1;
			isset($_POST['table_name']) ? $localization['view_name'] = $_POST['table_name'] : $localization['view_name'] = 0;
			isset($_POST['column_name']) ? $localization['column_name'] = $_POST['column_name'] : $localization['column_name'] = '';
			isset($_POST['language']) ? $localization['language'] = $_POST['language'] : $localization['language'] = '';
			isset($_POST['label']) ? $localization['label'] = $_POST['label'] : $localization['label'] = '';
			isset($_POST['label_short']) ? $localization['label_short'] = $_POST['label_short'] : $localization['label_short'] = '';
			if ($localization['No']===0) $errors['No'] = 'Kein Identifier übergeben!';
			if ($localization['type']===-1) $errors['type'] = 'Es wurde kein Typ übergeben!';
			if ($localization['view_name']=='') $errors['view_name'] = 'Keine Ansicht/Tabelle übergeben!';
			if ($localization['column_name']=='') $errors['column_name'] = 'Keine Spalte übergeben!';
			if ($localization['language']=='') $errors['language'] = 'Es wurde keine Sprache übergeben!';
//			if ($localization['entry']=='') $errors['entry'] = 'Der Eintrag darf nicht leer sein!';
			if (count($errors)==0) {
				try {
					if ($localization['No']=='new') {
						unset($localization['No']);
						$localization_table->insert($localization);
						$localization['No'] = $this->db->LastInsertId();
					} else {
						$this->logger->info('Localization: '.print_r($localization, true));
						$localization_table->update($localization, array('No=?'=>$localization['No']));
					}
				} catch (Exception $e) {
					$this->logger->err('Sprachversion nicht gespeichert '.$e->getMessage());
					$errors['all'] = 'Label wurde nicht gespeichert!';
				}
			}	
		} else {
			$errors['all'] = 'Es wurde kein POST-Request gesendet!';
		}
		$this->view->errors = $errors;
		$this->view->result = $localization;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');		
	}
	
	public function deleteLabelAction()
	{
		$errors = array();
		$localization = array();
		if ($this->getRequest()->isPost()) {
			$localization_table = new Application_Model_LocalizationModel();
			isset($_POST['No']) ? $localization['No'] = $_POST['No'] : $localization['No'] = 0;
			isset($_POST['table_name']) ? $localization['table_name'] = $_POST['table_name'] : $localization['table_name'] = 0;
			isset($_POST['column_name']) ? $localization['column_name'] = $_POST['column_name'] : $localization['column_name'] = '';
			isset($_POST['key_val']) ? $localization['key_val'] = $_POST['key_val'] : $localization['key_val'] = '';
			isset($_POST['language']) ? $localization['language'] = $_POST['language'] : $localization['language'] = '';
			if ($localization['No']===0) $errors['No'] = 'Kein Identifier übergeben!';
			if ($localization['table_name']=='') $errors['table_name'] = 'Keine Tabelle übergeben!';
			if ($localization['column_name']=='') $errors['column_name'] = 'Keine Spalte übergeben!';
			if ($localization['key_val']=='') $errors['key_val'] = 'Es wurde kein Identifier übergeben!';
			if ($localization['language']=='') $errors['language'] = 'Es wurde keine Sprache übergeben!';
			if (count($errors)==0) {
				try {
					$localization_table->delete(array('No=?'=>$localization['No']));
				} catch (Exception $e) {
					$errors['all'] = 'Sprache konnte nicht gelöscht werden!';
					$this->logger->err('Sprache konnte nicht gelöscht werden! '.$e->getMessage());
				}
			}
		} else $errors['all'] = 'Kein POST request gesendet!';
		$this->view->errors = $errors;
		$this->view->result = $localization;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultxml.phtml');		
	}
	
}