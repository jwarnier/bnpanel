<?php
/* For licensing terms, see /license.txt */

/*
 * This is a pretty bad attempt at being secure. If you're having
 * problems with it, feel free to comment it out. But it was
 * better than what we had before and should work.
*/

/*
 * __FILE__ is an absolute path and we need to make it relative to
 * the document root. This file must be called directly and
 * directly only.
*/
if(strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
	$file = str_replace("\\", "/", __FILE__);
	$prepend = "/";
} else {
	$file = __FILE__;
	$prepend = "";
}
$compare = explode($_SERVER["DOCUMENT_ROOT"], $file);
if($prepend . $compare[1] !== $_SERVER["PHP_SELF"]) {
	die("You can only run the install from the <em>".__FILE__."</em> file.");
}


/*
 * Quick little function made to make generating a default site URL
 * easy. Hopefully this will assist alot of support topics regarding
 * bad site URLs, as the automatically generated ones should be correct.
*/
function generateSiteUrl() {
	global $main;
	$url = "";
	if(!empty($_SERVER["HTTPS"])) {
		$url .= "https://";
	}
	else {
		$url .= "http://";
	}
	$exploded = explode($_SERVER["DOCUMENT_ROOT"], realpath("../"));
	$url .= $main->removeXSS($_SERVER["HTTP_HOST"]). $exploded[1] . "/";
	return $url;
}

//INSTALL GLOBALS
define("CVER", "1.2.2");
define("NVER", "1.2.3");

define("LINK", "../includes/"); # Set link
include LINK."compiler.php"; # Get compiler

define("THEME", 'bnpanel'); # Set the theme
define("URL", "../"); # Set url to blank
define("NAME", "BNPanel");
define("PAGE", "Install");
define("SUB", "Choose Method");

$array['VERSION'] = NVER;
$array['ANYTHING'] = "";
$link = LINK."conf.inc.php";
$disable = false;

echo $style->get("header.tpl");

$values=array('install'=>'Install');  

if(INSTALL == 1) {
	$main->errors('The system has already been installed. If you want to re-install you should delete the conf.inc.php file. If you want to update just continue this procedure.');
	$values=array('upgrade'=>'Upgrade');
}
	
if(!file_exists($link)) {
	$array["ANYTHING"] = "Your $link file doesn't exist! Please create it!";
	$disable = true;
} elseif(!is_writable($link)) {
	$array['ANYTHING'] = "Your $link isn't writeable! Please CHMOD it to 666!";
	$disable = true;
}

if($disable) {
	echo '<script type="text/javascript">$(function(){$(".twobutton").attr("disabled", "true");$("#method").attr("disabled", "true");});</script>';
}
$array['GENERATED_URL'] 	= generateSiteUrl();
$array['SITE_NAME'] 		= 'BNPanel';
$array['SITE_EMAIL'] 		= 'example@example.com';
$array['INSTALL_OPTIONS'] 	= $main->createSelect('method', $values);
echo $style->replaceVar("tpl/install/install.tpl", $array);

echo $style->get("footer.tpl");
require LINK."output.php"; #Output it
