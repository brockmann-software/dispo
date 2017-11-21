<?php
class inboundController extends Zend_Controller_Action
{
	protected $db;
	protected $global_settings;
	protected $logger;
	protected $config;
	
	private function calcQResult($qcheckpoint, $base, $result)
	{
		switch($qcheckpoint['operator']) {
			case 0 : $res_percent = $result;
					if ($res_percent>=$qcheckpoint['max_good']) {
						if ($res_percent>=$qcheckpoint['max_regular']) {
							$res_level = 'check_red';
						} else {
							$res_level = 'check_yellow';
						}
					} else {
						$res_level = 'check_green';
					}
					break;
			case 1 : ($base<>0) ? $res_percent = round($result/$base*100,1) : $res_percent = 0;
					if (abs($res_percent)>=$qcheckpoint['max_good']) {
						if (abs($res_percent)>=$qcheckpoint['max_regular']) {
							$res_level = 'check_red';
						} else {
							$res_level = 'check_yellow';
						}
					} else {
						$res_level = 'check_green';
					}
					$res_percent = $res_percent.' %';
					break;
			case 2 : if ($result==false) {
						$res_percent = 'nicht OK';
						$res_level = 'check_red';
					} else {
						$res_percent = 'OK';
						$res_level = 'check_green';
					}
					break;
		}
		return array($res_percent, $res_level);
	}
	
	public function init()
	{
    	$this->view->headTitle('Wareneingang');
		$this->view->title = 'Wareneingang';
		$this->db = Zend_Registry::get('db');
		$this->logger = Zend_Registry::get('logger');
		$this->config = Zend_Registry::get('config');
		$this->global_settings = $this->db->query("SELECT * FROM global_settings")->fetchAll()[0];
	}
	
	private function loadDependencies()
	{
		$db = $this->db;
		$dependencies['products'] = $db->query("select * from product")->fetchAll();
		$dependencies['countries'] = $db->query("select * from country")->fetchAll();
		$dependencies['packagings'] = $db->query("select * from packaging")->fetchAll();
		$dependencies['t_packagings'] = $db->query("select * from tp_format")->fetchAll();
		$dependencies['labels'] = $db->query("select * from label")->fetchAll();
		$dependencies['qualities'] = $db->query("select * from quality")->fetchAll();
		$dependencies['brands'] = $db->query("select * from brand")->fetchAll();
		$dependencies['quality_classes'] = $db->query("select * from quality_class")->fetchAll();
		$dependencies['pallets']=$db->query("SELECT * FROM pallet")->fetchAll();
		$dependencies['certificates']=$db->query("SELECT * FROM certification")->fetchAll();
		$dependencies['qchk_class']=$db->query('SELECT * FROM qcheckpoint_class WHERE No<4')->fetchAll();
		return $dependencies;
	}

	public function indexAction()
	{
        // action body
		$errors = array();
		if ($this->hasParam('error')) $errors['all'] = $this->getParam('error');
		$params = array();
		($this->hasParam('page')) ? $page = $this->getParam('page') : $page = 1;
		if ($this->hasParam('No')) {
			$params['No']['value'] = $this->getParam('No');
			$params['No']['type'] = 'number';
		}
		if ($this->hasParam('inbound')) {
			$params['inbound']['value'] = $this->getParam('inbound');
			$params['inbound']['type'] = 'number';
		}
		if ($this->hasParam('inb_arrival')) {
			$params['inb_arrival']['value'] = $this->getParam('inb_arrival');
			$params['inb_arrival']['type'] = 'date';
		}
		if ($this->hasParam('position')) {
			$params['position']['value'] = $this->getParam('position');
			$params['position']['type'] = 'string';
		}
		if ($this->hasParam('purchase_order')) {
			$params['purchase_order']['value'] = $this->getParam('purchase_order');
			$params['purchase_order']['type'] = 'string';
		}
		if ($this->hasParam('vendor_no')) {
			$params['vendor_no']['value'] = $this->getParam('vendor_no');
			$params['vendor_no']['type'] = 'string';
		}
		if ($this->hasParam('vendor_name')) {
			$params['vendor_name']['value'] = $this->getParam('vendor_name');
			$params['vendor_name']['type'] = 'string';
		}
		if ($this->hasParam('v_delivery_note')) {
			$params['v_delivery_note']['value'] = $this->getParam('v_delivery_note');
			$params['v_delivery_note']['type'] = 'string';
		}
		if ($this->hasParam('inb_trailor')) {
			$params['inb_trailor']['value'] = $this->getParam('inb_trailor');
			$params['inb_trailor']['type'] = 'string';
		}
		if ($this->hasParam('inb_container')) {
			$params['inb_container']['value'] = $this->getParam('inb_container');
			$params['inb_container']['type'] = 'string';
		}
		if ($this->hasParam('variety')) {
			$params['variety']['value'] = $this->getParam('variety');
			$params['variety']['type'] = 'string';
		}
		if ($this->hasParam('product')) {
			$params['product']['value'] = $this->getParam('product');
			$params['product']['type'] = 'string';
		}
		$whereClause = '';
		$pNo = 0;
		foreach ($params as $key => $val) {
			($pNo==0) ? $whereClause.=' WHERE ' : $whereClause.=' AND ';
			$pNo++;
			switch ($val['type']) {
				case 'string':	$whereClause.= 'UPPER('.$key.') LIKE UPPER("%'.$val['value'].'%")';
								break;
				case 'date':	$whereClause.= "DATE(".$key.") = '".date('Y-m-d', strtotime($val['value']))."'";
								break;
				default: $whereClause.= $key.' = '.$val['value'];
			}
		}
		$this->logger->info('InboundController->IndexAction called with whereClause: '.$whereClause);
		$count_inb = $this->db->query("SELECT COUNT(DISTINCT inbound) AS CNT FROM v_inb_line".$whereClause)->fetchAll()[0]['CNT'];
		$pages = floor($count_inb/20)+1;
		$inbound = $this->db->query("select * from v_inb_line".$whereClause." GROUP BY inbound ORDER BY inb_arrival DESC LIMIT ".(($page-1)*20).", 20")->fetchAll();
		(count($inbound)>0) ? $inbound_lines = $this->db->query('SELECT * from v_inb_line WHERE inbound = ?', $inbound[0]['inbound']) : $inbound_lines = array();
		$this->view->pages = $pages;
		$this->view->count_inb = $count_inb;
		$this->view->inbounds = $inbound;
		$this->view->cur_page = $page;
		$this->view->inbound_lines = $inbound_lines;
		$this->view->errors = $errors;
	}
	
	public function printqualityAction()
	{
		$params = array();
		if ($this->hasParam('No')) {
			$params['No']['value'] = $this->getParam('No');
			$params['No']['type'] = 'number';
		}
		$inbound_line = $this->db->query('SELECT * FROM v_inb_line WHERE No = ?', $params['No']['value'])->fetchAll()[0];
		if ($inbound_line) {
			$po_arrival = explode(' ', $inbound_line['po_arrival']);
			$this->logger->info('Inbund_line: '.print_r($inbound_line, true));
			$this->logger->info('po_arrival: '.print_r($po_arrival, true));
			$po_arrival_date = explode('-', $po_arrival[0]);
			$inbound_line['po_arrival_date'] = $po_arrival_date[2].'.'.$po_arrival_date[1].'.'.$po_arrival_date[0];
			$inbound_line['po_arrival_time'] = $po_arrival[1];
			$inb_arrival = explode(' ', $inbound_line['inb_arrival']);
			$this->logger->info('inb_arrival: '.print_r($inb_arrival, true));
			$inb_arrival_date = explode('-', $inb_arrival[0]);
			$inbound_line['inb_arrival_date'] = $inb_arrival_date[2].'.'.$inb_arrival_date[1].'.'.$inb_arrival_date[0];
			$inbound_line['inb_arrival_time'] = $inb_arrival[1];			
			$qc_inb_lines_log = $this->db->query('SELECT * FROM v_qc_inb_line WHERE inbound_line = ? and qc_type_no = 1', $inbound_line['No'])->fetchAll();
			$qc_inb_lines_quality = $this->db->query('SELECT * FROM v_qc_inb_line WHERE inbound_line = ? and qc_type_no = 0 ORDER BY qc_class_no, No', $inbound_line['No'])->fetchAll();
			$qc_class = $this->db->query('SELECT * FROM qcheckpoint_class')->fetchAll();
			foreach ($qc_inb_lines_log as &$qc_inb_line_log) {
				$qc_inb_line_log['res_percent'] = $this->calcQResult($qc_inb_line_log, $qc_inb_line_log['base'], $qc_inb_line_log['value'])[0];
				$qc_inb_line_log['res_level'] = $this->calcQResult($qc_inb_line_log, $qc_inb_line_log['base'], $qc_inb_line_log['value'])[1];
			}
			foreach ($qc_inb_lines_quality as &$qc_inb_line_quality) {
				$qc_inb_line_quality['res_percent'] = $this->calcQResult($qc_inb_line_quality, $qc_inb_line_quality['base'], $qc_inb_line_quality['value'])[0];
				$qc_inb_line_quality['res_level'] = $this->calcQResult($qc_inb_line_quality, $qc_inb_line_quality['base'], $qc_inb_line_quality['value'])[1];
			}
			$this->logger->info('cq_inb_lines_log: '.print_r($qc_inb_lines_log, true));
			$this->logger->info('qc_inb_lines_quality: '.print_r($qc_inb_lines_quality, true));
			$pictures = $this->db->query('SELECT * FROM attachment WHERE inbound_line = ? and type = 1', $inbound_line['No'])->fetchAll();
			$attachments = $this->db->query('SELECT * from attachment WHERE inbound_line = ? and type = 2', $inbound_line['No'])->fetchAll();
			$pathToTemplate = realpath($this->config->template->pdf).'/InboundProtokoll.pdf';
			$this->view->pathToTemplate = $pathToTemplate;
			$this->view->inbound_line = $inbound_line;
			$this->view->qc_inb_lines_log = $qc_inb_lines_log;
			$this->view->qc_inb_lines_quality = $qc_inb_lines_quality;
			$this->view->pictures = $pictures;
			$this->view->picture_path = realpath($this->config->upload->quality->pictures);
			$this->view->attachments = $attachments;
			$this->view->att_path = realpath($this->config->upload->quality->attachments);
			$this->view->qc_classes = $qc_class;
			$this->view->filename = 'Quality Report '.$inbound_line['No'].'.pdf';
			$layout = $this->_helper->layout();
			$layout->setLayout('pdf_layout');
			$this->renderScript('/inbound/pdfinbound.php');
			
/*			
			$layout->setLayout('print_layout');
*/
		}
	}

