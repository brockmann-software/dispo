<?php
use Zend/Form/Form
class Application_Form_Product_FormEdit extends Form
{
	public function __construct($name = null)
	{
		parrent::__construct($name);
		
		$this->setAtribute('method', 'post');
		$this->add(array(
			'name' => 'No',
			'attributes' => array(
				'type' => 'hidden'
			),
		));
		$this->add(array(
			'name' => 'product',
			'attributes' => array(
				'type' => 'text'
			),
			options => array(
				'label' => 'Produkt',
			),
		)),
		$this->add(array(
			'type' => 'Zend\Form\Element\Select',
			'name' => 'product_group',
			'options' => array(
				'0' => 'Bitte eine Gruppe wählen',
				'1' => 'Beerenfrüchte',
				'2' => 'Erdbeeren'
			),
		));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Speichern'
				'id' => 'submitbutton'
			)
		));
	}
}