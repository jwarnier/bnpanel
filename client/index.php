<?php
/* For licensing terms, see /license.txt */

define("PAGE", "Client Area");

require '../includes/compiler.php';

//Main ACP Function - Creates the ACP basically
function client() {
	global $main, $db, $style, $type,$email;
		
	if (!$main->getvar['page']) { 
		$main->getvar['page'] = 'home';
	}
	$client_navigation = $main->getClientNavigation();
	$client_item = $client_navigation[$main->getvar['page']];
	
	$header = 'Home';
	$link = 'pages/home.php';	
	
	if (isset($client_item) && !empty($client_item)) {		
		$header = $client_item['visual'];		
		$link = "pages/". $client_item['link'] .".php";
	}
	
	if (!file_exists($link)) {
		$html = "Seems like the .php is non existant. Is it deleted?";	
	} else {
		//If deleting something
		if (preg_match("/[\.*]/", $main->getvar['page']) == 0) {			
			require $link;
			
			$content = new page();
						
			// Main Side Bar HTML
			$nav = "Sidebar";			
			$array = array();
			$array['LINKS'] = null;
			
			foreach($client_navigation as $row) {	
				if ($row['link'] == 'delete' && !$db->config('delacc')) {
					continue;
				}								
				$array2['IMGURL'] = $row['icon'];
				$array2['LINK'] = "?page=".$row['link'];
				$array2['VISUAL'] = $row['visual'];
				$array2['ACTIVE'] = 'active';
				$array['LINKS'] .= $style->replaceVar("menu/leftmenu_link.tpl", $array2);			
			}
			
			# Types Navbar
			$user_id = $main->getCurrentUserId();
						
			$array2['IMGURL'] = "logout.png";
			$array2['LINK'] = "?page=logout";
			$array2['VISUAL'] = "Logout";
			$array['LINKS'] .= $style->replaceVar("menu/leftmenu_link.tpl", $array2);
			$sidebar = $style->replaceVar("menu/leftmenu_main.tpl", $array);
			
			//Page Sidebar			
			if (isset($content->navtitle)) {
				$subnav = $content->navtitle;
				$array3 = array();
				$array3['LINKS'] = null;
				if (!empty($content->navlist)) {
					foreach($content->navlist as $key => $value) {
						$array2['IMGURL'] = $value[1];
						$array2['LINK'] = "?page=".$client_item['link']."&sub=".$value[2];
						$array2['VISUAL'] = $value[0];
						$array3['LINKS'] .= $style->replaceVar("tpl/menu/submenu_link.tpl", $array2);
					}
				}
				$subsidebar = $style->replaceVar("menu/submenu_main.tpl", $array3);
			}
			
			if (isset($main->getvar['sub']) && $main->getvar['sub'] == "delete" && isset($main->getvar['do']) && !$_POST && !$main->getvar['confirm']) {
				foreach($main->postvar as $key => $value) {
					$array['HIDDEN'] .= '<input name="'.$key.'" type="hidden" value="'.$value.'" />';
				}
				$array['HIDDEN'] .= " ";
				$html = $style->replaceVar("warning.tpl", $array);	
				
			} elseif(isset($main->getvar['sub']) && $main->getvar['sub'] == "delete" && isset($main->getvar['do']) && $_POST && !$main->getvar['confirm']) {
				if($main->postvar['yes']) {
					foreach($main->getvar as $key => $value) {
					  if($i) {
						  $i = "&";	
					  } else {
						  $i = "?";	
					  }
					  $url .= $i . $key . "=" . $value;
					}
					$url .= "&confirm=1";
					$main->redirect($url);
				}
				elseif($main->postvar['no']) {
					$main->done();	
				}
			} else {
				$content->content();				
			}
		} else {
			$html = "You trying to hack me? You've been warned. An email has been sent.. May I say, Owned?";
			$email->staff("Possible Hacking Attempt", "A user has been logged trying to hack your copy of THT, their IP is: ". $main->removeXSS($_SERVER['REMOTE_ADDR']));
		}
	}
	
	if (isset($main->getvar['sub']) && $main->getvar['sub'] && $main->getvar['page'] != "type") {
		if (is_array($content->navlist))
		foreach($content->navlist as $key => $value) {
			if($value[2] == $main->getvar['sub']) {
				define("SUB", $value[0]);
				$header = $value[0];
			}
		}
	}	
	$staffuser = $db->client($main->getCurrentUserId());	
	$style->assign('sidebar',  $sidebar);
	$style->assign('sub_menu', $content->get_submenu());		
	if (!empty($content->content)) {
		$style->assign('content', $content->content);
	}
}
global $user;

if (!isset($_SESSION['clogged'])) {
	if (isset($main->getvar['page']) && $main->getvar['page'] == 'forgotpass') {		
		define("SUB", "Reset Password");
		define("INFO", SUB);		
		if ($_POST && $main->checkToken()) {
			if (!empty($main->postvar['user']) && !empty($main->postvar['email']) ) {		
				$username 		= $main->postvar['user'];
				$useremail		= $main->postvar['email'];
				$user_info 		= $user->getUserByUserName($username);
				if (!empty($user_info)) {
					if ($user_info['email'] == $useremail) {				
						$password = $main->generatePassword();
						$user->changeClientPassword($user_info['id'], $password);
						$main->errors("Password reset, please check your email");
						$array['PASS'] = $password;
						$emaildata = $db->emailTemplate('reset');
						$email->send($user_info['email'], $emaildata['subject'], $emaildata['content'], $array);
						$main->generateToken();
					} else {
						$main->errors("That account doesn't exist");
					}
				} else {
					$main->errors("That account doesn't exist");
				}
			} else {
				$main->errors("Please fill the email and username");
			}
		}
		$main->generateToken();				
		$content = $style->replaceVar("tpl/login/reset.tpl");		
		echo $style->replaceVar("layout/one-col/index.tpl", array('content' => $content));				
		
	} else {
		define("SUB", "Login");
		define("INFO", " ");		
		if ($_POST && $main->checkToken()) {			
			if($main->clientLogin($main->postvar['user'], $main->postvar['pass'])) {
				$main->redirect("?page=home");	
			} else {
				$main->generateToken();
			}		
		}		
		$array[] = "";
		if(!$db->config("cenabled")) {
			define("SUB", "Disabled");
			define("INFO", SUB);
			$content = '<div class="center">'.$main->table(gettext("Client Area - Disabled"), $db->config("cmessage"), "300px").'</div>';
		} else {
			$content = $style->replaceVar("login/clogin.tpl", $array);
		}		
		echo $style->replaceVar("layout/one-col/index.tpl", array('content' => $content));		
	}
} elseif($_SESSION['clogged']) {
	if(!isset($main->getvar['page'])) {
		$main->getvar['page'] = "home";
	} elseif($main->getvar['page'] == 'logout') {	
		$referer = basename($_SERVER['HTTP_REFERER']);		
		$main->logout('client');
		$main->redirect(URL.'order');				
	}	
	if (!$db->config("cenabled")) {
		define("SUB", "Disabled");
		define("INFO", SUB);
		$content = '<div align="center">'.$main->table("Client Area - Disabled", $db->config("cmessage"), "300px").'</div>';
		echo $style->replaceVar("layout/one-col/index.tpl", $content);
	} else {		
		client();
		$style->display("layout/two-col/index.tpl");
	}		
}