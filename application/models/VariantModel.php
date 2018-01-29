<?php
class Application_Model_VariantModel extends Zend_Db_Table
{
	protected $_name = 'variant';
	protected $_primary = 'No';
	
	protected $_referenceMap = array(
			'product' => array(
						'columns' => 		array('product'),
						'refTableClass' => 	'Application_Model_ProduktModel',
						'refColums' =>		array('No')),
			'origin' =>	array(
						'columns' => 		array('origin'),
						'refTableClass' => 	'Application_Model_CountryModel',
						'refColums' =>		array('Id')),
			'packaging' => array(
						'columns' => 		array('packaging'),
						'refTableClass' => 	'Application_Model_PackagingModel',
						'refColums' =>		array('No')),
			'label' => array(
						'label' => 		array('label'),
						'refTableClass' => 	'Application_Model_LabelTable',
						'refColums' =>		array('No')),
			't_apckaging' => array(
						'columns' => 		array('t-packaging'),
						'refTableClass' => 	'Application_Model_TPackagingModel',
						'refColums' =>		array('No')),
			'quality_class' => array(
						'columns' => 		array('quality_class'),
						'refTableClass' => 	'Application_Model_QualityClassModel',
						'refColums' =>		array('No')),
			'brand' => array(
						'columns' => 		array('brand'),
						'refTableClass' => 	'Application_Model_BrandModel',
						'refColums' =>		array('No')),
			'quality' => array(
						'columns' => 		array('quality'),
						'refTableClass' => 	'Application_Model_QualityModel',
						'refColums' =>		array('No')),
			'variety' => array(
						'columns' => 		array('variety'),
						'refTableClass' => 	'Application_Model_VarietyModel',
						'refColums' =>		array('No')),
			'attribute' => array(
						'columns' => 		array('attribute'),
						'refTableClass' => 	'Application_Model_ProduktModel',
						'refColums' =>		array('No'))
		);
		
	public function buildNo(array $data)
	{
		if (isset($data['product'])) $variant['product'] = $data['product']; else throw new Exception('field product missing');
		if (isset($data['origin'])) $variant['origin'] = $data['origin']; else throw new Exception('field origin missing');
		if (isset($data['items'])) $variant['items'] = $data['items']; else throw new Exception('field items missing');
		if (isset($data['weight_item'])) $variant['weight_item'] = $data['weight_item']; else throw new Exception('field weight_item missing');
		if (isset($data['t_packaging'])) $variant['t_packaging'] = $data['t_packaging']; else throw new Exception('field t_packaging missing');
		if (isset($data['packaging'])) $variant['packaging'] = $data['packaging']; else throw new Exception('field packaging missing');
		if (isset($data['label'])) $variant['label'] = $data['label']; else throw new Exception('field label missing');
		if (isset($data['quality'])) $variant['quality'] = $data['quality']; else throw new Exception('field quality missing');
		if (isset($data['quality_class'])) $variant['quality_class'] = $data['quality_class']; else throw new Exception('field quality_class missing');
		if (isset($data['brand'])) $variant['brand'] = $data['brand']; else throw new Exception('field brand missing');
		$variant['No'] = $variant['product']
						.$variant['origin']
						.$variant['	items']
						.$variant['weight_item']
						.$variant['t_packaging']
						.$variant['packaging']
						.$variant['label']
						.$variant['quality']
						.$variant['quality_class']
						.$variant['brand'];
		if (isset($data['variety']) && $data['variety']<>0 && $data['variety']<>'') $variant['No'].= $data['variety'];
		if (isset($data['attribute']) && $data['attribute']<>0 && $data['attribute']<>'') $variant['No'].= $data['attribute'];
		return $variant;
	}
}