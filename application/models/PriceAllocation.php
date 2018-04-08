<?php
class Application_Model_PriceAllocation
{
	protected $db;
	protected $_priceAllocationTable;
	protected $_allocationServiceTable;
	
	protected $_priceAllocation;
	protected $_allocationServices;
	
	public function __construct($No=null)
	{
		$this->db = Zend_Registry::get('db');
		$this->_priceAllocationTable = new Application_Model_PriceAllocationModel();
		$this->_allocationServiceTable = new Application_Model_PriceAllocationServiceModel();
		
		if (($No==null) || ($No=='')	) {
			$this->_priceAllocation = $this->_priceAllocationTable->fetchNew();
		} else {
			$allocations = $this->_priceAllocationTable->find($No);
			if ($allocations->count()==0) throw new Exeption('Price_Allocation not found!', 'Application_Model_PriceAllocation');
			$this->_priceAllocation = $allocations->current();
		}
		if ($this->_priceAllocation->No!==null)
			$this->_allocationServices = $this->_allocationServiceTable->fetchAll(array('price_allocation = ?'=>$this->_priceAllocation->No));
		else 
			$this->_allocationServices = $this->_allocationServiceTable->fetchAll(array('price_allocation is null'));
			
	}
	
	public function save($price_allocation, $services)
	{
		$oldNo = $this->_priceAllocation->No;
		$this->_priceAllocation->No = $price_allocation['No'];
		$this->_priceAllocation->Allocation = $price_allocation['Allocation'];
		$this->_priceAllocation->Allocation_Desc = $price_allocation['Allocation_Desc'];
		$this->_priceAllocation->save();
		
		if (is_array($services)) {
			$allocationServices = array();
			foreach($this->_allocationServices as $allocationService) $allocationServices[]=$allocationService->service;
			$new_services = array_diff($services, $allocationServices);
			$del_services = array_diff($allocationServices, $services);
			foreach ($new_services as $service) {
				$this->_allocationServiceTable->insert(array('price_allocation'=>$this->_priceAllocation->No, 'service'=>$service));
			}
			foreach ($del_services as $service) {
				$allocationServices = $this->_allocationServiceTable->fetchAll(array('price_allocation = ?'=>$this->_priceAllocation->No, 'service = ?'=>$service));
				if ($allocationServices->count()>0)
					$allocationServices->current()->delete();
				else throw new Exception('Allocation_Service does not exist!');
			}
		}

	}
	
	public function getData()
	{
		return $this->_priceAllocation;
	}
	
	public function getServices()
	{
		return $this->db->query('SELECT * FROM v_allocation_service WHERE price_allocation_no = ?', $this->_priceAllocation->No)->fetchAll();
	}
}