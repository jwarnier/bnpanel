<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "General Settings Sub Menu";
		$this->navlist[] = array("General Configuration", 	"world.png", "paths");
		$this->navlist[] = array("Email Configuration", 	"email.png", "email");
		$this->navlist[] = array("Security Settings", 		"lock.png", "security");
		$this->navlist[] = array("Signup Form", 			"user_red.png", "signup");
		$this->navlist[] = array("Terms of Service", 		"application_edit.png", "tos");
		$this->navlist[] = array("Client Area", 			"user_go.png", "client");
		$this->navlist[] = array("Support Area", 			"help.png", "support");
		$this->navlist[] = array("Paid Configuration",		"coins.png", "paid_configuration");
		
	}
	
	public function description() {
		return "<strong>System Settings</strong><br />
		This is where you control the main THT Functions. Change the Titles and Paths, work on the signup form,
		edit the TOS, change the Look &amp; Feel...<br />
		To get started, choose a link from the sidebar's SubMenu.";	
	}
	
	public function content() { # Displays the page 
		global $main, $style, $db;
		
		if($_POST && $main->checkToken()) {
			foreach($main->postvar as $key => $value) {
				if($value == "" && !$n) {
					$main->errors("Please fill in all the fields!");
					$n++;
				}
			}
			if(!$n) {				
				foreach($main->postvar as $key => $value) {
					$db->updateConfig($key, $value);
				}
				$main->errors("Settings Updated!");
				$main->generateToken();
				//Regenerate the config array
				$db->getSystemConfigList(true);
			}
		}
		switch($main->getvar['sub']) {
			default:
				$array['NAME'] 			= $db->config('name');
				$array['URL'] 			= $db->config('url');
				$array['ROWS_PER_PAGE'] = $db->config('rows_per_page');
				$array['RECURL'] 		= $main->removeXSS($_SERVER['HTTP_HOST']);
				
				//$array['SITE_EMAIL'] 	= $db->config('site_email');
				
				$domain_option_id = $db->config('domain_options');
				$domain_values = array(DOMAIN_OPTION_DOMAIN=>'Only Domains', DOMAIN_OPTION_SUBDOMAIN=>'Only Subdomains', DOMAIN_OPTION_BOTH=>'Both');
				$array['DOMAIN_OPTIONS'] = $main->createSelect('domain_options', $domain_values, $domain_option_id);
								
				$values[] = array("Admin Area", "admin");
				$values[] = array("Order Form", "order");
				$values[] = array("Client Area", "client");
				$array['DROPDOWN'] = $main->dropDown("default", $values, $db->config("default"));
				echo $style->replaceVar("tpl/settings/pathsettings.tpl", $array);
				break;
				
			case "security": #security settings
			    global $db;
			    $values[] = array("Yes", "1");
			    $values[] = array("No", "0");
			    $array['SHOW_VERSION_ID'] = $main->dropDown("show_version_id", $values, $db->config("show_version_id"));
			    $array['SHOW_ACP_IN_MENU'] = $main->dropDown("show_acp_menu", $values, $db->config("show_acp_menu"));
			    $array['SHOW_PAGE_GENTIME'] = $main->dropDown("show_page_gentime", $values, $db->config("show_page_gentime"));
                            $array['SHOW_WHM_SSL'] = $main->dropDown("whm-ssl", $values, $db->config("whm-ssl"));
				$array['SHOW_FOOTER'] = $main->dropDown("show_footer", $values, $db->config("show_footer"));
			    echo $style->replaceVar("tpl/asecurity.tpl", $array);
			    break;			
				
			case "tos":
				global $db;
				$array['TOS'] = $db->config("tos");
				echo $style->replaceVar("tpl/tos.tpl", $array);
				break;
				
			case "signup":
				$values[] = array("Enabled", "1");
				$values[] = array("Disabled", "0");
				$array['MULTIPLE'] = $main->dropDown("multiple", $values, $db->config("multiple"));
				$array['TLDONLY'] = $main->dropDown("tldonly", $values, $db->config("tldonly"));
				$array['GENERAL'] = $main->dropDown("general", $values, $db->config("general"));
				$array['MESSAGE'] = $db->config("message");
				echo $style->replaceVar("tpl/settings/signupsettings.tpl", $array);

				break;
				
			case "client":
				$values[] = array("Enabled", "1");
				$values[] = array("Disabled", "0");
				$array['DELACC'] = $main->dropDown("delacc", $values, $db->config("delacc"));
				$array['CENABLED'] = $main->dropDown("cenabled", $values, $db->config("cenabled"));
				$array['CMESSAGE'] = $db->config("cmessage");
				$array['ALERTS'] = $db->config("alerts");
				echo $style->replaceVar("tpl/user/clientsettings.tpl", $array);
				break;
				
		    case "support":
		        $values[] = array("Enabled", "1");
		        $values[] = array("Disabled", "0");
		        $array['SENABLED'] = $main->dropDown("senabled", $values, $db->config("senabled"));
		        $array['SMESSAGE'] = $db->config("smessage");
		        echo $style->replaceVar("tpl/settings/supportsettings.tpl", $array);
		        break;
		        
			case "email":
				$values[] = array("PHP Mail", "php");
				$values[] = array("SMTP (PEAR)", "smtp");
				$array['METHOD'] = $main->dropDown("emailmethod", $values, $db->config("emailmethod"), 0);
				$array['EMAILFROM'] = $db->config("emailfrom");
				$array['SMTP_HOST'] = $db->config("smtp_host");
				$array['SMTP_USER'] = $db->config("smtp_user");
				$array['SMTP_PASS'] = $db->config("smtp_password");
				echo $style->replaceVar("tpl/email/emailsettings.tpl", $array);
				break;
				
			case 'paid_configuration':
			
				$values[] = array("Pound Sterling","GBP");
				$values[] = array("US Dollars","USD");
				$values[] = array("Australian Dollars","AUD");
				$values[] = array("Canadian Dollars","CAD");
				$values[] = array("Euros","EUR");
				$values[] = array("Yen","JPY");
				$values[] = array("New Zealand Dollar","NZD");
				$values[] = array("Swiss Franc","CHF");
				$values[] = array("Hong Kong Dollar","HKD");
				$values[] = array("Singapore Dollar","SGD");
				$values[] = array("Swedish Krona","SEK");
				$values[] = array("Danish Krone","DKK");
				$values[] = array("Polish Zloty","PLN");
				$values[] = array("Norwegian Krone","NOK");
				$values[] = array("Hungarian Forint","HUF");
				$values[] = array("Czech Koruna","CZK");
				$values[] = array("Israeli Shekel","ILS");
				$values[] = array("Mexican Peso","MXN");
				
				$array['CURRENCY'] = $main->dropDown("currency", $values, $db->config("currency"));
				$array['SUSDAYS'] = $db->config("suspensiondays");
				$array['TERDAYS'] = $db->config("terminationdays");
				
				$selected_id = $db->config('paypal_mode');
				$values=array(0=>'Sandbox',1=>'Live');			
				$array['PAYPAL_MODE'] = $main->createSelect('paypal_mode', $values, $selected_id);
				
				$array['PAYPALEMAIL'] = $db->config("paypalemail");
				echo $style->replaceVar("tpl/paid/acp.tpl", $array);
			break;
		}
	}
}