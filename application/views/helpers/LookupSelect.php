<?php
class Zend_View_Helper_LookupSelect extends Zend_View_Helper_FormSelect
{
	public function lookupSelect($name, $value, $dataColumn, $lookupSet, $lookupKey, $lookupValue, $params=null, $listsep=null)
	{
		$options = array();
		foreach ($lookupSet as $lookup) {
			$options[$lookup[$lookupKey]] = $lookup[$lookupValue];
		}
		$xhtml = parent::formSelect($name, $value, $params, $options, $listsep);
		return $xhtml;
	}
}