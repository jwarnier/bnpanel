<?php
/* For licensing terms, see /license.txt */

require '../includes/compiler.php';

//THT Variables
define("PAGE", "Client Area");

//Main ACP Function - Creates the ACP basically
function client() {
	global $main, $db, $style, $type,$email;
	ob_start(); # Stop the output buffer
		
	if(!$main->getvar['page']) { 
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
	if(!file_exists($link)) {
		$html = "Seems like the .php is non existant. Is it deleted?";	
	} else {
		//If deleting something
		if(preg_match("/[\.*]/", $main->getvar['page']) == 0) {
			require $link;
			$content = new page();
			// Main Side Bar HTML
			$nav = "Sidebar";			
			
			foreach($client_navigation as $row) {	
				if ($row['link'] == 'delete' && !$db->config('delacc')) {
					continue;
				}								
				$array2['IMGURL'] = $row['icon'];
				$array2['LINK'] = "?page=".$row['link'];
				$array2['VISUAL'] = $row['visual'];
				$array['LINKS'] .= $style->replaceVar("tpl/menu/leftmenu_link.tpl", $array2);			
			}
			
			# Types Navbar
			$user_id = $main->getCurrentUserId();
						
			$array2['IMGURL'] = "logout.png";
			$array2['LINK'] = "?page=logout";
			$array2['VISUAL'] = "Logout";
			$array['LINKS'] .= $style->replaceVar("tpl/menu/leftmenu_link.tpl", $array2);
			$sidebar = $style->replaceVar("tpl/menu/leftmenu_main.tpl", $array);
			
			//Page Sidebar
			
			if($content->navtitle) {
				$subnav = $content->navtitle;				
				foreach($content->navlist as $key => $value) {
					$array2['IMGURL'] = $value[1];
					$array2['LINK'] = "?page=".$client_item['link']."&sub=".$value[2];
					$array2['VISUAL'] = $value[0];
					$array3['LINKS'] .= $style->replaceVar("tpl/menu/submenu_link.tpl", $array2);
				}
				$subsidebar = $style->replaceVar("tpl/menu/submenu_main.tpl", $array3);
			}
			
			if($main->getvar['sub'] == "delete" && isset($main->getvar['do']) && !$_POST && !$main->getvar['confirm']) {
				foreach($main->postvar as $key => $value) {
					$array['HIDDEN'] .= '<input name="'.$key.'" type="hidden" value="'.$value.'" />';
				}
				$array['HIDDEN'] .= " ";
				$html = $style->replaceVar("tpl/warning.tpl", $array);	
				
			} elseif($main->getvar['sub'] == "delete" && isset($main->getvar['do']) && $_POST && !$main->getvar['confirm']) {
				if($main->postvar['yes']) {
					foreach($main->getvar as $key => $value) {
					  if($i) {
						  $i = "&";	
					  }
					  else {
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
				if(isset($main->getvar['sub'])) {
					ob_start();
					$content->content();
					$html = ob_get_contents(); # Retrieve the HTML
					ob_clean(); # Flush the HTML
				}
				elseif($content->navlist) {
					//$html = "Select a sub-page from the sidebar.";
					ob_start();
					$content->content();
					$html = ob_get_contents(); # Retrieve the HTML
					ob_clean(); # Flush the HTML									
				} else {
					ob_start();
					$content->content();
					$html = ob_get_contents(); # Retrieve the HTML
					ob_clean(); # Flush the HTML	
				}
			}
		} else {
			$html = "You trying to hack me? You've been warned. An email has been sent.. May I say, Owned?";
			$email->staff("Possible Hacking Attempt", "A user has been logged trying to hack your copy of THT, their IP is: ". $main->removeXSS($_SERVER['REMOTE_ADDR']));
		}
	}
	
	if($main->getvar['sub'] && $main->getvar['page'] != "type") {
		if (is_array($content->navlist))
		foreach($content->navlist as $key => $value) {
			if($value[2] == $main->getvar['sub']) {
				define("SUB", $value[0]);
				$header = $value[0];
			}
		}
	}
	$staffuser = $db->client($main->getCurrentUserId());
	define("SUB", $header);
	define("INFO", '<b>Welcome back, '. $staffuser['user'] .'</b><br />'. SUB);
	
	echo '<div id="left">';
	echo $main->table($nav, $sidebar);
	if($content->navtitle) {
		echo "<br />";
		echo $main->table($subnav, $subsidebar);
	}
	echo '</div>';
	
	echo '<div id="right">';
	echo $main->table($header, $html);
	echo '</div>';
	
	$data = ob_get_contents(); # Retrieve the HTML
	ob_clean(); # Flush the HTML
	
	return $data; # Return the HTML
}

global $user;
if(!$_SESSION['clogged']) {	
	if($main->getvar['page'] == 'forgotpass') {		
		define("SUB", "Reset Password");
		define("INFO", SUB);
		echo $style->get("header.tpl");		
		if($_POST && $main->checkToken()) {
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
						$main->errors("That account doesn't exist!");
					}
				} else {
					$main->errors("That account doesn't exist!");
				}
			}
		}
		$main->generateToken();		
				
		echo '<div align="center">'.$main->table("Client Area - Reset Password", $style->replaceVar("tpl/login/reset.tpl", $array), "300px").'</div>';		
		echo $style->get("footer.tpl");
	} else {
		define("SUB", "Login");
		define("INFO", " ");		
		if($_POST && $main->checkToken()) {			
			if($main->clientLogin($main->postvar['user'], $main->postvar['pass'])) {
				$main->redirect("?page=home");	
			} else {
				$main->generateToken();
			}
		
		}	
		echo $style->get("header.tpl");
		$array[] = "";
		if(!$db->config("cenabled")) {
			define("SUB", "Disabled");
			define("INFO", SUB);
			echo '<div align="center">'.$main->table("Client Area - Disabled", $db->config("cmessage"), "300px").'</div>';
		} else {
			echo '<div align="center">'.$main->table("Client Area - Login", $style->replaceVar("tpl/login/clogin.tpl", $array), "300px").'</div>';
		}
		echo $style->get("footer.tpl");
	}
} elseif($_SESSION['clogged']) {
	if(!$main->getvar['page']) {
		$main->getvar['page'] = "home";
	} elseif($main->getvar['page'] == 'logout') {	
		$referer = basename($_SERVER['HTTP_REFERER']);		
		$main->logout();
		if ($referer == 'order') {
			$main->redirect(URL.'/order');
		} else {
			$main->redirect('?page=home');
		}		
	}
	if(!$db->config("cenabled")) {
		define("SUB", "Disabled");
		define("INFO", SUB);
		$content = '<div align="center">'.$main->table("Client Area - Disabled", $db->config("cmessage"), "300px").'</div>';
	} else {
		$content = client();
	}
	echo $style->get("header.tpl");
	echo $content;
	echo $style->get("footer.tpl");
}
//End the sctipt
require LINK .'output.php';