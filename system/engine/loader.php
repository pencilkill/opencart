<?php
final class Loader {
	protected $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}

	public function library($library) {
		$file = DIR_SYSTEM . 'library/' . $library . '.php';

		if (file_exists($file)) {
			include_once($file);
		} else {
			trigger_error('Error: Could not load library ' . $library . ' !');
			exit();
		}
	}

	public function helper($helper) {
		$file = DIR_SYSTEM . 'helper/' . $helper . '.php';

		if (file_exists($file)) {
			include_once($file);
		} else {
			trigger_error('Error: Could not load helper ' . $helper . ' !');
			exit();
		}
	}

	public function ext($ext) {
		$file = DIR_EXT . '/' . $ext . '.php';

		if (file_exists($file)) {
			include_once($file);
		} else {
			trigger_error('Error: Could not load ext ' . $ext . ' !');
			exit();
		}
	}

	public function model($model) {
		$file  = DIR_APPLICATION . 'model/' . $model . '.php';

		// File can include any legal filename char while model classname chars not
		$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);	// Model classname char limit

		if (file_exists($file)) {
			include_once($file);

			// registry model
			$this->registry->set('model_' . str_replace('/', '_', $model), new $class($this->registry));	// Slash will be replaced with dash while others will be ignore.
			// Now , $this->model_folder_modelFileName->mothod() is accessable !
		} else {
			trigger_error('Error: Could not load model ' . $model . ' !');
			exit();
		}
	}

	public function config($config) {
		$this->config->load($config);
	}

	public function language($language) {
		return $this->language->load($language);
	}
}
?>