	public function printpalletlabelAction()
	{
		require_once(APPLICATION_PATH.'/controllers/variantController.php');
		$variantTable = new Application_Model_VariantModel();
		$errors = array();
		if ($this->getRequest()->isPost()) {
			isset($_POST['position']) ? $data['position'] = $_POST['position'] : $data['position'] = '';
			isset($_POST['purchase_order']) ? $data['purchase_order'] = $_POST['purchase_order'] : $data['purchase_order'] = '';
			isset($_POST['vendor_name']) ? $data['vendor_name'] = $_POST['vendor_name'] : $data['vendor_name'] = '';
			isset($_POST['product']) ? $data['product'] = $_POST['product'] : $data['product'] = '';
			isset($_POST['items']) ? $data['items'] = $_POST['items']  : $data['items'] = 0;
			isset($_POST['weight_item']) ? $data['weight_item'] = $_POST['weight_item']  : $data['weight_item'] = 0;
			isset($_POST['packaging']) ? $data['packaging'] = $_POST['packaging']  : $data['packaging'] = 0;
			isset($_POST['t_packaging']) ? $data['t_packaging'] = $_POST['t_packaging']  : $data['t_packaging'] = '';
			isset($_POST['origin']) ? $data['origin'] = $_POST['origin']  : $data['origin'] = 0;
			isset($_POST['quality_class']) ? $data['quality_class'] = $_POST['quality_class']  : $data['quality_class'] = 0;
			isset($_POST['quality']) ? $data['quality'] = $_POST['quality']  : $data['quality'] = 0;
			isset($_POST['label']) ? $data['label'] = $_POST['label']  : $data['label'] = 0;
			isset($_POST['brand']) ? $data['brand'] = $_POST['brand']  : $data['brand'] = 0;
			isset($_POST['lot']) ? $data['lot'] = $_POST['lot']  : $data['lot'] = '';
			isset($_POST['barcode']) ? $data['barcode'] = $_POST['barcode']  : $data['barcode'] = '';
			isset($_POST['pallets']) ? $data['pallets'] = $_POST['pallets']  : $data['pallets'] = 0;
			if ($data['position']=='') $errors['position'] = 'Es muss eine Position vergeben sein!';
			if ($data['purchase_order']=='') $errors['purchase_order'] = 'Bestellnummer darf nicht leer sein!';
			if (($data['items']=='') || ($data['items']==0)) $errors['items'] = "Einheit darf nicht leer oder 0 sein!";
			if (($data['weight_item']=='') || ($data['weight_item']==0)) $errors['weight_item'] = "Gewicht/Einheit darf nicht leer oder 0 sein";
			if (count($errors)==0) {
				$variant = variantController::buildNo($data);
				$data['variant'] = $variant['No'];
				// falls Variante nicht existiert, anlegen	
				$variants = $variantTable->find($variant['No']);
				if ($variants->count()==0) {
					try {
						$variantTable->insert($variant);
						$this->logger->info('Variante wurde hinzugefügt: '.print_r($variant['No'], true));
					} catch (Exception $e) {
						$errors['all'] = 'Eine neue Variante konnte nicht angelegt werden! '.$e->getMessage();
						$this->logger->info('Fehler bei Variante: '.$e->getMessage());
					}
				}
				$variant = $this->db->query('SELECT * FROM v_variant WHERE No = ?', $variant['No'])->fetchAll()[0];
				$this->view->data = $data;
				$this->view->variant = $variant;
				$this->view->filename = 'palletlabel.pdf';
				$layout = $this->_helper->layout();
				$layout->setLayout('pdf_layout');
				$this->renderScript('/inbound/pdfpalletlabel.php');
				//$this->_redirect('/inbound/index/');
			}
		} else {
			$data = array(
					'position'=>'',
					'purchase_order'=>'',
					'vendor_name'=>'',
					'product'=>'',
					'items'=>0,
					'weight_item'=>0,
					'packaging'=>0,
					't_packaging'=>'',
					'origin'=>'',
					'quality_class'=>1,
					'quality'=>1,
					'label'=>0,
					'brand'=>0,
					'lot'=>'',
					'barcode'=>'',
					'pallets'=>0);
		}
		$params = $this->loadDependencies();
		$this->view->params = $params;
		$this->view->data = $data;
		$this->view->errors = $errors;
	}
	
	public function editAction()
	{
		$errors = array();
		// No operation
		if ($this->hasParam('No')) {
			$inbounds = $this->db->query("SELECT * FROM v_inbound WHERE No = ?", $this->getParam('No'));
			if (count($inbounds)>0) {
				$inbound = $inbounds[0];
				$inbound['arrival_date'] = date('d.m.Y', strtotime($inbound['arrival']));
				$inbound['arrival_time'] = date('m.s', strtotime($inbound['arrival']));
			}
		} else {
			$inbond=array(
				'No'=>0,
				'position'=>'',
				'vendor'=>'',
				'purchase_order'=>'',
				'stock_location'=>1,
				'departure_date'=>$date->get(Zend_Date::DATES),
				'departure_time'=>$date->get(Zend_Date::TIME_SHORT),
				'arrival_date'=>$date->get(Zend_Date::DATES),
				'arrival_time'=>$date->get(Zend_Date::TIME_SHORT),
				'truck'=>'',
				'trailor'=>'',
				'container'=>'',
				'vessel'=>0,
				'habour'=>0);
		}
		$params['data']=$inbound;
		$params['title']='Wareneingang';
		$params['errors']=$errors;
		$this->view->params=$params;
	}
	
