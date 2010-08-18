<?php
/* For licensing terms, see /license.txt */
//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Staff Accounts Sub Menu";
		$this->navlist[] = array("Add Staff Account", "user_add.png", "add");
		$this->navlist[] = array("Edit Staff Account", "user_edit.png", "edit");
		$this->navlist[] = array("Delete Staff Account", "user_delete.png", "delete");
	}
	
	public function description() {
		return "<strong>Managing Staff Accounts</strong><br />
		This is where you add/edit/delete staff accounts. <b>Be careful, don't delete yourself!</b><br />
		To get started, just choose a link from the sidebar's SubMenu.";	
	}
	public function content() { # Displays the page 
		global $main, $style, $db, $staff, $user;
		
		$admin_navigation = $user->getAdminNavigation();		
		
		switch($main->getvar['sub']) {
			default:
				if($_POST && $main->checkToken()) {		
					foreach($main->postvar as $key => $value) {
						if($value == "" && !$n) {
							$main->errors("Please fill in all the fields!");
							$n++;
						}
						$broke = explode("_", $key);
						if($broke[0] == 'pages') {							
							$main->postvar['perms'][$broke[1]] = $value;	
						}
					}
					if(!$n) {
						if ($staff->userNameExists($main->postvar['user'])) {
							$main->errors("That account already exists!");	
						} else {						
							if(!$main->check_email($main->postvar['email'])) {
								$main->errors("Your email is the wrong format!");
							} elseif($main->postvar['pass'] != $main->postvar['conpass']) {
								$main->errors("Passwords don't match!");								
							}						
							
							if($main->postvar['perms']) {
								foreach($main->postvar['perms'] as $key => $value) {
									if($n) {
										$string .= ",";	
									}
									if($value == '1') {
										$string .= $key;
									}
									$n++;
								}
							}							
							$main->postvar['perms'] 	= $string;
							$main->postvar['password'] 	= $main->postvar['pass'];
							$staff->create($main->postvar);	
							$main->errors('Account added!');	
						}
					}
				}	
				$main->generateToken();			
							
				$array['PAGES'] = '<table width="100%" border="0" cellspacing="0" cellpadding="1">';
				
				foreach( $admin_navigation as $data) {
					$array['PAGES'] .= '<tr><td width="30%" align="left">'.$data['visual'].':</td><td><input name="pages_'.$data['link'].'" id="pages_'.$data['link'].'" type="checkbox" value="1" /></td></tr>';
				}
				$array['PAGES'] .= "</table>";
				echo $style->replaceVar("tpl/staff/addstaff.tpl", $array);
			break;
			
			case 'edit':
				if(isset($main->getvar['do'])) {
					$staff_info	=	$staff->getStaffUserById($main->getvar['do']);					
					if (empty($staff_info)) {
						echo "That account doesn't exist!";
					} else {
						if($_POST && $main->checkToken()) {
							foreach($main->postvar as $key => $value) {
								if($value == "" && !$n) {
									$main->errors("Please fill in all the fields!");
									$n++;
								}
								$broke = explode("_", $key);
								if($broke[0] == "pages") {
									$main->postvar['perms'][$broke[1]] = $value;	
								}
							}
							if(!$n) {
								
								if(!$main->check_email($main->postvar['email'])) {
									$main->errors("Your email is the wrong format!");
								} else {
									foreach($main->postvar['perms'] as $key => $value) {
										if($n) {
											$string .= ",";	
										}
										if($value == "1") {
											$string .= $key;
										}
										$n++;
									}
									
									$main->postvar['perms'] = $string;
									$staff->edit($main->getvar['do'], $main->postvar);									
									$main->errors("Staff account edited!");
									//$main->done();
								}
							}
						}
						
						$array['USER'] = $staff_info['user'];
						$array['EMAIL'] = $staff_info['email'];
						$array['NAME'] = $staff_info['name'];
						
						$perms = explode(",", $staff_info['perms']);							
						$perm_list = array();			
						foreach($perms as $value) {
							$perm_list[]= $value;						
						}
						
						$array['PAGES'] = '<table width="100%" border="0" cellspacing="0" cellpadding="1">';
						
						foreach( $admin_navigation as $data2) {
							if(in_array($data2['link'], $perm_list)) {
								$string = 'checked="checked"';	
							}
							$array['PAGES'] .= '<tr><td width="30%" align="left">'.$data2['visual'].':</td><td><input name="pages_'.$data2['link'].'" id="pages_'.$data2['link'].'" type="checkbox" value="1" '.$string.'/></td></tr>';
							$string = NULL;
						}
						$array['PAGES'] .= "</table>";
						
						echo $style->replaceVar("tpl/staff/editstaff.tpl", $array);	
					}
				} else {					
					$staff_list = $staff->gettAllStaff();
					echo "<ERRORS>";
					foreach($staff_list as $data) {
						echo $main->sub("<strong>".$data['user']."</strong>", '<a href="?page=staff&sub=edit&do='.$data['id'].'"><img title="Edit" src="'. URL .'themes/icons/pencil.png"></a>');											
					}
				}
				break;
			
			case 'delete':				
				$user_id = $main->getCurrentStaffId();
				if(!empty($main->getvar['do']) && $user_id != $main->getvar['do'] && $main->checkToken()) {
					$staff->delete($main->getvar['do'], true);						
					$main->errors("Staff Account Deleted!");
				}
				$staff_list = $staff->gettAllStaff();
				echo "<ERRORS>";
				foreach($staff_list as $data) {
						//Do not delete my self
					if ($data['id'] != $user_id) {
						echo $main->sub("<strong>".$data['user']."</strong>", '<a href="?page=staff&sub=delete&do='.$data['id'].'"><img title="Delete" src="'. URL .'themes/icons/delete.png"></a>');
					} else {
						echo $main->sub("<strong>".$data['user']."</strong>", '<img title="You can\'t delete yourself" src="'. URL .'themes/icons/delete_na.png">');
					}					
				}
			break;
		}
	}
}