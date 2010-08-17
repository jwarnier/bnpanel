<?php
/* For licensing terms, see /license.txt */

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

define("LINK", "../includes/");
define("CRON", 0);
require_once LINK ."compiler.php";

//THT Variables
define("PAGE", "Admin Area");

//Main ACP Function - Creates the ACP basically
function acp() {
	global $main, $db, $style,$type, $email, $user;
	ob_start(); # Stop the output buffer
	
	if(!$main->getvar['page']) { 
		$main->getvar['page'] = 'home';
	}
	$admin_navigation = $user->getAdminNavigation();
	$admin_nave_item = $admin_navigation[$main->getvar['page']];
	
	$link ='pages/home.php';
	if (isset($admin_nave_item) && !empty($admin_nave_item)) {		
		$header = $admin_nave_item['visual'];
		$link = 'pages/'. $admin_nave_item['link'].'.php';
	}
	if(!file_exists($link)) {	
		$html = "<strong>Fatal Error:</strong> Seems like the .php is non existant. Is it deleted?";	
	} elseif(!$main->checkPerms($admin_nave_item['link'])) {		
		$html = "You don't have access to this page.";	
	} else {		
		//If deleting something
		//&& $main->linkAdminMenuExists($main->getvar['page']) == true
		if(preg_match("/[\.*]/", $main->getvar['page']) == 0  ) {			
			require $link;
			$content = new page();
			// Main Side Bar HTML
			$nav = "Sidebar Menu";		
						
			foreach ($admin_navigation as $row) {
				if($main->checkPerms($row['link'])) {
					$array2['IMGURL'] = $row['icon'];
					$array2['LINK'] = "?page=".$row['link'];
					$array2['VISUAL'] = $row['visual'];
					$array['LINKS'] .= $style->replaceVar("tpl/menu/leftmenu_link.tpl", $array2);
				}
			}
			
			# Types Navbar
			/*
			 * When Working on the navbar, to make a spacer use this:
			 * $array['LINKS'] .= $style->replaceVar("tpl/spacer.tpl");
			 */
			$type->createAll();
			foreach($type->classes as $key => $value) {
				if($type->classes[$key]->acpNav) {
					foreach($type->classes[$key]->acpNav as $key2 => $value)  {
						$array2['IMGURL'] = $value[2];
						$array2['LINK'] = "?page=type&type=".$key."&sub=".$value[1];
						$array2['VISUAL'] = $value[0];
						$array['LINKS'] .= $style->replaceVar("tpl/menu/leftmenu_link.tpl", $array2);	
						if($main->getvar['page'] == "type" && $main->getvar['type'] == $key && $main->getvar['sub'] == $value[1]) {
							define("SUB", $value[3]);
							$header = $value[3];
							$main->getvar['myheader'] = $value[3];
						}
					}
				}
			}
			$array2['IMGURL'] = "logout.png";
			$array2['LINK'] = "?page=logout";
			$array2['VISUAL'] = "Logout";
			$array['LINKS'] .= $style->replaceVar("tpl/menu/leftmenu_link.tpl", $array2);
			$sidebar = $style->replaceVar("tpl/menu/leftmenu_main.tpl", $array);
			
			//Page Sidebar
			
			$sidebar_link_link = "tpl/menu/leftmenu_link.tpl";
			$sidebar_link =  "tpl/menu/leftmenu_main.tpl";	
				
			if (isset($main->getvar['sub'])) {
				$sidebar_link_link = "tpl/menu/submenu_link.tpl";
				$sidebar_link =  "tpl/menu/submenu_main.tpl";							
			}
			
			if($content->navtitle) {
				$subnav = $content->navtitle;				
				foreach($content->navlist as $key => $value) {
					$array2['IMGURL'] = $value[1];
					$array2['LINK'] = "?page=".$main->getvar['page']."&sub=".$value[2];
					$array2['VISUAL'] = $value[0];
					$array3['LINKS'] .= $style->replaceVar($sidebar_link_link, $array2);
				}
				$subsidebar = $style->replaceVar($sidebar_link, $array3);
			}
			
			if($main->getvar['sub'] && $main->getvar['page'] != "type") {
				foreach($content->navlist as $key => $value) {
					if($value[2] == $main->getvar['sub']) {
						if(!$value[0]) {
							define("SUB", $main->getvar['page']);	
							$header = $main->getvar['page'];
						} else {
							define("SUB", $value[0]);
							$header = $value[0];
						}
					}
				}
			}
		
			
			if($main->getvar['sub'] == 'delete' && isset($main->getvar['do']) && !$_POST && !$main->getvar['confirm']) {
				
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
				} elseif($main->postvar['no']) {
					$main->done();	
				}
			} else {
				if(isset($main->getvar['sub'])) {
					ob_start();
					$content->content();
					$html = ob_get_contents(); # Retrieve the HTML
					ob_clean(); # Flush the HTML
				} elseif($content->navlist) {
					$description = $content->description();
					if (!empty($description)) {
						$html .= $description; # First, we gotta get the page description.
                    	$html .= "<br /><br />"; # Break it up
					}
                    // Now we should prepend some stuff here
                    $subsidebar2 .= "<strong>Page Submenu</strong><div class='break'></div>";
                    $subsidebar2 .= $subsidebar;
                    // Done, now output it in a sub() table
                    $html .= $main->sub($subsidebar2, NULL); # Initial implementation, add the SubSidebar(var) into the description, basically append it 
				} else {
					ob_start();
					$content->content();
					$html = ob_get_contents(); # Retrieve the HTML
					ob_clean(); # Flush the HTML
				}
			}
		} else {
			$html = "You trying to hack me? You've been warned. An email has been sent.. May I say, Owned?";
			$email->staff("Possible Hacking Attempt", "A user has been logged trying to hack your copy of BNPanel, their IP is: ". $_SERVER['REMOTE_ADDR']);
		}
	}
	
	$staffuser = $db->staff( $main->getCurrentStaffId());
	define("SUB", $header);
	define("INFO", '<b>Welcome back, '. strip_tags($staffuser['name']) .'</b><br />'. SUB);
	
	echo '<div id="left">';	
		echo $main->table($nav, $sidebar);
	echo '</div>';	
	echo '<div id="right">';	
		if (isset($main->getvar['sub'])) {
			if($content->navtitle) {				
				echo $main->table($subnav, $subsidebar);
			}
		}
		
	echo $main->table($header, $html);
	echo '</div>';
	
	$data = ob_get_contents(); # Retrieve the HTML
	ob_clean(); # Flush the HTML
	
	return $data; # Return the HTML
}