	public function editpicturesAction()
	{
		$errors = array();
		$attachment_table = new Application_Model_AttachmentModel();
		if ($this->getRequest()->isPost()) {
			$this->logger->info('POST: '.print_r($_POST, true));
			isset($_POST['inbound_line']) ? $inbound_line['No'] = $_POST['inbound_line'] : $errors['No'] = 'Es wurde keine Inbound_line übergeben!';
			try {
				$inbound_line = $this->db->query('SELECT * FROM v_inb_line WHERE No = ?', $inbound_line['No'])->fetchAll()[0];
			} catch (Exception $e) {
				$errors['sql'] = 'Inbound Line existiert nicht!';
				$this->logger->err("Inbound Line {$inbound_line['No']} wurde nicht geladen! ".$e->getMessage());
			}
			$pictures = $_FILES['picture'];
			$attachments = $_FILES['attachment'];
			if (count($errors)==0) {
				// Bilder und Dokumente löschen
				foreach($_POST as $key => $value) {
					if (is_numeric($key) and $value==true) {
						try {
							$attachment = $attachment_table->find($key)->current();
							unlink(realpath($this->config->upload->quality->pictures).'/'.$attachment->path);
							$attachment->delete();
						} catch (Exception $e) {
							$errors[$key] = "Anhang {$attachment->path} konnte nicht gelöscht werden!";
							$this->logger->err("Anhang {$attachment->path} konnte nicht gelöscht werden! ".$e->getMessage());
						}
					}
				}
				// Bilder speichern
//				$this->logger->info('pictures: '.print_r($pictures));
				$destination = realpath($this->config->upload->quality->pictures);
				$pic_count = $this->db->query('SELECT COUNT(inbound_line) as cnt_att FROM attachment WHERE inbound_line = ? AND type = 1', $inbound_line['No'])->fetchAll()[0]['cnt_att'];
				$attachment = array();
				foreach ($pictures['error'] as $key => $error) {
					try {
						if ($error==0) {
							$pic_count++;
							preg_match('#\.([a-z0-9]+)$#i', basename($pictures['name'][$key]), $tmp);
							$sufix = $tmp[1];
							$file_name = 'pic_'.$inbound_line['position'].'_'.$inbound_line['No'].'_'.$pic_count.($sufix!='' ? '.'.$sufix : '');
							$fullname = $destination.'/'.$file_name;
							$this->logger->info('Destination: '.$fullname);
							$this->logger->info('Temp-Name: '.$pictures['tmp_name'][$key]);
							if (!move_uploaded_file($pictures['tmp_name'][$key], $fullname)) 
								 throw new Exception('Fehler bei Dateiupload');
							$attachment['inbound_line'] = $inbound_line['No'];
							$attachment['path'] = $file_name;
							$attachment['type'] = 1;
							$attachment_table->insert($attachment);
							$this->logger->info('Attachment: '.print_r($attachment, true));
						}
					} catch (Exception $e) {
						$errors['upload'] = 'Bild konnten nicht gespeichert werden';
						$this->logger->err('Bild wurden nicht gespeichert: '.$e->getMessage());
					}
				}
				$destination = realpath($this->config->upload->quality->attachments);
				$att_count = $this->db->query('SELECT COUNT(inbound_line) AS cnt_att FROM attachment WHERE inbound_line = ? AND type = 2', $inbound_line['No'])->fetchAll()[0]['cnt_att'];
//				$this->logger->info('Attachments: '.print_r($attachments, true));
				foreach ($attachments['error'] as $key => $error) {
					try {
						if ($error==0) {
							$att_count++;
							preg_match('#\.([a-z0-9]+)$#i', basename($attachments['name'][$key]), $tmp);
							$sufix = $tmp[1];
							$file_name = 'att_'.$inbound_line['position'].'_'.$inbound_line['No'].'_'.$att_count.($sufix!='' ? '.'.$sufix : '');
							$fullname = $destination.'/'.$file_name;
							$this->logger->info('Destination: '.$fullname);
							$this->logger->info('Temp-Name: '.$attachments['tmp_name'][$key]);
							if (!move_uploaded_file($attachments['tmp_name'][$key], $fullname)) 
								 throw new Exception('Felhler bei Dateiupload');
							$attachment['inbound_line'] = $inbound_line['No'];
							$attachment['path'] = $file_name;
							$attachment['type'] = 2;
							$attachment_table->insert($attachment);
							$this->logger->info('Attachment: '.print_r($attachment, true));
						}
					} catch (Exception $e) {
						$errors['upload'] = 'Anhang konnte nicht gespeichert werden';
						$this->logger->err('Anhang wurde nicht gespeichert: '.$e->getMessage());
					}
				}
			}
			if (count($errors==0)) $this->_redirect('/inbound/adminpics/No/'.$inbound_line['No']);
		}
		
	}
	
	public function adminpicsAction()
	{
		if ($this->hasParam('No')) {
			$pictures = $this->db->query('SELECT * FROM attachment WHERE inbound_line = ? and type=1', $this->getParam('No'))->fetchAll();
			$attachments = $this->db->query('SELECT * FROM attachment WHERE inbound_line = ? and type=2', $this->getParam('No'))->fetchAll();
			$this->view->inbound_line = $this->getParam('No');
			$this->view->pictures = $pictures;
			$this->view->attachments = $attachments;
			$this->view->picture_path = realpath($this->config->upload->quality->pictures);
			$this->view->att_path = realpath($this->config->upload->quality->attachments);
		} else {
			$this->_redirect('inbound/index/');
		}
	}
	
	public function getinboundlinesAction()
	{
		$errors=array();
		$inboundlines=array();
		$params = array();
		if ($this->hasParam('No')) {
			$params['No']['value'] = $this->getParam('No');
			$params['No']['type'] = 'number';
		}
		if ($this->hasParam('inbound')) {
			$params['inbound']['value'] = $this->getParam('inbound');
			$params['inbound']['type'] = 'number';
		}
		if ($this->hasParam('purchase_order')) {
			$params['purchase_order']['value'] = $this->getParam('purchase_order');
			$params['purchase_order']['type'] = 'string';
		}
		if ($this->hasParam('vendor_no')) {
			$params['vendor_no']['value'] = $this->getParam('vendor_no');
			$params['vendor_no']['type'] = 'string';
		}
		if ($this->hasParam('variety')) {
			$params['variety']['value'] = $this->getParam('variety');
			$params['variety']['type'] = 'string';
		}
		if ($this->hasParam('product')) {
			$params['product']['value'] = $this->getParam('product');
			$params['product']['type'] = 'string';
		}
		$sqlStr = 'select * from v_inb_line';
		$pNo = 0;
		foreach ($params as $key => $val) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' AND ';
			$pNo++;
			$val['type']=='string' ? $sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val['value'].'%")' : $sqlStr.= $key.' = '.$val['value'];
		}
		try {
			$inboundlines = $this->db->query($sqlStr)->fetchAll();
		} catch (Exception $e) {
			$errors['all']=$e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->results = $inboundlines;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
		$this->renderScript('/xml/resultlistxml.phtml');
	}
	
