<?php
/* For licensing terms, see /license.txt */

# Start timer for Page Generated in: xxx seconds
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

//Billing types
define('BILLING_TYPE_ADDON', 					'addon');
define('BILLING_TYPE_PACKAGE', 					'package');

//Addong status
define('ADDON_STATUS_ACTIVE', 					1);
define('ADDON_STATUS_INACTIVE', 				0);

//Billing cycle status
define('BILLING_CYCLE_STATUS_ACTIVE', 			1);
define('BILLING_CYCLE_STATUS_INACTIVE', 		0);

// Relation between packages and users a.k.a Orders 
define('ORDER_STATUS_ACTIVE', 					1);
define('ORDER_STATUS_WAITING_USER_VALIDATION', 	2); //before ORDER_STATUS_INACTIVE
define('ORDER_STATUS_WAITING_ADMIN_VALIDATION', 3); //Awaiting Validation
define('ORDER_STATUS_CANCELLED', 				4); //Awaiting Payment
define('ORDER_STATUS_FAILED',					5); //Means that an order try
define('ORDER_STATUS_DELETED', 					9);

// Invoices
define('INVOICE_STATUS_PAID', 					1); // Active in THT 1
define('INVOICE_STATUS_CANCELLED', 				2); // Suspended in THT 2
define('INVOICE_STATUS_WAITING_PAYMENT', 		3); // Awaiting Payment 4
define('INVOICE_STATUS_DELETED', 				9); // Cancelled in 9

//Domain Options
define('DOMAIN_OPTION_DOMAIN', 					1); // Cancelled in 9
define('DOMAIN_OPTION_SUBDOMAIN', 				2); // Cancelled in 9
define('DOMAIN_OPTION_BOTH', 					3); // Cancelled in 9

define('PAYPAL_STATUS_LIVE', 					1);
define('PAYPAL_STATUS_SANDBOX', 				0);

// User status 
define('USER_STATUS_ACTIVE', 					1);// Active users
define('USER_STATUS_SUSPENDED', 				2);// Suspend users can't login
define('USER_STATUS_WAITING_ADMIN_VALIDATION',	3);// Waiting can't login till admin validation 
define('USER_STATUS_WAITING_USER_VALIDATION', 	4); //before ORDER_STATUS_INACTIVE
//define(USER_STATUS_WAITING_PAYMENT, 			4); //should not be use is useless!! 
define('USER_STATUS_DELETED', 					9); //cancelled deleted users


//Tickets
define('TICKET_URGENCY_VERY_HIGH', 				1); 
define('TICKET_URGENCY_HIGH', 					2); 
define('TICKET_URGENCY_MEDIUM', 				3); 
define('TICKET_URGENCY_LOW', 					4); 

define('TICKET_STATUS_OPEN', 					1); 
define('TICKET_STATUS_ON_HOLD', 				2); 
define('TICKET_STATUS_CLOSED', 					3); 

//Used in admin/billing.php
define('MAX_NUMBER_MONTHS',						48);

//Start us up
//if (CRON != 1) {
session_start();	
//}

//Defining paths
$includePath = dirname(__FILE__);
define('LINK', $includePath.'/');
define('MAIN', dirname($includePath).'/');

/**
 * 
 * Experimental translation feature for BNPanel 
 * 
 * Using gettext with BNPanel
 * 
 * Install this in Ubuntu
 * 
 * sudo locale-gen es_ES
 * sudo apt-get install php-gettext
 * 
 * Install Spanish locale: $ sudo locale-gen es_ES
 * Install English locale: $ sudo locale-gen en_GB
 * 
 * In Debian check this file More info: http://algorytmy.pl/doc/php/ref.gettext.php
 * vim /etc/locale.gen 
 *  
 * Translate po files using this GUI 
 * sudo apt-get install poedit 
 * 
 * Some help here:
 * 
 * Config getext
 * http://zez.org/article/articleview/42/3/
 *  * 
 * Using getext in ubuntu
 * http://www.sourcerally.net/regin/49-How-to-get-PHP-and-gettext-working-%28ubuntu,-debian%29
 * 
 * Getext tutorial
 * http://mel.melaxis.com/devblog/2005/08/06/localizing-php-web-sites-using-gettext/
 *  
 */

if (empty($_GET['l'])) {
	if(isset($_SESSION['locale'])) {
		$locale = $_SESSION['locale'];
	} else {
		$locale = 'en';
	}
} else {
	if (in_array($_GET['l'], array('nl', 'es', 'en'))) {
		$locale = $_GET['l'];		
	} else {
		$locale = 'en';
	}	
}
$_SESSION['locale'] = $locale;
switch ($locale) {
	case 'nl':
		$locale = 'nl_NL.UTF-8';	
	break;
	case 'es':
		$locale = 'es_ES.UTF-8';	
	break;
	case 'en':
	default:
		$locale = 'en_GB.UTF-8';
	break;
}

$domain = 'default';
putenv("LC_ALL=$locale");
setlocale(LC_ALL,$locale);

bindtextdomain($domain, MAIN.'locale'); // /var/www/bnpanel/locale
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);
/*
putenv("LC_ALL=$locale");
putenv("LANG=$locale"); 
setlocale(LC_ALL, $locale);
setlocale(LC_MESSAGES, $locale);
*/

