<?php
class Application_Model_Label
{
	protected $_labelTable;
	protected $_labelCountryTable;
	protected $_labelProductTable;
	
	protected $_label;
	protected $_labelCountries;
	protected $_labelProducts;
	
	public function __construct($No=null)
	{
		$this->_labelTable = new Application_Model_LabelTable();
		$this->_labelCountryTable = new Application_Model_LabelCountryTable();
		$this->_labelProductTable = new Application_Model_LabelProductTable();
		
		if (($No==null) || ($No=='') || ($No==0)) {
			$this->_label = $this->_labelTable->fetchNew();
		} else {
			$labels = $this->_labelTable->find($No);
			if ($labels->count()==0) throw new Exeption('Label not found!', 'Application_Model_Label');
			$this->_label = $labels->current();
		}
		if ($this->_label->No!==null) {
			$this->_labelCountries = $this->_labelCountryTable->fetchAll(array('label = ?'=>$this->_label->No));
			$this->_labelProducts = $this->_labelProductTable->fetchAll(array('label = ?'=>$this->_label->No));
		} else {
			$this->_labelCountries = $this->_labelCountryTable->fetchAll(array('label = ?'=>0));
			$this->_labelProducts = $this->_labelProductTable->fetchAll(array('label = ?'=>0));
		}
			
	}
	
	public function save($label, $countries, $products)
	{
		$this->_label->No = $label['No'];
		$this->_label->label = $label['label'];
		$this->_label->label_short = $label['label_short'];
		$this->_label->save();
		Zend_Registry::get('logger')->info('Label: '.print_r($this->_label->toArray(), true));
/*		
		if (is_array($countries)) {
			foreach($this->_labelCountries as $labelCountry) $labelCountries[]=$labelCountry->country;
			$new_countries = array_diff($countries, $labelCountries);
			$del_countries = array_diff($labelCountries, $countries);
			foreach ($new_countries as $country) {
				$this->LabelCountryTable->insert(array('label'=>$this->_label->No, 'country'=>$country);
			}
			foreach (del_countries as $country) {
				$labelCountries = $this->_labelCountryTable->fetchAll('label = ? AND country = ?', array($this->_label->No, $country));
				if ($labelCountries->count()>0)
					$labelCountries->current()->delete();
				else throw new Exception('Label_Country does not exist!');
			}
		}

		if (is_array($products)) {
			foreach($this->_labelProducts as $labelProduct) $labelProducts[]=$labelProduct->product;
			$new_products = array_diff($products, $labelProducts);
			$del_products = array_diff($labelProducts, $products);
			foreach ($new_products as $product) {
				$this->LabelProductTable->insert(array('label'=>$this->_label->No, 'product'=>$product);
			}
			foreach (del_products as $product) {
				$labelProducts = $this->_labelProductTable->fetchAll('label = ? AND product = ?', array($this->_label->No, $product));
				if ($labelProducts->count()>0)
					$labelProducts->current()->delete();
				else throw new Exception('Label_Product does not exist!');
			}
		}
*/
	}
	
	public function getData()
	{
		return $this->_label;
	}
}