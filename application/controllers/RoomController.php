<?php

class roomController extends Zend_Controller_Action
{
	protected $db;
	protected $logger;
	
    public function init()
    {
        /* Initialize action controller here */
    	$this->view->headTitle('Räume');
		$this->view->title = 'Räume';
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
    }
	
	function loadDependencies()
	{
		$dependencies['stock_locations'] = $this->db->query('SELECT * FROM stock_location')->fetchAll();
		return $dependencies;
	}

    public function indexAction()
    {
        // action body
		$this->logger->Info('Room Index');
		$statement = $this->db->query("select * from v_room");
		$result = $statement->fetchAll();
		$this->view->result = $result;
    }
	
	public function editAction()
	{
		$roomTable = new Application_Model_RoomModel();
		$errors = array();
		if ($this->getRequest()->isPost()){
			isset($_POST['No']) ? $room['No'] = $_POST['No'] : $room['No'] = '';
			isset($_POST['room']) ? $room['room'] = $_POST['room'] : $room['room'] = '';
			isset($_POST['stock_location']) ? $room['stock_location'] = $_POST['stock_location'] : $room['stock_location'] = 0;
			$this->logger->info('Raum: '.print_r($room, true));
			if ($room['room'] == '') $errors['room'] = 'Raum darf nicht leer sein';
			if ($room['stock_location'] == 0) $errors['stock_location'] = 'Es muss ein Lagerort ausgewählt sein!';
			if (count($errors)==0) {
				if ($room['No'] == '') {
					try {
						$roomTable->insert($room);
					} catch (Exception $e) {
						$errors['all'] = 'Raum wurde nicht gespeichert '+$e->getMessage();
						$this->logger->info('Der Raum wurde nicht hinzugefügt! '.$e->getMessage());
					}
				} else {
					try {
						$roomTable->update($room, array('No = ?'=>$room['No']));
					} catch (Exception $e) {
						$errors['all'] = 'Die Änderung wurde nicht gespeichert! '.$e->getMessage();
						$this->logger->info('Der Raum wurde nicht gespeichert! '.$e->getMessage());
					}
				}
				$this->_redirect('room/index');
			}
		} else {
			$no = $this->getParam('No');
			if (empty($no)==false) {
				$room = $roomTable->find($no)->current()->toArray();
			} else {
				$room = array('No'=>'', 
							'room'=>'',
							'stock_location'=>1);
			}
		}
		$this->logger->info('Room Edit '.print_r($room, true));
		$params = $this->loadDependencies();
		$params['data'] = $room;
		$params['errors'] = $errors;
		$params['title'] = 'Raum';
		$this->view->params = $params;
		$this->view->subtemplate ='room/edit.phtml';
	}
	
}