//If user is NOT log in 
if(!$_SESSION['logged']) {
	if($main->getvar['page'] == "forgotpass") {
		define("SUB", "Reset Password");
		define("INFO", SUB);
		echo $style->get("header.tpl");
		
		if($_POST) {
			if (!empty($main->postvar['user']) && !empty($main->postvar['email']) ) {
				$username 		= $main->postvar['user'];
				$useremail		= $main->postvar['email'];			
				$staff_info 	= $staff->getStaffUserByUserName($username);
				
				if (!empty($staff_info)) {										
					$password = rand(0,999999);
					$newpass = md5(md5($password) . md5($user_info['salt']));
					$params['password'] = $newpass;
					$staff->edit($staff_info['id'], $params);
					
					$main->errors("Password reset!");
					$array['PASS'] = $password;
					$emaildata = $db->emailTemplate("areset");
					$email->send($email2, $emaildata['subject'], $emaildata['content'], $array);
				} else {
					$main->errors("That account doesn't exist!");
				}
			}
		}
		echo '<div align="center">'.$main->table("Admin Area - Reset Password", $style->replaceVar("tpl/login/reset.tpl", $array), "300px").'</div>';		
		echo $style->get("footer.tpl");
	} else { 
		define("SUB", "Login");
		define("INFO", " ");
		if($_POST) { # If user submitts form
			if ($main->checkToken()) {
				if($main->staffLogin($main->postvar['user'], $main->postvar['pass'])) {
					$main->redirect("?page=home");	
				} else {
					$main->errors("Incorrect username or password!");
				}
			}
		}	
		echo $style->get("header.tpl");
		$array[] = "";
		echo '<div align="center">'.$main->table("Admin Area - Login", $style->replaceVar("tpl/login/alogin.tpl", $array), "300px").'</div>';
		echo $style->get("footer.tpl");
	}
} elseif($_SESSION['logged']) {
	//Ok user is already in 
	if(!$main->getvar['page']) {
		$main->getvar['page'] = "home";
	} elseif($main->getvar['page'] == "logout") {
		$main->logout();		
		$main->redirect("?page=home");
	}
	$content = acp();
	//exit;
	echo $style->get("header.tpl");
	echo $content;
	echo $style->get("footer.tpl");
}

//End the sctipt
require_once LINK ."output.php";
/*
//Memory usage
echo ('MemoryUsage').': '.number_format((memory_get_usage()/1048576), 3, '.', '') .'Mb' ;
echo '<br />';
echo ('MemoryUsagePeak').': '.number_format((memory_get_peak_usage()/1048576), 3, '.', '').'Mb';
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
echo '<br />'.$totaltime = number_format(($totaltime), 4, '.', '');
*/
?>