<?php
class Hook {
	var $data = null;
	var $settings = array();
	
	function __construct($data = null) {
		if (isset($data)) {
			$this->data = $data;			
		}				
	}
	
	function getSettings() {
		$my_class = strtolower(get_class($this));
		list($hook, $module, $function) = explode('_', $my_class);
		if (isset($this->settings) && !empty($this->settings)) {
			return $this->settings;
		}
		
		//Loading hook settings
		$hook_settings_file = INCLUDES.'hooks/'.$module.'/'.$module.'.php';
		
		if (file_exists($hook_settings_file)) {
			require_once $hook_settings_file;			
			
			if (isset($hook_settings) && !empty($hook_settings[$module])) {				
				$this->settings = $hook_settings[$module];				
				return $this->settings;				
			}
		}
	}
}