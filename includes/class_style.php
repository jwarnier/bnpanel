<?php
/* For licensing terms, see /license.txt */

class style {

	# Start the functions #

	private function error($name, $template, $func) { #Shows a SQL error from main class
		if (INSTALL) {
			$error['Error'] = $name;
			$error['Function'] = $func;
			$error['Template'] = $template;
			global $main;
			$main->error($error);
		}
	}

	private function getFile($name, $prepare = 1, $override = 0) { # Returns the content of a file		
		$link = LINK ."../themes/". THEME . "/" . $name;
		if (!file_exists($link) || $override != 0) {
			$link = LINK . $name;
		}
		if (!file_exists($link) && INSTALL) {
			$error['Error'] = "File doesn't exist!";
			$error['Path'] = $link;			
		} else {
			if ($prepare) {
				return $this->prepare(file_get_contents($link));
			} else {
				return file_get_contents($link);
			}
		}
	}

	public function prepare($data) { # Returns the content with the THT variables replaced
		include LINK . "variables.php";
		return $data;
	}
	/*
	private function prepareCSS($data) { # Returns the CSS with all tags removed
		include LINK . "css_variables.php";
		return $data;
	}
*/
	public function get($template) { # Fetch a template
		return $this->getFile($template);
	}
	
	public function show_logo() {
		$link = URL."themes/". THEME . "/images/logo.png";
		return '<img src="'.$link.'" />';
	}
	
	public function show_login_link() {
		global $main;
		$user_info = $main->getCurrentUserInfo();
		
		$link = '';
		if (!empty($user_info)) {
			$link = '<li><a href="'.URL.'client">'.$user_info['user'].'</a></li><li><a href="'.URL.'client/?page=logout">Logout</a></li>';
		} else {
			$link = '<li><a href="#" onclick="showLogin();">Sign in</a></li>';
		}		
		return $link;
	}
	

	public function css() { # Fetches the CSS and prepares it
        global $db;                
		$link = URL."themes/". THEME . "/style.css";
		
		//Including 960 css		
		/*$css  = '<link rel="stylesheet" href="'.URL.'includes/css/960/reset.css" type="text/css" />';
		$css .= '<link rel="stylesheet" href="'.URL.'includes/css/960/text.css" type="text/css" />';
		$css .= '<link rel="stylesheet" href="'.URL.'includes/css/960/960_12_col.css" type="text/css" />';
		*/
		//Including bootstrap
		$css = '<link rel="stylesheet" href="'.URL.'includes/css/bootstrap/bootstrap-1.0.0.css" type="text/css" />';
				
		$css .= '<link rel="stylesheet" type="text/css" href="'.$link.'"/>';
		
		
		if (FOLDER != "install" && FOLDER != "includes") {
	        $css .= '<link rel="stylesheet" href="'.URL.'includes/css/'.$db->config('ui-theme').'/jquery-ui.css" type="text/css" />';
		}
		return $css;
	}

	public function replaceVar($template, $array = array(), $style = 0) { #Fetches a template then replaces all the variables in it with that key
		$data = $this->getFile($template, 0, $style);
		
		if (!empty($array)) {
			foreach($array as $key => $value) {				
				$data = preg_replace("/%". $key ."%/si", $value, $data);				
			}	
			
			//Commented lines are the traduction like Chamilo
			
			//include '/var/www/bnpanel/locale/es_ES/LC_MESSAGES/main.php';			
			$generate_cache = false;					 		
			preg_match_all("/_{.*?}/", $data, $output);
			//preg_match_all("/_\(.*?\)/", $data, $output);				
			/**
			 * 
			 * @todo 
			 * See this task  https://task.beeznest.net/issues/1464 for more information about the status of the translation package
			 * 
			 */			 
			if ($generate_cache) {
				if (is_dir(MAIN.'locale/cache') && is_writable(MAIN.'locale/cache')) {		
					$cache = MAIN.'locale/cache/'.basename($template).'.php';
					$handle = fopen($cache,'w');
				} else {
					$generate_cache = false;
				}
			}
						   		
			if (!empty($output)) {
				foreach($output as $out) {
					if (!empty($out)) {
						if ($generate_cache) {
							fputs($handle,"<?php \n");
						}
						foreach($out as $item) {							
							if (!empty($item)) {							
								$item_original = $item;
								$item = str_replace(array('_{','}'), '', $item);
								$save = "gettext('$item');\n";
								if ($generate_cache) {								
									fputs($handle, $save);
								}								
								//$item = str_replace(array('_(',')'), '', $item);
								//if (isset($$item)) {
								if (isset($item)) {
									$item_to_prereg = preg_quote($item);
									//This is the piece of code that do the replace to use gettext			
									$data = preg_replace("/_\{$item_to_prereg\}/si", gettext($item), $data);
									//$data = preg_replace("/_\($item\)/si", gettext($item), $data);
								}				
							}
						}
						if ($generate_cache) {
							fputs($handle,'?>');
						}
					}			
				}
			}			
			if ($generate_cache) {
				fclose($handle) or die ("Error Closing File!");
			}			
		}
		
		return $data;
	}

	public function javascript() { # Returns the HTML code for the header that includes all the JS in the javascript folder
		$folder = LINK ."javascript/";
		$html = "<script type=\"text/javascript\" src='".URL."includes/javascript/jquery.js'></script>\n";
		$html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/jquery-ui.js'></script>\n";
		$html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/misc.js'></script>\n";
		$html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/slide.js'></script>\n";
		$html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/ajax.js'></script>\n";		
		/*if ($handle = opendir($folder)) { # Open the folder
			while (false !== ($file = readdir($handle))) { # Read the files
				if($file != "." && $file != ".." && $file != "jquery.js" && $file != "simpletip.js") { # Check aren't these names
					$base = explode(".", $file); # Explode the file name, for checking
					if($base[1] == "js") { # Is it a JS?
						$html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/{$file}'></script>\n"; # Creates the HTML
					}
				}
			}
		}*/		
        $html .= "<script type=\"text/javascript\" src='".URL."includes/tiny_mce/tiny_mce.js'></script>";
		//closedir($handle); #Close the folder
		return $html;
	}

    public function notice($good, $message) {
        if($good) {
            //Cool! Everything's OK.
            $color = "green";
        }
        else {
            //Oh no! It's a bad message!
            $color = "red";
        }
        $notice = '<strong><em style="color: '. $color .';">';
        $notice .= $message;
        $notice .= '</em></strong>';
        return $notice;
    }
    
    public function showMessage($message, $type = 'info',$allow_html = true) {
		echo $this->returnMessage($message, $type, $allow_html);
    }
    
    public function returnMessage($message, $type = 'info', $allow_html = true) {
    	global $main;
    	if (!empty($type) && in_array($type, array('info', 'warning', 'error', 'success'))) {
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
    	} else {
    		$type = 'info';
    	}    	
    	return $html;
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