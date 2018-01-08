<?php
class Application_Model_Inboundline
{
	protected $data = array();
	
	protected $qc_logistic = array();
	
	protected $qc_quality = array();
	
	protected $qc_classes = array();
	
	protected $immages = array();
	
	protected $attachments = array();
	
	protected $db;
	
	private function calcQResult($qcheckpoint, $base, $result)
	{
		switch($qcheckpoint['operator']) {
			case 0 : $res_percent = sprintf('%01.1f', $result);
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
					$res_percent = sprintf('%01.1f%%', $res_percent);
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
	
	public function __construct($id, $language='')
	{
		if ($id==null || $id=='' || $id==0) {
			throw new Zend_Exception('Id muss Integer größer 0 sein!');
		} else {
			$this->db = Zend_Registry::get('db');
			if ($language<>'') {
				Zend_Registry::get('logger')->info('Sprache: ', print_r($language, true));
				$this->db->query('SET @lang = ?', $language);
			}
			$inbound_lines = $this->db->query('SELECT * FROM v_inb_line WHERE No = ?', $id)->fetchAll();
			if (count($inbound_lines)==0) throw new Zend_Exception('Keine Inbound Line gefunden');
			$this->data = $inbound_lines[0];
			$this->data['po_arrival_date'] = date('d.m.Y', strtotime($this->data['po_arrival']));
			$this->data['po_arrival_time'] = date('H:i', strtotime($this->data['po_arrival']));
			$this->data['inb_arrival_date'] = date('d.m.Y', strtotime($this->data['inb_arrival']));
			$this->data['inb_arrival_time'] = date('H:i', strtotime($this->data['inb_arrival']));
			
			$this->qc_logistic = $this->db->query('SELECT * FROM v_qc_inb_line WHERE inbound_line = ? and qc_type_no = 1', $this->data['No'])->fetchAll();
			$this->qc_quality = $this->db->query('SELECT * FROM v_qc_inb_line WHERE inbound_line = ? and qc_type_no = 0 ORDER BY qc_class_no, No', $this->data['No'])->fetchAll(); 
			$this->qc_classes = $this->db->query('SELECT * FROM qcheckpoint_class')->fetchAll();
			
			foreach ($this->qc_logistic as &$qc_inb_line_log) {
				$qc_inb_line_log['res_percent'] = $this->calcQResult($qc_inb_line_log, $qc_inb_line_log['base'], $qc_inb_line_log['value'])[0];
				$qc_inb_line_log['res_level'] = $this->calcQResult($qc_inb_line_log, $qc_inb_line_log['base'], $qc_inb_line_log['value'])[1];
			}
			foreach ($this->qc_quality as &$qc_inb_line_quality) {
				$qc_inb_line_quality['res_percent'] = $this->calcQResult($qc_inb_line_quality, $qc_inb_line_quality['base'], $qc_inb_line_quality['value'])[0];
				$qc_inb_line_quality['res_level'] = $this->calcQResult($qc_inb_line_quality, $qc_inb_line_quality['base'], $qc_inb_line_quality['value'])[1];
			}
			$this->images = $this->db->query('SELECT * FROM attachment WHERE inbound_line = ? and type = 1', $this->data['No'])->fetchAll();
			$this->attachments = $this->db->query('SELECT * from attachment WHERE inbound_line = ? and type = 2', $this->data['No'])->fetchAll();
		}
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function getQcLogistic()
	{
		return $this->qc_logistic;
	}
	
	public function getQcQuality()
	{
		return $this->qc_quality;
	}
	
	public function getClasses()
	{
		return $this->qc_classes;
	}
	
	public function getImages()
	{
		return $this->images;
	}
	
	public function getAttachments()
	{
		return $this->attachments;
	}
}