	public function editqualityAction()
	{
		$errors = array();
		$purchase_order_table = new Application_Model_purchaseorderModel();
		$po_line_table = new Application_Model_PurchaseorderlineModel();
		$inbound_table = new Application_Model_InboundModel();
		$inbound_line_table = new Application_Model_inboundlineModel();
		$variantTable = new Application_Model_VariantModel();
		$qck_inb_line_table = new Application_Model_qckinblineModel();
		$movement_table = new Application_Model_MovementModel();
		$attachment_table = new Application_Model_AttachmentModel();
		$po_arrival = new Zend_Date();
		$inb_arrival = new Zend_Date();
		$date = new Zend_Date();
		if (($this->getRequest()->isPost()) and ($_POST['submit']=='Speichern')) {
		//Feldinhalte aus Formular empfangen	
			$this->logger->info('$_POST: '.print_r($_POST, true));
			$this->logger->info('$_FILES: '.print_r($_FILES, true));
			//purchase_order auslesen
			(isset($_POST['purchase_order'])) ? $purchase_order['No'] = $_POST['purchase_order'] : $purchase_order['No'] = '';
			(isset($_POST['stock_location'])) ? $purchase_order['stock_location']=$_POST['stock_location'] : $purchase_order['stock_location']=1;
			(isset($_POST['vendor_no'])) ? $purchase_order['vendor'] = $_POST['vendor_no'] : $purchase_order['vendor'] = '';
			(isset($_POST['po_arrival_date'])) ? $po_arrival_date=$_POST['po_arrival_date'] : $po_arrival_date = '';
			(isset($_POST['po_arrival_time'])) ? $po_arrival_time=$_POST['po_arrival_time'] : $po_arrival_time = '';
			(isset($_POST['po_transport_temp_min'])) ? $purchase_order['transport_temp_min']=$_POST['po_transport_temp_min'] : $purchase_order['transport_temp_min']=0;
			(isset($_POST['po_transport_temp_max'])) ? $purchase_order['transport_temp_max']=$_POST['po_transport_temp_max'] : $purchase_order['transport_temp_max']=0;
			(isset($_POST['forwarder'])) ? $purchase_order['forwarder'] = $_POST['forwarder'] : $purchase_order['forwarder'] = '';
			(isset($_POST['truck'])) ? $purchase_order['truck'] = $_POST['truck'] : $purchase_order['truck'] = '';
			(isset($_POST['trailor'])) ? $purchase_order['trailor'] = $_POST['trailor'] : $purchase_order['trailor'] = '';
			(isset($_POST['container'])) ? $purchase_order['container'] = $_POST['container'] : $purchase_order['container'] = '';
			if ($purchase_order['transport_temp_min']==0) $purchase_order['transport_temp_min']=$_POST['inb_transport_temp'];
			if ($purchase_order['transport_temp_max']==0) $purchase_order['transport_temp_max']=$_POST['inb_transport_temp'];
			$purchase_order['incoterm'] = 'DDP';
			$purchase_order['vessel'] = '';
			$purchase_order['habour'] = '';
			if (($purchase_order['No']=='') && ($this->global_settings['inb_wo_po']==1)) $purchase_order['No']=$date->get(Zend_Date::TIMESTAMP);

			//Inbound auslesen
			(isset($_POST['inbound'])) ? $inbound['No'] = $_POST['inbound'] : $inbound['No'] = 0;
			(isset($_POST['position'])) ? $inbound['position'] = $_POST['position'] : $inbound['position'] = '';
			(isset($_POST['stock_location'])) ? $inbound['stock_location']=$_POST['stock_location'] : $inbound['stock_location']=1;
			(isset($_POST['v_delivery_note'])) ? $inbound['v_delivery_note'] = $_POST['v_delivery_note'] : $inbound['v_delivery_note'] = '';
			$inbound['forwarder'] = $purchase_order['forwarder'];
			(isset($_POST['truck'])) ? $inbound['truck'] = $_POST['truck'] : $inbound['truck'] = '';
			(isset($_POST['trailor'])) ? $inbound['trailor'] = $_POST['trailor'] : $inbound['trailor'] = '';
			(isset($_POST['container'])) ? $inbound['container'] = $_POST['container'] : $inbound['container'] = '';
			(isset($_POST['inb_arrival_date'])) ? $inb_arrival_date=$_POST['inb_arrival_date'] : $inb_arrival_date = '';
			(isset($_POST['inb_arrival_time'])) ? $inb_arrival_time=$_POST['inb_arrival_time'] : $inb_arrival_time = '';
			(isset($_POST['inb_transport_temp'])) ? $inbound['transport_temperature']=$_POST['inb_transport_temp'] : $inbound['transport_temperature']=0;
			$inbound['purchase_order'] = $purchase_order['No'];			

			//variant auslesen
			(isset($_POST['variant'])) ? $varant['No']=$_POST['variant'] : $varant['No']='';
			(isset($_POST['product'])) ? $variant['product']=$_POST['product'] : $variant['product']='';
			(isset($_POST['items'])) ? $variant['items']=$_POST['items'] : $variant['items']=0;
			(isset($_POST['weight_item'])) ? $variant['weight_item']=$_POST['weight_item'] : $variant['weight_item']=0;
			(isset($_POST['packaging'])) ? $variant['packaging']=$_POST['packaging'] : $variant['packaging']='';
			(isset($_POST['t_packaging'])) ? $variant['t_packaging']=$_POST['t_packaging'] : $variant['t_packaging']='';
			(isset($_POST['origin'])) ? $variant['origin']=$_POST['origin'] : $variant['origin']='';
			(isset($_POST['quality_class'])) ? $variant['quality_class']=$_POST['quality_class'] : $variant['quality_class']=0;
			(isset($_POST['quality'])) ? $variant['quality']=$_POST['quality'] : $variant['quality']=0;
			(isset($_POST['label'])) ? $variant['label']=$_POST['label'] : $variant['label']=0;
			(isset($_POST['brand'])) ? $variant['brand']=$_POST['brand'] : $variant['brand']=0;
			//varant No bestimmen
			$variant['No'] = $variant['product']
							.$variant['origin']
							.$variant['items']
							.$variant['weight_item']
							.$variant['t_packaging']
							.$variant['packaging']
							.$variant['label']
							.$variant['quality']
							.$variant['quality_class']
							.$variant['brand'];

			//po_line auslesen
			(isset($_POST['po_line'])) ? $purchase_order_line['No'] = $_POST['po_line'] : $purchase_order_line['No']=0;
			$purchase_order_line['Purchase_order']=$purchase_order['No'];
			(isset($_POST['po_line_line'])) ? $purchase_order_line['Line']=$_POST['po_line_line'] : $purchase_order_line['Line'] = 0;
			$purchase_order_line['product'] = $variant['product'];
			$purchase_order_line['variant'] = $variant['No'];
			(isset($_POST['po_trading_units'])) ? $purchase_order_line['Trading_Units']=$_POST['po_trading_units'] : $purchase_order_line['Trading_Units']=0;
			$purchase_order_line['PU_TU'] = $variant['items'];
			(isset($_POST['po_packing_units'])) ? $purchase_order_line['Packing_Units']=$_POST['po_packing_units'] : $purchase_order_line['Packing_Units']=0;
			if ($purchase_order_line['Packing_Units']==0) $purchase_order_line['Packing_Units']=$purchase_order_line['Trading_Units']*$purchase_order_line['PU_TU'];
			(isset($_POST['po_pallet'])) ? $purchase_order_line['Pallet']=$_POST['po_pallet'] : $purchase_order_line['Pallet']=0;
			(isset($_POST['po_pallets'])) ? $purchase_order_line['Pallets']=$_POST['po_pallets'] : $purchase_order_line['Pallets']=0;
			(isset($_POST['po_tu_pal'])) ? $purchase_order_line['TU_Pallet']=$_POST['po_tu_pal'] : $purchase_order_line['TU_Pallet']=0;
			$purchase_order_line['Price']=0;
			$purchase_order_line['Price_Allocation']=0;
			(isset($_POST['inb_lot'])) ? $purchase_order_line['lot']=$_POST['inb_lot'] : $purchase_order_line['lot']='';
			(isset($_POST['inb_barcode'])) ? $purchase_order_line['barcode']=$_POST['inb_barcode'] : $purchase_order_line['barcode']='';
			(isset($_POST['po_brix_min'])) ? $purchase_order_line['brix_min']=$_POST['po_brix_min'] : $purchase_order_line['po_brix_min']=0;
			(isset($_POST['po_brix_max'])) ? $purchase_order_line['brix_max']=$_POST['po_brix_max'] : $purchase_order_line['po_brix_max']=0;

			//inbound_line auslesen
			(isset($_POST['old_No'])) ? $old_No = $_POST['old_No'] : $old_No = '';
			(isset($_POST['No'])) ? $inbound_line['No'] = $_POST['No'] : $inbound_line['No'] = '';
			(isset($_POST['inb_line'])) ? $inbound_line['line'] = $_POST['inb_line'] : $inbound_line['line'] = 0;
			(isset($_POST['lot'])) ? $inbound_line['lot']=$_POST['lot'] : $inbound_line['lot']='';
			$inbound_line['product'] = $variant['product'];
			$inbound_line['variant'] = $variant['No'];
			(isset($_POST['barcode'])) ? $inbound_line['barcode']=$_POST['barcode'] : $inbound_line['barcode']='';
			(isset($_POST['inb_trading_units'])) ? $inbound_line['trading_units']=$_POST['inb_trading_units'] : $inbound_line['trading_units']=0;
			(isset($_POST['inb_packing_units'])) ? $inbound_line['packing_units']=$_POST['inb_packing_units'] : $inbound_line['packing_units']=0;
			if ($inbound_line['packing_units'] == 0) $inbound_line['packing_units'] = $inbound_line['trading_units']*$variant['items'];
			(isset($_POST['inb_pallet'])) ? $inbound_line['Pallet']=$_POST['inb_pallet'] : $inbound_line['Pallet']=0;
			(isset($_POST['inb_pallets'])) ? $inbound_line['Pallets']=$_POST['inb_pallets'] : $inbound_line['Pallets']=0;
			(isset($_POST['inb_tu_pal'])) ? $inbound_line['tu_pallet']=$_POST['inb_tu_pal'] : $inbound_line['tu_pallet']=0;
			(isset($_POST['inb_brix'])) ? $inbound_line['brix']=$_POST['inb_brix'] : $inb_line['brix']=0;
			(isset($_POST['items_checked'])) ? $inbound_line['items_checked'] = $_POST['items_checked'] : $inbound_line['items_checked'] = 0;
			(isset($_POST['remarks'])) ? $inbound_line['remarks'] = $_POST['remarks'] : $inbound_line['remarks'] = '';
			(isset($_POST['checked_by'])) ? $inbound_line['checked_by'] = $_POST['checked_by'] : $inbound_line['checked_by'] = '';
			//
			$inbound_line['inbound']=$inbound['No'];
			$inbound_line['purchase_order']=$inbound['purchase_order'];
			$inbound_line['po_line']=$purchase_order_line['No'];
			
			//Felder im Formular mit den gesendeten Inhalten wieder vorbelegen, falls ein Fehler auftritt
			$inbound_sheet=array(
				'No'=>$inbound_line['No'],
				'inbound'=>$inbound_line['inbound'],
				'inb_line'=>$inbound_line['line'],
				'position'=>$inbound['position'],
				'v_delivery_note'=>$inbound['v_delivery_note'],
				'vendor'=>$purchase_order['vendor'],
				'purchase_order'=>$purchase_order['No'],
				'po_line' => $purchase_order_line['No'],
				'po_line_line' => $purchase_order_line['Line'],
				'stock_location'=>1,
				'po_arrival_date'=>$_POST['po_arrival_date'],
				'po_arrival_time'=>$_POST['po_arrival_time'],
				'inb_arrival_date'=>$_POST['inb_arrival_date'],
				'inb_arrival_time'=>$_POST['inb_arrival_time'],
				'forwarder'=>$purchase_order['forwarder'],
				'truck'=>$inbound['truck'],
				'trailor'=>$inbound['trailor'],
				'container'=>$inbound['container'],
				'po_transport_temp_min'=>$purchase_order['transport_temp_min'],
				'po_transport_temp_max'=>$purchase_order['transport_temp_max'],
				'inb_transport_temp'=>$inbound['transport_temperature'],
				'variant'=>$variant['No'],
				'product'=>$variant['product'],
				't_packaging'=>$variant['t_packaging'],
				'packaging'=>$variant['packaging'],
				'origin'=>$variant['origin'],
				'label'=>$variant['label'],
				'quality_class'=>$variant['quality_class'],
				'brand'=>$variant['brand'],
				'quality'=>$variant['quality'],
				'items'=>$variant['items'],
				'weight_item'=>$variant['weight_item'],
				'po_lot'=>$purchase_order_line['lot'],
				'po_barcode'=>$purchase_order_line['barcode'],
				'po_trading_units'=>$purchase_order_line['Trading_Units'],
				'po_packing_units'=>$purchase_order_line['Packing_Units'],
				'po_pallet'=>$purchase_order_line['Pallet'],
				'po_pallets'=>$purchase_order_line['Pallets'],
				'po_tu_pal'=>$purchase_order_line['TU_Pallet'],
				'inb_lot'=>$inbound_line['lot'],
				'inb_barcode'=>$inbound_line['barcode'],
				'items_checked'=>$inbound_line['items_checked'],
				'inb_trading_units'=>$inbound_line['trading_units'],
				'inb_packing_units'=>$inbound_line['packing_units'],
				'inb_pallet'=>$inbound_line['Pallet'],
				'inb_pallets'=>$inbound_line['Pallets'],
				'inb_tu_pal'=>$inbound_line['tu_pallet'],
				'po_brix_min' => $purchase_order_line['brix_min'],
				'po_brix_max' => $purchase_order_line['brix_max'],
				'inb_brix' => $inbound_line['brix'],
				'inb_certificate'=>1,
				'remarks' => $inbound_line['remarks'],
				'checked_by' => $inbound_line['checked_by']);
				//qualitätsmerkmale laden
			$qcheckpoints = $this->db->query('SELECT * FROM v_qc_product WHERE type=0 AND UPPER(product) = UPPER("?")',$prod_no)->fetchAll();
			if (count($qcheckpoints)==0) {
				$qcheckpoints = $this->db->query('SELECT * FROM v_quality_checkpoint WHERE type=0 ORDER BY qchk_class_no')->fetchAll();
			}
			foreach($qcheckpoints as $qcheckpoint) {
				$inbound_sheet['res_'.$qcheckpoint['qck_no']] = $_POST['res_'.$qcheckpoint['qck_no']];
				$inbound_sheet['base_'.$qcheckpoint['qck_no']] = $_POST['base_'.$qcheckpoint['qck_no']];
				$inbound_sheet['rp_'.$qcheckpoint['qck_no']] = $this->calcQResult($qcheckpoint, $_POST['base_'.$qcheckpoint['qck_no']], $_POST['res_'.$qcheckpoint['qck_no']])[0];
				$inbound_sheet['rl_'.$qcheckpoint['qck_no']] = $this->calcQResult($qcheckpoint, $_POST['base_'.$qcheckpoint['qck_no']], $_POST['res_'.$qcheckpoint['qck_no']])[1];
			}
			$qcheck_logs = $this->db->query('SELECT * FROM v_quality_checkpoint WHERE type=1 ORDER BY qchk_class_no')->fetchAll();
			foreach($qcheck_logs as $qcheck_log) {
				$inbound_sheet['res_'.$qcheck_log['qck_no']] = $_POST['res_'.$qcheck_log['qck_no']];
				$inbound_sheet['base_'.$qcheck_log['qck_no']] = $_POST['base_'.$qcheck_log['qck_no']];
				$inbound_sheet['rp_'.$qcheck_log['qck_no']] = $this->calcQResult($qcheck_log, $_POST['base_'.$qcheck_log['qck_no']], $_POST['res_'.$qcheck_log['qck_no']])[0];
				$inbound_sheet['rl_'.$qcheck_log['qck_no']] = $this->calcQResult($qcheck_log, $_POST['base_'.$qcheck_log['qck_no']], $_POST['res_'.$qcheck_log['qck_no']])[1];
			}				
			
			//Eingaben prüfen
			if ($purchase_order['vendor']=='') $errors['vendor']='Es muss ein Lieferant ausgewählt sein!';
			if ($inbound['position']=='') $errors['position']="Es muss eine Positionsnummer angegeben werden!";
			if ($inbound['transport_temperature']=='') $errors['inb_transport_temp']="Es muss die Transporttemperatur gemessen werden!";
			try {
				$po_arrival->set($po_arrival_date, Zend_Date::DATES);
				$po_arrival->set($po_arrival_time, Zend_Date::TIME_SHORT);
				$purchase_order['arrival']=$po_arrival->toString('YYYY-MM-dd HH:mm:ss');
				$purchase_order['departure']=$purchase_order['arrival'];
			} catch (Exception $e) {
				$errors['po_arrival']='Datum oder Uhrzeit sind nicht im richtigen Format';
			}
			try {
				$inb_arrival->set($inb_arrival_date, Zend_Date::DATES);
				$inb_arrival->set($inb_arrival_time, Zend_Date::TIME_SHORT);
				$inbound['arrival']=$inb_arrival->toString('YYYY-MM-dd HH:mm:ss');
			} catch (Exception $e) {
				$errors['inb_arrival']='Datum oder Uhrzeit sind nicht im richtigen Format';
			}
			if ($variant['No']=='') $errors['variant']="Es muss eine Variante ausgewählt sein!";
			if ($inbound['purchase_order']==0) {
				($global_settings['inb_wo_po']==1) ? $inbound['purchase_order'] = $date->get(Zend_Date::TIMESTAMP) : $errors['purchase_order']='Es muss eine Bestellung vorhanden sein!';
			}
			$this->logger->info('Errors nach Eingabe-Check: '.print_r($errors, true));
			$this->logger->info('purchase_order: '.print_r($purchase_order, true));
			$this->logger->info('Inbound: '.print_r($inbound, true));
			$this->logger->info('Variante: '.print_r($variant, true));
			$this->logger->info('purchase_order_line: '.print_r($purchase_order_line, true));
			$this->logger->info('inbound_line: '.print_r($inbound_line, true));

			if (count($errors)==0) {
			// Eingaben korrekt, Daten speichern.
				//Transaktion starten
				$this->logger->info('Transaktion gestartet');
				$this->db->beginTransaction();

				//check ob Bestellung bereits existiert, sonst mit Daten aus Qualitätsbericht füllen und anlegen.
				($inbound['purchase_order']<>'') ? $purchase_orders = $purchase_order_table->find($inbound['purchase_order']) : $purchase_orders=null;
				$this->logger->info('Anzahl Purchase Orders gefunden: '.$purchase_orders->count());
				if ($purchase_orders->count()==0) {
					try {
						$purchase_order_table->insert($purchase_order);
						$purchase_order['No'] = $this->db->lastInsertId();
						$this->logger->info('purchase order wurde hinzugefügt: '.print_r($purchase_order, true));
					} catch (Exception $e) {
						$errors['all'] = 'Eine neue Bestellung konnte nicht angelegt werden! '.$e->getMessage();
						$this->logger->info('Fehler bei Purchase Order: '.$e->getMessage());
					}
				} else {
					$this->logger->info(print_r($purchase_orders->current()->toArray(), true));
					$this->logger->info('Purchase Order gefunden mit No: '.$purchase_order['No']);
				}

				//prüfen on Inbound schon existiert
				$inboundRecs = $inbound_table->fetchAll(array('position = ?'=>$inbound['position']));
				$this->logger->info('Inbounds gefunden: '.$inboundRecs->count());
				if (($inbound['No']==0) and ($inboundRecs->count()==0)) {
					try {
						$inbound_table->insert($inbound);
						$inbound['No'] = $this->db->lastInsertId();
						$this->logger->info('inbound wurde hinzugefügt mit No '.$inbound['No']);
					} catch (Exception $e) {
						$errors['all']="Der Wareneingang konnte nicht angelegt werden! ".$e->getMessage();
						$this->logger->info('Fehler bei Inbound: '.$e->getMessage());
					}
				} else {
					try {
						$inboundRec = $inboundRecs->current();
						$inbound['No'] = $inboundRec->No;
						$inbound_table->update($inbound, array('No = ?' => $inboundRec->No));
						$inbound['No'] = $inboundRec->No;
						$this->logger->info('Inbound gespeichert mit Nummer: '.$inbound['No']);
					} catch (Exception $e) {
						$errors['all'] = "Der Wareneingang wurde nicht gespeichert! ".$e->getMessage();
						$this->logger->info('Fehler bei Inbound: '.$e->getMessage());
					}
				}
				
				//check ob Variante schon existiert, sonst anlegen
				$variants = $variantTable->find($variant['No']);
				if ($variants->count()==0) {
					try {
						$variantTable->insert($variant);
						$this->logger->info('Variante wurde hinzugefügt mit No: '.$this->db->lastInsertId());
					} catch (Exception $e) {
						$errors['all'] = 'Eine neue Variante konnte nicht angelegt werden! '.$e->getMessage();
						$this->logger->info('Fehler bei Variante: '.$e->getMessage());
					}
				} else {
					$this->logger->info('Variante gefunden: '.print_r($variants->current()->toArray(), true));
				}
				if ($purchase_order_line['No']==0) {
					//keine PO_Line vorhanden. Deshalb neu anlegen
					//erst schauen, ob für die PO schon weitere Zeilen existieren
					$stmnt = $this->db->query('SELECT * FROM purchase_order_line WHERE UPPER(Purchase_order) = UPPER(?) AND ass_line=0 ORDER BY Line DESC', array($purchase_order_line['Purchase_order']));
					$po_lines = $stmnt->fetchAll();
//					$this->logger->info(print_r($stmnt, true));
					if (count($po_lines)==0) {
						$purchase_order_line['Line']=10000;
						$this->logger->info('Es wurde keine Purchase Order Line zur Purchase Order '.$purchase_order['No'].' gefunden');
					} else {
						$this->logger->info('Es wurden '.count($po_lines).' Purchase Order Lines gefunden. Die höchste Zeile ist '.$po_lines[0]['Line']);
						$line = $po_lines[0]['Line'];
						$line = (floor($line/10000)+1)*10000;
						$purchase_order_line['Line'] = $line;
					}
					try {
						$po_line_table->insert($purchase_order_line);
						$purchase_order_line['No'] = $this->db->lastInsertId();
						$this->logger->info('Purchase order Line wurde hinzugefügt mit No: '.$purchase_order_line['No'].' und Line: '.$purchase_order_line['Line']);
					} catch (Exception $e) {
						$errors['all'] = 'Es wurde keine Bestellzeile hinzugefügt! '.$e->getMessage();
						$this->logger->info('Purchase Order Line wurde nicht gespeichert! '.$e->getMessage());
					}
				}
				if ($inbound_line['No']==0) {
					//Inbund_line nicht vorhanden. Deshalb neu anlegen
					//erst schauen, ob für die inbound_line schon weitere Zeilen existieren
					$inbound_line['inbound'] = $inbound['No'];
					$inbound_line['po_line'] = $purchase_order_line['No'];
					$inb_lines = $this->db->query('SELECT * FROM inbound_line WHERE inbound = ? AND ass_line=0 ORDER BY line DESC', $inbound['No'])->fetchAll();
					if (count($inb_lines)==0) {
						$inbound_line['line'] = 10000;
					} else {
						$line = $inb_lines[0]['line'];
						$inbound_line['line'] = (floor($line/10000)+1)*10000;
					}
					try {
						$inbound_line_table->insert($inbound_line);
						$inbound_line['No'] = $this->db->lastInsertId();
						$this->logger->info('Inbound Line wurde hinzugefügt mit No: '.$inbound_line['No'].' und Line: '.$inbound_line['line']);
					} catch (Exception $e) {
						$errors['all'] = 'Wareneingangszeile konnte nicht hinzugefügt werden! '.$e->getMessage();
						$this->logger->info('Fehler bei Inbound Line: '.$e->getMessage());
					}
				} else {
					try {
						$inbound_line_table->update($inbound_line, $inbound_line['No']);
						$this->logger->info('Inbound Line wurde gespeichert!');
					} catch (Exception $e) {
						$errors['all'] = 'Wareneingangszeile konnte nicht gespeichert werden! '.$e->getMessage();
						$this->logger->info('Wareneingangszeile konnte nicht gespeichert werden! '.$e->getMessage());
					}
				}
			// Movement speichern
				// Eingang als Bewegung speichern
				$movement['No'] = 0;
				$movement['movement'] = 1;
				$movement['date'] = $inbound['arrival'];
				$movement['trading_units'] = $inbound_line['trading_units'];
				$movement['packing_units'] = $inbound_line['packing_units'];
				$movement['trading_units_production'] = $inbound_line['trading_units'];
				$movement['packing_units_production'] = $inbound_line['packing_units'];
				$movement['status'] = 3;
				$movement['type'] = 0;
				$movement['stock_location'] = $inbound['stock_location'];
				$movement['remarks'] = '';
				$movement['in_order_line'] = $purchase_order_line['No'];
				$movement['inbound_line'] = $inbound_line['No'];
				$this->logger->info('Movement: '.print_r($movement, true));
				try {
					$movement_table->insert($movement);
					$movement['No'] = $this->db->lastInsertId();
					$this->logger->info('Bewegung wurde gespeichert mit No: '.$movement['No']);
				} catch (Exception $e) {
					$this->logger->info('Lagerbewegung wurde nicht gespeichert! '.$e->getMessage());
					$errors['all'] = 'Lagerbewegung wurde nicht gespeichert! '.$e->getMessage();
				}
				// Bilder und Anhänge speichern
				$pictures = $_FILES['picture'];
				$attachments = $_FILES['attachment'];
				// Bilder speichern
				$destination = realpath($this->config->upload->quality->pictures);
				$pic_count = $this->db->query('SELECT COUNT(inbound_line) as cnt_att FROM attachment WHERE inbound_line = ? AND type = 1', $inbound_line['No'])->fetchAll()[0]['cnt_att'];
				foreach ($pictures['error'] as $key => $error) {
					try {
						if ($error==0) {
							$pic_count++;
							preg_match('#\.([a-z0-9]+)$#i', basename($pictures['name'][$key]), $tmp);
							$sufix = $tmp[1];
							$file_name = 'pic_'.$inbound['position'].'_'.$inbound_line['No'].'_'.$pic_count.($sufix!='' ? '.'.$sufix : '');
							$fullname = $destination.'/'.$file_name;
							$this->logger->info('Destination: '.$fullname);
							$this->logger->info('Temp-Name: '.$pictures['tmp_name'][$key]);
							if (!move_uploaded_file($pictures['tmp_name'][$key], $fullname)) 
								 throw new Exception('Fehler bei Dateiupload');
							$attachment['inbound_line'] = $inbound_line['No'];
							$attachment['path'] = $file_name;
							$attachment['type'] = 1;
							$attachment_table->insert($attachment);
							$this->logger->info('Attachment: '.print_r($attachment, true));
						}
					} catch (Exception $e) {
						$errors['upload'] = 'Bilder konnten nicht gespeichert werden';
						$this->logger->err('Bilder wurden nicht gespeichert: '.$e->getMessage());
					}
				}
				$destination = realpath($this->config->upload->quality->attachments);
				$att_count = $this->db->query('SELECT COUNT(inbound_line) AS cnt_att FROM attachment WHERE inbound_line = ? AND type = 1', $inbound_line['No'])->fetchAll()[0]['cnt_att'];
				foreach ($attachments['error'] as $key => $error) {
					try {
						if ($error==0) {
							preg_match('#\.([a-z0-9]+)$#i', basename($attachments['name'][$key]), $tmp);
							$sufix = $tmp[1];
							$file_name = 'pic_'.$inbound['position'].'_'.$inbound_line['No'].'_'.$pic_count.($sufix!='' ? '.'.$sufix : '');
							$fullname = $destination.'/'.$file_name;
							$this->logger->info('Destination: '.$fullname);
							$this->logger->info('Temp-Name: '.$attachments['tmp_name'][$key]);
							if (!move_uploaded_file($attachments['tmp_name'][$key], $fullname)) 
								 throw new Exception('Felhler bei Dateiupload');
							$attachment['inbound_line'] = $inbound_line['No'];
							$attachment['path'] = $file_name;
							$attachment['type'] = 2;
							$attachment_table->insert($attachment);
							$this->logger->info('Attachment: '.print_r($attachment, true));
						}
					} catch (Exception $e) {
						$errors['upload'] = 'Bilder konnten nicht gespeichert werden';
						$this->logger->err('Bilder wurden nicht gespeichert: '.$e->getMessage());
					}
				}
				$this->logger->info('Anzahl Qualitätsmerkmale: '.count($quality_checkpoints));
				try {
					$this->logger->info('Bilder: '.print_r($pictures, true));
				} catch (Exception $e) {
					$errors['files'] = 'Die Bilder konnten nicht gespeichert werden!';
					$this->logger->info('Bilderupload fehlgeschlagen! '.$e->getMessage());
				}
				$quality_checkpoints = $this->db->query("SELECT * FROM quality_checkpoint")->fetchAll();
				foreach ($quality_checkpoints as $quality_checkpoint) {
					$qindex = 'operator_'.$quality_checkpoint['No'];
					$this->logger->info('QIndex: '.print_r($qindex, true));
					if (isset($_POST[$qindex])) {
						$qcheckpoint_inb_lines = $this->db->query("SELECT * FROM qcheckpoint_inb_line WHERE quality_checkpoint=? AND inbound_line=?", 
														array($quality_checkpoint['qck_no'], $inbound_line['No']))->fetchAll();
						if (count($qcheckpoint_inb_lines)==0) {
							$qcheckpoint_inb_line['no']=0;
							$qcheckpoint_inb_line['quality_checkpoint'] = $quality_checkpoint['No'];
							$qcheckpoint_inb_line['inbound_line'] = $inbound_line['No'];
							if ($_POST['operator_'.$quality_checkpoint['No']] == 2) {
								$qcheckpoint_inb_line['value'] = ($_POST['res_'.$quality_checkpoint['No']]==true);
								$qcheckpoint_inb_line['base'] = 0;
							} else {
								($_POST['base_'.$quality_checkpoint['No']]<>0) ? $qcheckpoint_inb_line['base'] = $_POST['base_'.$quality_checkpoint['No']] : $qcheckpoint_inb_line['base'] = $inbound_line['items_checked'];
								$qcheckpoint_inb_line['value'] = $_POST['res_'.$quality_checkpoint['No']];
							}	
							$qcheckpoint_inb_line['max_good'] = $_POST['max_good_'.$quality_checkpoint['No']];
							$qcheckpoint_inb_line['max_regular'] = $_POST['max_regular_'.$quality_checkpoint['No']];
							$this->logger->info('Qcheck_inb_line: '.print_r($qcheckpoint_inb_line, true));
							try {
								$qck_inb_line_table->insert($qcheckpoint_inb_line);
								$qcheckpoint_inb_line['no']=$this->db->lastInsertId();
								$this->logger->info('Qualitätsmerkmal wurde hinzugefügt');
							} catch (Exception $e) {
								$errors['all'] = 'Qualitätsmerkmal konnte nicht hinzugefügt werden! '.$e->getMessage();
								$this->logger->info('Fehler bei Qualitätsmerkmal: '.$e->getMessage());
							}
						} else {
							$qcheckpoint_inb_line = $qcheckpoint_inb_lines[0];
							$qcheckpoint_inb_line['value'] = $_POST[$qindex];
							$qcheckpoint_inb_line['max_good'] = $_POST['max_good_'.$quality_checkpoint['No']];
							$qcheckpoint_inb_line['max_regular'] = $_POST['max_regular_'.$quality_checkpoint['No']];
							$qcheckpoint_inb_line['base'] = $_POST['base_'.$quality_checkpoint['No']];
							try {
								$qck_inb_line_table->update($qcheckpoint_inb_line, $qcheckpoint_inb_line['No']);
							} catch (Exception $e) {
								$errors['all'] = 'Qualitätsmerkmal konnte nicht gespeichert werden! '.$e->getMessage();
							}
						}
					}
				}
				if (count($errors)==0) {
					$this->db->commit();
					$this->mailWE($inbound_line['No']);
					$this->logger->info('Datenbank Committed');
					$this->_redirect('/inbound/editquality/stored/1');
				} else {
					$this->db->rollBack();
					$this->logger->info('Datenbank rolled back');
				}
			}
			$qcheckpoints = $this->db->query('SELECT * FROM v_qc_product WHERE type=0 AND UPPER(product) = UPPER("?")',$inbound_sheet['product'])->fetchAll();
			if (count($qcheckpoints)==0) {
				$qcheckpoints = $this->db->query('SELECT * FROM v_quality_checkpoint WHERE type=0 ORDER BY qchk_class_no')->fetchAll();
			}
			$qcheck_logs = $this->db->query('SELECT * FROM v_quality_checkpoint WHERE type=1 ORDER BY qchk_class_no')->fetchAll();
		
		} else {
			$date = new Zend_Date();
			$errors = array();
			if ($this->hasParam('stored')) $errors['stored'] = $this->getParam('stored');			
			$product = $this->db->query("SELECT * FROM v_product LIMIT 1")->fetchAll();
			$prod_no = $product[0]['No'];
			$inbound_sheet=array(
				'No'=>0,
				'inbound'=>0,
				'inb_line'=>0,
				'position'=>'',
				'v_delivery_note'=>'',
				'vendor'=>'',
				'purchase_order'=>'',
				'po_line' => 0,
				'po_line_line' => 0,
				'stock_location'=>1,
				'po_arrival_date'=>$date->get(Zend_Date::DATES),
				'po_arrival_time'=>$date->get(Zend_Date::TIME_SHORT),
				'inb_arrival_date'=>$date->get(Zend_Date::DATES),
				'inb_arrival_time'=>$date->get(Zend_Date::TIME_SHORT),
				'forwarder'=>'',
				'truck'=>'',
				'trailor'=>'',
				'container'=>'',
				'po_transport_temp_min'=>$product[0]['transport_temp_min'],
				'po_transport_temp_max'=>$product[0]['transport_temp_max'],
				'inb_transport_temp'=>0,
				'variant'=>'',
				'product'=>$product[0]['No'],
				't_packaging'=>'',
				'packaging'=>0,
				'origin'=>'DE',
				'label'=>0,
				'quality_class'=>1,
				'brand'=>0,
				'quality'=>1,
				'items'=>0,
				'weight_item'=>0,
				'po_lot'=>'',
				'po_barcode'=>'',
				'po_trading_units'=>0,
				'po_packing_units'=>0,
				'po_pallet'=>'',
				'po_pallets'=>0,
				'po_tu_pal'=>0,
				'inb_lot'=>'',
				'inb_barcode'=>'',
				'items_checked'=>0,
				'inb_trading_units'=>0,
				'inb_packing_units'=>0,
				'inb_pallet'=>'',
				'inb_pallets'=>0,
				'inb_tu_pal'=>0,
				'po_brix_min' => 0,
				'po_brix_max' => 0,
				'inb_brix' => 0,
				'inb_certificate'=>1,
				'remarks' => '',
				'checked_by' => '');
			if ($prod_no<>'') {
				$this->logger->info('product_no: '.print_r($prod_no, true));
				$qcheckpoints = $this->db->query('SELECT * FROM v_qc_product WHERE type=0 AND UPPER(product_no) = UPPER(?)',$prod_no)->fetchAll();
				$this->logger->info('QCheckpoints: '.print_r($qcheckpoints, true));
				if (count($qcheckpoints)==0) {
					$qcheckpoints = $this->db->query('SELECT * FROM v_quality_checkpoint WHERE type=0 ORDER BY qchk_class_no')->fetchAll();
				}
				foreach($qcheckpoints as $qcheckpoint) {
					$inbound_sheet['res_'.$qcheckpoint['qck_no']] = 0;
					$inbound_sheet['base_'.$qcheckpoint['qck_no']] = 0;
					$inbound_sheet['rp_'.$qcheckpoint['qck_no']] = $this->calcQResult($qcheckpoint, 0, 0)[0];
					$inbound_sheet['rl_'.$qcheckpoint['qck_no']] = $this->calcQResult($qcheckpoint, 0, 0)[1];
				}
				$qcheck_logs = $this->db->query('SELECT * FROM v_quality_checkpoint WHERE type=1 ORDER BY qchk_class_no')->fetchAll();
				foreach($qcheck_logs as $qcheck_log) {
					$inbound_sheet['res_'.$qcheck_log['qck_no']] = 0;
					$inbound_sheet['base_'.$qcheck_log['qck_no']] = 0;
					$inbound_sheet['rp_'.$qcheck_log['qck_no']] = $this->calcQResult($qcheck_log, 0, 0)[0];
					$inbound_sheet['rl_'.$qcheck_log['qck_no']] = $this->calcQResult($qcheck_log, 0, 0)[1];
				}				
			}
		}
		$this->logger->info('editquality aufgerufen mit Inbound_sheet: '.print_r($inbound_sheet, true));
		$params = $this->loadDependencies();	
		$params['data']=$inbound_sheet;
		$params['qcheckpoints'] = $qcheckpoints;
		$params['qcheck_logs'] = $qcheck_logs;
		$params['title']='Qualitätsbericht';
		$params['errors']=$errors;
		$this->view->params=$params;
	}
	
