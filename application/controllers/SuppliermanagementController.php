<?php
class SuppliermanagementController extends Zend_Controller_Action
{
	public function exportAction()
	{
		if ($this->getRequest()->isPost()) {
			$today = new Zend_Date();
			$period['start'] = new Zend_Date();
			$period['end'] = new Zend_Date();
			if ($this->hasParam('period_start')) {
				try { 
					$period['start']->set($this->getParam('period_start'), Zend_Date::DATES);
				} catch(Exception $e) {
					$period['start']->subYear(1);
				}
			} $period['start']->subYear(1);
			if ($this->hasParam('period_end')) {
				try {
					$period['end']->set($this->getParam('period_end', Zend_Date::DATES));
				} catch(Exception $e) {
				}
			}
			$this->_redirect('/index/index');
		}
	}
}