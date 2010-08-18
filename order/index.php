<?php
/* For licensing terms, see /license.txt */

include '../includes/compiler.php';

//THT Variables
define("PAGE", "Order Form");
define("SUB", "Account Creation");
define("INFO", "IP Logged: ". $main->removeXSS($_SERVER['REMOTE_ADDR']));

#If logout
if($main->getvar['do'] == "logout") {
	session_destroy();
	$main->redirect("../order/");
}

echo $style->get("header.tpl"); #Output Header
$ip = $_SERVER['REMOTE_ADDR'];

//Deleting check  
unset($_SESSION['check']);
//Deleting last invoices just in case
unset($_SESSION['last_invoice_id']);	

#Check stuff
if($db->config("general") == 0) {
	$maincontent = $main->table("Signups Closed", $db->config("message"));
} elseif(!$main->checkIP($ip) && !$db->config("multiple")) {
	$maincontent = $main->table("IP Already Exists!", "Your IP already exists in the database!");
} elseif($_SESSION['clogged'] && $db->config('multiple') != 1) {
	$maincontent = $main->table("Unable to sign-up!", "One package per account!");
} else {
	$_SESSION['orderform'] = true;	
}

global $billing;
echo '<div id="ajaxwrapper">'; #Ajax wrapper, for steps

//Get all packages
if(!$main->getvar['id']) {
	$packages2 = $db->query("SELECT * FROM `<PRE>packages` WHERE `is_hidden` = 0 AND `is_disabled` = 0 ORDER BY `order` ASC"); 
} else {
	$packages2 = $db->query("SELECT * FROM `<PRE>packages` WHERE `is_disabled` = 0 AND `id` = '{$main->getvar['id']}'");
}

if($db->num_rows($packages2) == 0) {
	echo $main->table("No packages", "Sorry there are no available packages!");
} else {
	while($data = $db->fetch_array($packages2)) {
		if(!$n) {
			$array['PACKAGES'] .= "<tr>";	
		}
		$array2['NAME'] 		= $data['name'];
		$array2['DESCRIPTION'] 	= $data['description'];
		$array2['ID']			= $data['id'];
		$array2['PACKAGE_TYPE']	= $data['type'];
		$array['PACKAGES'] 	   .= $style->replaceVar("tpl/orderpackages.tpl", $array2);	
		$n++;
		if($n == 1) {
			$array['PACKAGES'] .= '<td width="2%"></td>';	
		}
		if($n == 2) {
			$array['PACKAGES'] .= "</tr>";	
			$n = 0;	
		}		
		//Selecting billing cycles
		$billing_cycle_data = $billing->getAllBillingCycles();	
		$array['BILLING_CYCLE'] = '';
		foreach($billing_cycle_data as $billing_data) {
			$array['BILLING_CYCLE'].= '<option value="'.$billing_data['id'].'">'.$billing_data['name'].'</option>';
		}		
	}
	$array['COUNTRY_SELECT'] = $main->countrySelect();			
	$array['TOS'] = $db->config('tos');
	$array['USER'] = "";
	$array['DOMAIN'] = '<input name="cdom" id="cdom" type="text" />';
	$subdomain_list = $main->getSubDomains();
		
	switch($db->config('domain_options')) {
		case DOMAIN_OPTION_DOMAIN:	
		$domain_options = '<div style="display:none"><select name="domain" id="domain">
	              			<option value="dom" selected="selected">Domain</option>	              			
	            			</select></div>';
		break;
		case DOMAIN_OPTION_SUBDOMAIN:
		if (!empty($subdomain_list)) {
			$domain_options = '<div style="display:none"><select name="domain" id="domain">
          			<option value="sub" selected="selected">Subdomain</option>	              			
        			</select></div>';
		} else {
			$domain_options = 'No current subdomain';
		}
		break;
		case DOMAIN_OPTION_BOTH:		
			$sub_domain_option = '<option value="sub">Subdomain</option>';
			$domain_options = '
			<div class="table">
			    <div class="cat">Select your domain type</div>
			    <div class="text">
			        <table width="100%" border="0" cellspacing="2" cellpadding="0">
			          <tr>
			            <td width="20%">Domain/Subdomain:</td>
			            <td>
			            	<select name="domain" id="domain">
			              		<option value="dom" selected="selected">Domain</option>
			              		'.$sub_domain_option.';
			            	</select>
			            </td>
			            <td width="70%">
			            	<a title="Choose the type of hosting:<br /><strong>Domain:</strong> example.com<br /><strong>Subdomain:</strong> example.subdomain.com" class="tooltip">
			            	<img src="<URL>themes/icons/information.png" /></a>
			            </td>
			          </tr>                  
			        </table>
			    </div>
			</div>';
		break;
	}
	
	$array['DOMAIN_CONFIGURATION'] = $domain_options;
	
	//Determine what to show in Client box
	if(!$_SESSION['clogged']) {
		$content = $style->replaceVar("tpl/login/clogin.tpl");
	} else {		
		$user_id = $main->getCurrentUserId();
		$clientdata = $db->client($user_id);		
		$array['NAME'] = $clientdata['user'];
		$content = $style->replaceVar("tpl/user/cdetails.tpl", $array);
	}
	if(!$maincontent) {
		$maincontent = $style->replaceVar("tpl/orderform.tpl", $array);
	}

	echo '<div>';
	echo $maincontent;
	echo '</div>';

}
echo '</div>'; #End it
echo $style->get("footer.tpl"); #Output Footer
//Output
include(LINK ."output.php");
