<?php
class Application_Model_PurchaseOrder
{
	protected $db;
	protected $purchaseOrderTable;
	protected $poLineTable;
	protected $vendorTable;
	
	protected $purchaseOrder = null;
	protected $poLines = null;
	protected $vendor = null;
	
	public function __construct($No=null)
	{
		$this->db = Zend_Registry::get('db');
		$this->purchaseOrderTable = new Application_Model_PurchaseorderModel();
		$this->poLineTable = new Application_Model_PurchaseorderlineModel();
		$this->vendorTable = new Application_Model_VendorModel();
		if ($No!==null) {
			$purchaseOrders = $this->purchaseOrderTable->find($No);
			if ($purchaseOrders->count()>0) {
				$this->purchaseOrder = $purchaseOrders->current();
				$this->poLines = $this->db->query('SELECT * FROM v_po_line WHERE purchase_order = ?', $this->purchaseOrder->No);
				$vendors = $this->vendorTable->find($this->purchaseOrder->vendor);
				if ($vendors->count()>0) $this->vendor = $vendors->current();
			} else throw new Exception('Purchase Order not found!');
		} else {
			$this->purchaseOrder = $this->purchaseOrderTable->createRow();
			$this->purchaseOrder->No = '';
			$this->purchaseOrder->stock_location = 1;
			$this->purchaseOrder->departure = date('Y-m-d H:i:s', time());
			$this->purchaseOrder->arrival =  date('Y-m-d H:i:s', time());
			$this->vendor = $this->vendorTable->createRow();
		}
	}
	
	public function getPO()
	{
		return $this->purchaseOrder;
	}
	
	public function getLines()
	{
		return $this->poLines;
	}
	
	public function getVendor()
	{
		return ($this->vendor) ? $this->vendor : $this->vendorTable->createRow();
	}
	
	public function update($purchase_order)
	{
		$key = $this->purchaseOrder->No;
		if ($this->purchaseOrder->No=='') {
			$this->purchaseOrder->insert($purchase_order);
			$key = $this->db->lastInsertId();
		} else {
			$this->purchaseOrderTable->update($purchase_order, array('No=?'=>$key));
		}
		$this->purchaseOrder = $this->purchaseOrderTable->find($key)->current();
		$this->poLines = $this->db->query('SELECT * FROM v_po_line WHERE purchase_order = ?', $this->purchaseOrder->No);
		$vendors = $this->vendorTable->find($this->purchaseOrder->vendor);
		if ($vendors->count()>0) $this->vendor = $vendors->current(); else throw new Exception('Kein gültiger Lieferant eingetragen!');
	}
}