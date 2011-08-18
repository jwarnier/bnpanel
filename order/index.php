<?php
/* For licensing terms, see /license.txt */

require '../includes/compiler.php';

define("PAGE", "Order Form");
define("SUB", "Account Creation");
define("INFO", "IP Logged: ". $main->removeXSS($_SERVER['REMOTE_ADDR']));

echo $style->get("header.tpl"); #Output Header

//Deleting check  
unset($_SESSION['check']);

//Deleting last invoices just in case
unset($_SESSION['last_invoice_id']);	

#Check stuff
if($db->config('general') == 0) {
	$maincontent = $main->table("Signups Closed", $db->config("message"));
} elseif(!$main->checkIP($_SERVER['REMOTE_ADDR']) && !$db->config('multiple')) {
	$maincontent = $main->table("IP Already Exists!", "Your IP already exists in the database!");
} elseif(isset($_SESSION['clogged']) && $_SESSION['clogged'] && $db->config('multiple') != 1) {
	$maincontent = $main->table("Unable to sign-up!", "One package per account!");
} else {
	$_SESSION['orderform'] = true;	
}

global $billing;
echo '<div id="ajaxwrapper">'; #Ajax wrapper, for steps

$main->getvar['id'] = isset($main->getvar['id']) ? intval($main->getvar['id']) : null;

//Get all packages
if (!$main->getvar['id']) {
	$packages2 = $db->query("SELECT * FROM <PRE>packages WHERE is_hidden = 0 AND is_disabled = 0 ORDER BY `order` ASC"); 
} else {
	$packages2 = $db->query("SELECT * FROM <PRE>packages WHERE is_disabled = 0 AND id = '{$main->getvar['id']}'");
}

$user_info = $main->getCurrentUserInfo();

if (!empty($user_info)) {
	$array['WELCOME_MESSAGE'] = 'Signed in as <a href="'.URL.'client">'.$user_info['user'].'</a> | <a href="'.URL.'client/?page=logout">Logout</a>';	
} else {
	$array['WELCOME_MESSAGE'] = '<a href="#" onclick="showLogin();">Log in to your account</a>';
}

if($db->num_rows($packages2) == 0) {
	echo $main->table("No packages", '<div class="warning">There are no available Packages</div>');
} else {	
	$n = 0;
	$array['PACKAGES'] = null;
	while ($data = $db->fetch_array($packages2, 'ASSOC')) {
		if (!$n) {
			$array['PACKAGES'] .= "<tr>";	
		}
		$array2['NAME'] 		= $data['name'];
		$array2['DESCRIPTION'] 	= $data['description'];
		$array2['ID']			= $data['id'];
		$array2['PACKAGE_TYPE']	= $data['type'];
		
		if ($main->getCurrentStaffId()) {
			$array2['EDIT_LINK'] = '<a href="'.URL.'admin/?page=packages&sub=edit&do='.$data['id'].'" />'.$style->returnIcon('pencil.png').'<a/>';
		} else {
			$array2['EDIT_LINK'] = '';
		}
		
		$array['PACKAGES'] 	   .= $style->replaceVar("tpl/orderform/orderpackages.tpl", $array2);	
		$n++;
		if($n == 1) {
			$array['PACKAGES'] .= '<td width="2%"></td>';	
		}
		if($n == 2) {
			$array['PACKAGES'] .= '</tr>';	
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
	$array['DOMAIN'] = '<input name="cdom" id="cdom" type="text" maxlength="40" onkeyup="checkDomain();"/>';
	
	if ($main->getCurrentUserId()) {
		$array['LOGIN_TPL'] = '';
	} else {
		$array['LOGIN_TPL'] = '<div id="login_form" title="Login ">'.$style->replaceVar("tpl/login/login_widget.tpl", array()).'</div>';		
		//$array['LOGIN_TPL'] = $main->table("Client Area - Login", $style->replaceVar("tpl/login/login_widget.tpl", array()), '300px');
	}		
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
			$domain_options = '';
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
	if(!isset($_SESSION['clogged'])) {
		$content = $style->replaceVar("tpl/login/clogin.tpl");
	} else {		
		$user_id = $main->getCurrentUserId();
		$clientdata = $db->client($user_id);		
		$array['NAME'] = $clientdata['user'];
		$content = $style->replaceVar("tpl/user/cdetails.tpl", $array);
	}
	
	if(!isset($maincontent)) {
		$maincontent = $style->replaceVar("tpl/orderform/orderform.tpl", $array);
	}
	echo '<div>';
	echo $maincontent;
	echo '</div>';
}
echo '</div>'; #End it
echo $style->get("footer.tpl"); #Output Footer
//Output
require LINK ."output.php";