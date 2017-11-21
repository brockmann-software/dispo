<?php
class QualitycheckpointController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	protected $config;
	
	public function init()
	{
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
		$this->config = Zend_Registry::get('config');
		$this->view->headTitle('Qualitätsmerkmale');
	}
	
	public function indexAction()
	{
		$quality_checkpoints = $this->db->query("SELECT * FROM v_quality_checkpoint ORDER BY type, No")->fetchAll();
		$this->view->data = $quality_checkpoints;
	}
	
	public function editgeneralAction()
	{
		$errors = array();
		$qcheckpointTable = new Application_Model_Qualitycheckpoint();
		if ($this->getRequest()->isPost()) {
			isset($_POST['curNo']) ? $curNo : $_Post['curNo'] = $curNo = 0;
			isset($_POST['quality_checkpoint']) ? $qcheckpoint['quality_checkpoint'] = $_POST['quality_checkpoint'] : $qcheckpoint['quality_checkpoint']='';
			isset($_POST['type']) ? $qcheckpoint['type'] = $_POST['type'] : $qcheckpoint['type'] = 0;
			isset($_POST['checkpoint_class']) ? $qcheckpoint['checkpoint_class'] = $_POST['checkpoint_class'] : $qcheckpoint['checkpoint_class'] = 0;
			isset($_POST['max_good']) ? $qcheckpoint['max_good'] = $_POST['max_good'] : $qcheckpoint['max_good'] = 0;
			isset($_POSt['max_regular']) ? $qcheckpoint['max_regular'] = $_POST['max_regular'] : $qcheckpoint['max_regular'] = 0;
			isset($_POST['operator']) ? $qcheckpoint['operator'] = $_POST['operator'] : $qcheckpoint['operator'] = 0;
			if ($qcheckpoint['quality_checkpoint']=='') $errors['quality_checkpoint'] = 'Prüfpunkt darf nicht leer sein!';
			if (($qcheckpoint['operator']<2) && ($qcheckpoint['max_good'] = 0)) $errors['max_good']='Der Maximalwert für Gut darf nicht 0 sein!';
			if (($qcheckpoint['operator']<2) && ($qcheckpoint['max_regular'] = 0)) $errors['max_regular']='Der Maximalwert für Gut darf nicht 0 sein!';
			if (($qcheckpoint['operator']<2) && ($qcheckpoint['max_regular']<=$qcheckpoint['max_good'])) $errors['max_regular']='Der Wert muss größer sein als der Maximalwert für Gut!';
			if (count($errors)==0) try {
				if ($curNo<>0) {
					$qcheckpointTable->update($qcheckpoint, array('No'=>$curNo));
					$this->logger->info('Änderungen für qcheckpoint wurden gespeichert');
				} else {
					$qcheckpointTable->insert($qcheckpoint);
					$this->logger->info('Qcheckpoint wurde hinzugefügt');
				}
			} catch (Exception $e){
				$this->logger->err('Fehler beim speichern des Checkpoints '.print_r($qcheckpoint, true).' '.$e->getMessage());
			}
		} else {
			if ($this->hasParam('No')) {
				$qcheckpoints = $qcheckpointTable->find($this->getParam('No'));
				if ($RSqcheckpoints->count()>0) $Rqcheckpoint = $Rqcheckpoints->fetch() else $qcheckpoint = $qcheckpointTable->createRecord();
			} else $Rqcheckpoint = $qcheckpointTable->createRecord();
			$qcheckpoint = $Rqcheckpoint->toArray();
		}
		$this->view->data = $qcheckpoint;
	}
}