	public function reverseinboundlineAction()
	{
		$errors = array();
		if ($this->hasParam('No')) {
			$inb_line_table = New Application_Model_inboundlineModel();
			$movement_table = new Application_Model_MovementModel();
			$inbound_lines = $inb_line_table->find($this->getParam('No'));
			if ($inbound_lines->count()<>1) {
				$this->logger->err('Nummer der Lieferzeile nicht eindeutig! Storno kann nicht durchgeführt werden.');
				$errors['No'] = 'Nummer der Lieferzeile nicht eindeutig! Storno kann nicht durchgeführt werden.';
				$this->_redirect('inbound/index/error/'.$errors['No']);
			}
			$inbound_line = $inbound_lines->current();
			$stock = $this->db->query('SELECT SUM(trading_units) as trading_units, SUM(packing_units) as packing_units FROM movements WHERE inbound_line = ?', $inbound_line->No)->fetchAll()[0];
			if (($stock[trading_units]<>$inbound_line->trading_units) || ($stock['packing_units']<>$inbound_line->packing_units)) {
				$this->logger->err("Bestand auf Inbound_line {$inbound_line->No} ist nicht der Ursprungsbestand!");
				$errors['stock'] = "Bestand auf Inbound_line {$inbound_line->No} ist nicht der Ursprungsbestand!";
				$this->_redirect('inbound/index/error/'.$errors['stock']);
			}
			if ($inbound_line->reversed_to<>0) {
				$errors['reversed_to'] = 'Diese Zeile ist ein Storno oder wurde storniert!';
				$this->logger->info("Inbound_line {$inbound_line->No} ist ein Storno oder wurde storniert!");
				$this->_redirect('inbound/index/error/'.$errors['reversed_to']);
			}
			try {
				$this->db->beginTransaction();
				$inbound = $this->db->query('SELECT * FROM inbound WHERE No=?', $inbound_line->inbound)->fetchAll()[0];
				$reverse_inb_line = $inbound_line->toArray();
				$inb_lines = $this->db->query('SELECT * FROM inbound_line WHERE inbound = ? AND ass_line=0 ORDER BY line DESC', $inbound['No'])->fetchAll();
				if (count($inb_lines)==0) {
					$reverse_inb_line['line'] = 10000;
				} else {
					$line = $inb_lines[0]['line'];
					$reverse_inb_line['line'] = (floor($line/10000)+1)*10000;
				}
				unset($reverse_inb_line['No']);
				$reverse_inb_line['trading_units'] = -$inbound_line->trading_units;
				$reverse_inb_line['packing_units'] = -$inbound_line->packing_units;
				$reverse_inb_line['remarks'] = "Storno zu {$inbound['position']} Zeile {$inbound_line->line}";
				$reverse_inb_line['reversed_to'] = $inbound_line->No;
				$inb_line_table->insert($reverse_inb_line);
				$inbound_line->reversed_to = $this->db->lastInsertId();
				$inbound_line->save();
				// Storno als Bewegung speichern
				$movement['No'] = 0;
				$movement['movement'] = 1;
				$movement['date'] = date('Y-m-d H:i:s');
				$movement['trading_units'] = $reverse_inb_line['trading_units'];
				$movement['packing_units'] = $reverse_inb_line['packing_units'];
				$movement['trading_units_production'] = $reverse_inb_line['trading_units'];
				$movement['packing_units_production'] = $reverse_inb_line['packing_units'];
				$movement['status'] = 3;
				$movement['type'] = 0;
				$movement['stock_location'] = $inbound['stock_location'];
				$movement['remarks'] = "Storno zu {$inbound['position']} Zeile {$inbound_line->line}";
				$movement['in_order_line'] = 0;
				$movement['inbound_line'] = $inbound_line->No;
				$movement['outbound_line'] = $inbound_line->reversed_to;
				$movement_table->insert($movement);
				$this->logger->info("Storno von Inbound_line {$inbound_line->No} erfolgreich mit Reverse_inb_line {$inbound_line->reversed_to}");
				$this->db->commit();
			} catch (Exception $e) {
				$this->logger->err('Storno konnte nicht durchgeführt werden! '.$e->getMessage());
				$this->db->rollBack();
			}
			$this->_redirect('inbound/index');
		}
	}
	
