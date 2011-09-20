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
	
}

global $main;
$data = preg_replace("/<LOGO>/si",  $this->show_logo(), $data);


$data = preg_replace("/<PAGEGEN>/si", $pagegen, $data); #Page Generation Time