//Stop the output
ob_start();

//Check for Dependencies
$d = checkForDependencies();
if($d !== true) {
	die((string)$d);
}

//Grab DB First
require LINK."class_db.php"; # Get the file
if (file_exists(LINK."conf.inc.php")) {
	require LINK."conf.inc.php"; # Get the config
	define("NOCONFIG", false);
} else {
	define("NOCONFIG", true);
}

if (isset($sql) && $sql['install']) {	
	define('INSTALL', 1);	
	$db = new db(); 	
	global $db;
	$db->getSystemConfigList();
	
	define('SERVER_STATUS', $db->config('server_status')); # Set the default theme	
} else {
	//Default constants
	define('SERVER_STATUS',	'test');
	define("INSTALL", 0);
}


#Page generated
if (SERVER_STATUS == 'test') {
	@error_reporting(E_ALL);	
	$starttime = explode(' ', microtime());
	$starttime = $starttime[1] + $starttime[0];
}

require_once LINK.'model.php'; # Get the file
require_once LINK.'class_main.php'; # Get the file
			
$main = new main(); # Create the class
if (isset($main) && !empty($main)) {
	global $main;		
}

//Improve security to avoid double agents with the same session, avoiding session hijacking
if ($main->checkUserAgent() == false) {
	$main->logout();
}

/* Autoload classes */
function __autoload($class_name) {
	$class_name = strtolower($class_name);
    require_once LINK.'class_'.$class_name . '.php';
}

$available_classes = array('addon', 'billing', 'currency', 'email', 'invoice', 'order', 'package', 'server', 'staff', 'style', 'ticket', 'type','user');
foreach($available_classes as $class_item) {	
	${$class_item} = new $class_item();
	global ${$class_item};		
}


// Setting GETs and POSTss 

$load_post = false;

if ($_POST) {
	$load_post = true;
}

if (!isset($is_ajax_load)) {
	if (!$load_post) {
		$token =  $main->generateToken();
		//var_dump('load_post->'.$token);
	}
} else {
	if ($main->isValidMd5($_GET['_get_token'])) {
		$token =  $_GET['_get_token'];
	} else {
		$token = md5(uniqid(rand(),TRUE));
	}
}

//Converts all POSTS into variable - DB Friendly.
if (isset($_POST)) {
	foreach($_POST as $key => $value) {
		$main->postvar[$key] = $value;
	}
}

$main->postvar['_post_token'] =	$main->getToken();
//var_dump('postvar->'.$main->postvar['_post_token']);

//Converts all GET into variable - DB Friendly.
if (isset($_GET)) {
	foreach($_GET as $key => $value) {
		switch ($key) {
			case 'do':
			case 'id':
				$main->getvar[$key] = intval($value);
				break;
			default:
				$main->getvar[$key] = $value;
			break;
		}
	}
}
$main->getvar['_get_token'] = $main->getToken();

if (INSTALL == 1) {	
	define("THEME", $db->config("theme")); # Set the default theme
	define("URL", 	$db->config("url")); # Sets the URL THT is located at	
	define("NAME", 	$db->config("name")); # Sets the name of the website	
} else {
	
	define("THEME", 'bnpanel'); # Set the default theme
	define("URL", "../"); # Set url to blank
	define("NAME", 	'BNPanel'); # Sets the name of the website
}
	

$path		= dirname($main->removeXSS($_SERVER['PHP_SELF']));
$position 	= strrpos($path,'/') + 1;
$folder 	= substr($path, $position);	
define("FOLDER", $folder); # Add current folder name to global

if (FOLDER != "install" && FOLDER != "includes" && INSTALL != 1) { # Are we installing?	
	//Lets just redirect to the installer, shall we?	
	if ($path == '/') {
		$installURL = $path . "install";
	} else {
		 $installURL = $path . "/install";
	}	
	header("Location: $installURL");
	exit;
}

//Resets the error.
$_SESSION['ecount'] = 0;
if (!isset($_GET['msg'])) {
	$_SESSION['errors'] = null;
}

//If payment..
if (FOLDER == "client" && isset($main->getvar['page']) && $main->getvar['page'] == 'invoices' && $main->getvar['iid'] && $_SESSION['clogged'] == 1) {
	if ($main->checkToken(false)) {
		$invoice->pay($main->getvar['iid'], 'client/index.php?page=invoices');
	}
	echo 'You made it this far.. something went wrong.';
}

function checkForDependencies() {
	//Here, we're going to see if we have the functions that we need. :D
	$needed = array();
	//First things first:
	$version = explode(".", phpversion());
	if($version[0] < 5) {
		die("PHP Version 5 or over is required! You're currently running: " . phpversion());
	}
	if(!function_exists("curl_init")) {
		$needed[] = "cURL (sudo apt-get install php5-curl in Debian base machines) ";
	}
	if(!function_exists("mysql_connect")) {
		$needed[] = "MySQL";
	}
	if(count($needed) == 0) {
		return true;
	}
	else {
		$output = "The following function(s) are/is needed for
		BNPanel to run properly: <ul>";
		foreach($needed as $key => $value) {
			$output .= "<li>$value</li>";
		}
		$output .= "</ul>";
		return $output;
	}
}