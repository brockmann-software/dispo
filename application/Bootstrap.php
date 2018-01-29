<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initAutoload()
	{
		// add autoloader empty namespace
		$autoLoader = Zend_Loader_AutoLoader::getInstance();
		$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
		'basePath' 		=> APPLICATION_PATH,
		'namespace' 	=> '',
	));
	
	// return it so that it can be stored by the bootstrap
	return $autoLoader;
	}
	
	protected function _initConfig()
	{
		$config = new zend_config($this->getOptions(), false);
		Zend_Registry::set('config', $config);
		return $config;
	}
	
	protected function _initLogger()
	{
		$log_file = Zend_Registry::get('config')->logging->file;
		$realPath = realpath($log_file);
		$log = explode('.',$realPath);
		$test_file = $log[0].date('Ymd').'.'.$log[1];
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($test_file));
		Zend_Registry::set('logger', $logger);
		//$logger->info('Log File: '.print_r($log_file, true));
		//$logger->info('realPath: '.print_r($realPath, true));
		//$logger->info('Log Array: '.print_r($log, true));
		//$logger->info('Test File: '.print_r($test_file, true));
		return $logger;
	}
	
	protected function _initDatabase()
	{
	// Modell inizialisieren
		$config = Zend_Registry::get('config');
		$db = Zend_Db::factory($config->database->adapter, $config->database->params->toArray());
		Zend_Db_Table::setDefaultAdapter($db);
		Zend_Registry::set('db', $db);
		$db_btm = Zend_Db::factory($config->database2->adapter, $config->database2->params->toArray());
		Zend_Registry::set('db_btm', $db_btm);
		return $db;
	}
	
	protected function _initAuth()
	{
		$auth = Zend_Auth::getInstance();
		$storage = new Zend_Auth_Storage_Session();
		$auth->setStorage($storage);
		$db = Zend_Registry::get('db');
		$adapter = new Zend_Auth_Adapter_DbTable($db, 'user', 'username', 'password', 'MD5(?)');
		Zend_Registry::set('auth', $auth);
	}
	
	protected function _initTranslate()
	{
		$locale = new Zend_Locale();
		Zend_Registry::set('Zend_Locale', $locale);
		$translate = new Zend_Translate(array(
			'adapter'=>'My_Translate_Adapter_Mysql', 
			'content'=>'view_label', 
			'locale'=>'de',
			'columns'=>array('language', 'column_name', 'label'),
			'view'=>'production_index.phtml'));
		if (!$translate->isAvailable($locale->getLanguage())) $translate->setLocale('de'); else $translate->setLocale('auto');
		Zend_Registry::set('Zend_Translate', $translate);
	}
}
