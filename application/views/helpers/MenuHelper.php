<?php
class Zend_View_Helper_MenuHelper
{
   public $view;
 
   public function menuHelper() {
      $config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/menu.xml', 'nav');
      $container = new Zend_Navigation($config);
 
      $this->view->navigation($container)->UlClass = "main-nav";
      return $this->view->navigation()->menu()->render();
   }
 
   public function setView(Zend_View_Interface $view) {
      $this->view = $view;
   }
}