	public function getlinesAction()
	{
		$where = array();
		$errors = array();
		if ($this->hasParam('No')) {
			$where['No']['value'] = $this->getParam('No');
			$where['No']['type'] = 'number';
		}
		if ($this->hasParam('inbound')) {
			$where['inbound']['value'] = $this->getParam('inbound');
			$where['inbound']['type'] = 'string';
		}
		if ($this->hasParam('position')) {
			$where['position']['value'] = $this->getParam('position');
			$where['position']['type'] = 'string';
		}
		if ($this->hasParam('variant')) {
			$where['variant']['value'] = $this->getParam('variant');
			$where['variant']['type'] = 'string';
		}
		if ($this->hasParam('product')) {
			$where['product']['value'] = $this->getParam('product');
			$where['product']['type'] = 'string';
		}
		$sqlStr = 'SELECT * FROM v_inb_line';
		$pNo = 0;
		foreach ($where as $key => $val) {
			($pNo==0) ? $sqlStr.=' WHERE ' : $sqlStr.=' AND ';
			$pNo++;
			$val['type']=='string' ? $sqlStr.= 'UPPER('.$key.') LIKE UPPER("%'.$val['value'].'%")' : $sqlStr.= $key.' = '.$val['value'];
		}
		try {
			$inbound_lines = $this->db->query($sqlStr)->fetchAll();
		} catch (Exception $e) {
			$errors['sql'] = $e->getMessage();
		}
		$this->view->errors = $errors;
		$this->view->results = $inbound_lines;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
	}
	
