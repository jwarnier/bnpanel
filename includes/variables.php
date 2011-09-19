<?php
/* For licensing terms, see /license.txt */

$navigation = $pagegen = $version = '';
 
//Define global, as we are going to pull up things from db
global $db, $starttime, $style, $main;

if (INSTALL == 1) {
	/*
	if ($db->config("show_page_gentime") == 1) {
		$mtime = explode(' ', microtime());
		$totaltime = $mtime[0] + $mtime[1] - $starttime;
		$gentime = substr($totaltime, 0, 5);
		$array['PAGEGEN'] = $gentime;
		$array['IP'] = getenv('REMOTE_ADDR');		
		$pagegen .= $style->replaceVar('tpl/footergen.tpl', $array);
		if ($db->config("show_footer")) {
			if(ini_get('safe_mode') or strpos(ini_get('disable_functions'), 'shell_exec') != false or stristr(PHP_OS, 'Win')) {
				$version[0] = "N/A";
			} else {
				$output = shell_exec('mysql -V');
				preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
			}
			global $style;
			$array2['OS'] 				= PHP_OS;
			$array2['SOFTWARE'] 		= $main->removeXSS($_SERVER["SERVER_SOFTWARE"]);
			$array2['PHP_VERSION'] 		= phpversion();
			$array2['MYSQL_VERSION'] 	= $version[0];
			$array2["SERVER"] 			= $main->removeXSS($_SERVER["HTTP_HOST"]);
			$array['TITLE'] 			= $style->replaceVar('tpl/aserverstatus.tpl',$array2);
			$pagegen .= $style->replaceVar('tpl/footerdebug.tpl',$array);
		}
	}*/
	 
	if ($db->config("show_version_id") == 1) {
	 	$version = $db->config("version");
	}

	
}



if ($main->getCurrentUserId()) {	
} else {	
	$data = preg_replace("/<LOGIN_TPL>/si",'<div id="login_form" title="Login">'.$style->replaceVar("tpl/login/login_widget.tpl", array()).'</div>', $data);
}

global $main;
$current_token = $main->getToken();

$data = preg_replace("/<AJAX>/si", URL."includes/ajax.php?_get_token=".$current_token."&", $data);






$data = preg_replace("/<MENU>/si", $navigation, $data);

$data = preg_replace("/<LOGO>/si",  $this->show_logo(), $data);
$data = preg_replace("/<LOGIN>/si", $this->show_login_link(), $data);

$data = preg_replace("/<PAGEGEN>/si", $pagegen, $data); #Page Generation Time

$error_messages = $main->errors();

if (!empty($error_messages)) {
	$data = preg_replace("/<ERRORS>/si", '<div class="alert-message info">'.$error_messages.'</div><div style="clear:both"></div>', $data);	
}
//$data = preg_replace("/%INFO%/si", INFO, $data);