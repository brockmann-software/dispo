<?php
class Zend_View_Helper_NTMSelect extends Zend_View_Helper_FormSelect
{
	public function NTMSelect($name, $dataSet, $dataKey, $dataValue, $lookupSet, $lookupKey, $lookupValue, $params=null, $listsep=null)
	{
		$dataOptions = array();
		$lookupOptions = array();
		foreach ($dataSet as $data) $dataOptions[$data[$dataKey]] = $data[$dataValue];
		foreach ($lookupSet as $lookup) $lookupOptions[$lookup[$lookupKey]] = $lookup[$lookupValue];
		$lookupDiv = array_diff($lookupOptions, $dataOptions);
		$xhtml = "<table style=\"display:block;\"><tr><td id=\"left\">\n";
		$xhtml.= parent::formSelect($name.'[]', '', array('id'=>$name.'_data', 'size'=>'5'), $dataOptions, $listsep);
		$xhtml.= "</td>\n";
		$xhtml.= "<td id=\"middle\"><button type=\"button\" name=\"btnAddProduct\" onclick=\"addProduct('{$name}_data', '{$name}_lookup')\"><<</button><br />\n";
		$xhtml.= "<button type=\"button\" name=\"btnDelProduct\" onclick=\"deleteProduct('{$name}_data', '{$name}_lookup')\">>></button><br />\n";
		$xhtml.= "</td><td id=\"right\">\n";
		$xhtml.= parent::formSelect($name.'_lookup', '', array('id'=>$name.'_lookup', 'size'=>'5', 'multiple'=>'multiple'), $lookupDiv, $listsep);
		$xhtml.= "</td></tr></table>";
		return $xhtml;
	}
}