	public function getinboundsAction()
	{
		$params = array();
		$inbounds = array();
		$errors = array();
		($this->hasParam('page')) ? $page = $this->getParam('page') : $page = 1;
		$offset = ($page-1)*20;
		if ($this->hasParam('No')) {
			$params['No']['value'] = $this->getParam('No');
			$params['No']['type'] = 'number';
		}
		if ($this->hasParam('inbound')) {
			$params['inbound']['value'] = $this->getParam('inbound');
			$params['inbound']['type'] = 'number';
		}
		if ($this->hasParam('position')) {
			$params['position']['value'] = $this->getParam('position');
			$params['position']['type'] = 'number';
		}
		if ($this->hasParam('purchase_order')) {
			$params['purchase_order']['value'] = $this->getParam('purchase_order');
			$params['purchase_order']['type'] = 'string';
		}
		if ($this->hasParam('vendor_no')) {
			$params['vendor_no']['value'] = $this->getParam('vendor_no');
			$params['vendor_no']['type'] = 'string';
		}
		if ($this->hasParam('vendor_name')) {
			$params['vendor_name']['value'] = $this->getParam('vendor_name');
			$params['vendor_name']['type'] = 'string';
		}
		if ($this->hasParam('variety')) {
			$params['variety']['value'] = $this->getParam('variety');
			$params['variety']['type'] = 'string';
		}
		if ($this->hasParam('product')) {
			$params['product']['value'] = $this->getParam('product');
			$params['product']['type'] = 'string';
		}
		$whereClause = '';
		$pNo = 0;
		foreach ($params as $key => $val) {
			($pNo==0) ? $whereClause.=' WHERE ' : $whereClause.=' AND ';
			$pNo++;
			$val['type']=='string' ? $whereClause.= 'UPPER('.$key.') LIKE UPPER("%'.$val['value'].'%")' : $whereClause.= $key.' = '.$val['value'];
		}
		$this->logger->info('InboundController->getinboundsAction called with whereClause: '.$whereClause);
		try {
			$count_inb = $this->db->query("SELECT COUNT(DISTINCT inbound) AS CNT FROM v_inb_line".$whereClause)->fetchAll()[0]['CNT'];
			$pages = floor($count_inb/20)+1;
			$inbounds = $this->db->query("select * from v_inb_line".$whereClause." GROUP BY inbound ORDER BY inb_arrival DESC LIMIT ".$offset.", 20")->fetchAll();
		} catch (Exception $e) {
			$this->logger->err("Fehler bei Abfrage! ".$e->getMessage());
			$errors['all'] = 'Fehler bei Abfrage der Wareneingänge';
		}
		$this->view->errors = $errors;
		$this->view->results = $inbounds;
		$this->view->count = $count_inb;
		$this->view->page = $page;
		$layout = $this->_helper->layout();
		$layout->setLayout('xml_layout');
	}
	
