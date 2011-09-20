<?php
/* For licensing terms, see /license.txt */

function do_translation($params, $content, $smarty, &$repeat, $template = null) {
	if (isset($content)) {
		//$lang = $params["lang"];
		// do some translation with $content
		return gettext($content);
	}
}

class style extends Smarty {
	
	var $messages = array();
	
	function __construct() {
		
		parent::__construct();
				
		$this->setPluginsDir(INCLUDES.'smarty/plugins/');
		$this->setCacheDir(CACHE_PATH);
		$this->setCompileDir(CACHE_PATH);
		$this->setTemplateDir(INCLUDES.'tpl/');
		$this->setConfigDir(CACHE_PATH);		
		
		$this->debugging 		= false;		
		$this->caching 			= false;
		$this->cache_lifetime 	= Smarty::CACHING_OFF; // no caching		
		$this->assign('app_name', NAME);
		$sub = defined('SUB') ? ' - '.SUB : '';		
		
		$this->assign('app_title', NAME . " - " . PAGE.$sub);
		$this->registerPlugin('block', 't', 'do_translation');
		
		$this->set_header_parameters();
		$this->assign('messages', $this->messages);		
		
		//$this->testInstall($errors);
	}	
	
	function set_header_parameters() {
		global $main, $db;
		
		//JS
		$this->assign('javascript', $this->javascript());
		
		//CSS
		$this->assign('css', $this->css());
		
		//URLs
		$this->assign('url', URL);		
		$this->assign('IMG', URL . "themes/". THEME ."/images/");
		$this->assign('icon_dir', URL . "themes/icons/");
		$current_token = $main->getToken();		
		$this->assign('ajax', URL."includes/ajax.php?_get_token=".$current_token."&");
		
		$version = '';
		if ($db->config("show_version_id") == 1) {
			$version = $db->config("version");
		}
		
		$this->assign('copyright', 'Powered by <a href="http://www.beeznest.com" target="_blank">BNPanel</a> '. $version);
		
		
		
		if ($main->getCurrentUserId()) {			
		} else {						
		}	
		
		
		$this->assign('login', $this->show_login_link());
		
		
		$link = $this->fetch("login/login_widget.tpl");
		$this->assign('login_tpl', $link);		
		
		if (FOLDER != 'install') {
			$array = array();
			$navigation_list = $main->getMainNavigation();
			$navbits = '';
			foreach ($navigation_list as $nav_item) {
				if(!$db->config("show_acp_menu") && $nav_item['link'] == 'admin') {
					continue;
				} else {
					$array['ID'] = "nav_". $nav_item['link'];
					if (PAGE == $nav_item['visual']) {
						$array['ACTIVE'] = ' class="active" ';
					} else {
						$array['ACTIVE'] = '';
					}
					$array['LINK'] = $nav_item['link'];
					$array['ICON'] = $nav_item['icon'];
					$array['NAME'] = $nav_item['visual'];
		
					$tpl = "menu/top_link.tpl";
					$this->assign($array);
					$navbits .= $this->fetch($tpl);
				}
			}
		}		
		
		$array3 = array();
		$array3['nav'] = null;
		$array3['admin_nav'] = null;
		if (!empty($navbits)) {
			$array3['nav'] = $navbits;
		}
		if ($main->getCurrentStaffId()) {
			$array3['admin_nav'] = '<li><a href="<URL>admin">Administration</a></li>';
		}		
		//$tpl = "tpl/menu/top_main.tpl";
		$this->assign($array3);		
	}
	
	private function error($name, $template, $func) { #Shows a SQL error from main class
		if (INSTALL) {
			$error['Error'] = $name;
			$error['Function'] = $func;
			$error['Template'] = $template;
			global $main;
			$main->error($error);
		}
	}


	/*
	private function prepareCSS($data) { # Returns the CSS with all tags removed
		include LINK . "css_variables.php";
		return $data;
	}
*/

	public function show_logo() {
		$link = URL."themes/". THEME . "/images/logo.png";
		return '<img src="'.$link.'" />';
	}
	
	public function show_login_link() {
		global $main, $style;
		if (FOLDER == 'admin') {
			return '';
		}		
		
		$user_info = $main->getCurrentUserInfo();
		$link = '';
		if (INSTALL) {
			if (!empty($user_info)) {
				$link = '<ul class="nav secondary-nav"><li><a href="'.URL.'client">'.$user_info['user'].'</a></li>
						<li><a href="'.URL.'client/?page=logout">Logout</a></li></ul>';
			} else {				
				$link = '<form class="pull-right" action="" method="POST">
							<input class="input-small" name="user" type="text" placeholder="'.gettext('Username').'">
							<input class="input-small" name="pass" type="password" placeholder="'.gettext('Password').'">
							<button class="btn" type="submit">'.gettext('Sign in').'</button>
						</form>';
			}		
		}
		return $link;
	}
	

	public function css() {
        global $db;                
		$link = URL."themes/". THEME . "/style.css";		
		//Including bootstrap
		$css = '<link rel="stylesheet" href="'.URL.'includes/css/bootstrap/bootstrap.css" type="text/css" />';
				
		$css .= '<link rel="stylesheet" type="text/css" href="'.$link.'"/>';
		
		
		if (FOLDER != "install" && FOLDER != "includes") {
	        $css .= '<link rel="stylesheet" href="'.URL.'includes/css/'.$db->config('ui-theme').'/jquery-ui.css" type="text/css" />';
		}
		return $css;
	}

	public function replaceVar($template, $array = array(), $style = 0) {
		if (strpos($template, 'tpl/') === false) {			
		} else {
			$template = substr($template, 4, strlen($template));
		}		
		$this->assign($array);
		return $this->fetch($template);
	}

	/**
	 * Returns the HTML code for the header that includes all the JS in the javascript folder	 * 
	 */
	public function javascript() {
		$folder = INCLUDES ."javascript/";
		$html = "<script type=\"text/javascript\" src='".URL."includes/javascript/jquery.js'></script>\n";
		$html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/jquery-ui.js'></script>\n";
		$html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/misc.js'></script>\n";
		$html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/slide.js'></script>\n";	
        $html .= "<script type=\"text/javascript\" src='".URL."includes/tiny_mce/tiny_mce.js'></script>";
		return $html;
	}
    
    public function showMessage($message, $type = 'info', $allow_html = true) {
    	$this->messages[] = $this->returnMessage($message, $type, $allow_html);
    	$this->assign('messages', $this->messages);		
    }
    
    public function returnMessage($message, $type = 'info', $allow_html = true) {
    	global $main;
    	if (!in_array($type, array('info', 'warning', 'error', 'success'))) {    		
			$type = 'info';
		}
    	if ($type == 'error') {
    		$type = 'error_message';
		}
    	$html = '<div class="alert-message '.$type.'">';    		
		if ($allow_html) {   		
    		$html .= $message;
   		} else {
   			$html .= $main->removeXSS($message);    			
   		}
   		$html .= '</div>';  
   		
   		$this->messages[] = $html;
   		$this->assign('messages', $this->messages);
    	//return $html;
    }
    
    public function returnIcon($icon_name) {
    	if (!empty($icon_name)) {
    		$icon_file = URL.'themes/icons/'.$icon_name;    		
    		return '<img src="'.$icon_file.'">';
		}
    }
    
    public function showIcon($icon_name) {
    	echo $this->returnIcon($icon_name);
    } 
}