	public function testmailAction()
	{
		$this->mailWE($this->getParam('No'));
	}
	
	private function mailWE($No)
	{
		$sent = false;
		$inbound_line = $this->db->query('SELECT * FROM v_inb_line WHERE No = ?', $No)->fetchAll();
		if (count($inbound_line)>0) {
			$emailText = "Hallo Kollegen vom Ein- und Verkauf.\n\n";
			$emailText.= "Ein Neuer Wareneingang wurde gebucht.\n\n";
			$emailText.= "Lieferant: {$inbound_line[0]['vendor_name']}\n";
			$emailText.= "Artikel: {$inbound_line[0]['variant_desc']}\n";
			$emailText.= "Eingangsmenge: {$inbound_line[0]['inb_trading_units']}\n\n";
			$emailText.= "Der Wareneingang wurde mit einem Score von {$inbound_line[0]['grade_weighted']} bewertet.\n\n";
			$emailText.= "Bitte schaue unter http://dispo.mvs.local/inbound/index/position/{$inbound_line[0]['position']}\n\n";
			$emailText.= "Mit freundlichen Gruessen\n\nWareneingang\n\n";
			$emailText.= "Marktvertrieb Schwerin GmbH";
			$mailconfig = $this->config->mailconfig->toArray();
			$transport = new Zend_Mail_Transport_SMTP($this->config->mailserver, $mailconfig);
			try {
				$mail = new Zend_Mail();
				$mail->setBodyText($emailText);
				$mail->setFrom('dispo@marktvertrieb.de', 'MVS-Dispo Wareneingang');
				$mail->addTo('vt-verkauf@marktvertrieb.de');
				$mail->setSubject("Wareneingang {$inbound_line[0]['position']} {$inbound_line[0]['vendor_name']} {$inbound_line[0]['origin']} {$inbound_line[0]['product_desc']} {$inbound_line[0]['items']}x{$inbound_line[0]['weight_item']}g");
				$mail->send($transport);
			} catch (Exception $e) {
				$this->logger->err('Email nicht versendet! '.$e->getMessage());
			}
		}
	}

}