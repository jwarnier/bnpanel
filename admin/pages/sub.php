<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		
		$this->navtitle = "Subdomain Sub Menu";
		$this->navlist[] = array("View All Subdomains", "package_add.png", "view");
		$this->navlist[] = array("Add Subdomain", "add.png", "add");				
		
	}
	public function description() {
		return "<strong>Managing Subdomains</strong><br />
		This is where you add domains so users can make subdomains with them.<br />
		To get started, choose a link from the sidebar's SubMenu.";	
	}
	public function content() { # Displays the page 
		global $main, $style, $db, $server;
		$subdomain_list = $main->getSubDomains();
		
		switch($main->getvar['sub']) {
			default:
				if($_POST) {
					if($main->checkToken()) {
						foreach($main->postvar as $key => $value) {
							if($value == "" && !$n) {
								$style->showMessage("Please fill in all the fields!");
								$n++;
							}
						}						
						if (!in_array($main->postvar['subdomain'],$subdomain_list)) {
							if(!$n) {								
								if ($main->validDomain($main->postvar['subdomain'])) {
									$db->query("INSERT INTO `<PRE>subdomains` (subdomain, server) VALUES('{$main->postvar['subdomain']}', '{$main->postvar['server']}')");
									$main->errors("Subdomain has been added!");
									$main->redirect('?page=sub&sub=view&msg=1');
								} else {
									$style->showMessage('Domain not valid');							
								}
							}
							$main->generateToken();
						} else {
							$main->errors("Subdomain already exist");
							$main->generateToken();							
						}
					}					
				}
				$all_servers = $server->getAllServers();
				
				if (!empty($all_servers)) {
					$array['SERVER'] = $main->createSelect("server", $all_servers);
					echo $style->replaceVar("tpl/subdomain/addsubdomain.tpl", $array);
				} else {
					$main->errors('There are no servers, you need to add a Server first <a href="?page=servers&sub=add">here</a>');
					echo '<ERRORS>';
				}				
			break;
			case 'view':	
			case 'edit':
				if(isset($main->getvar['do'])) {
					$query = $db->query("SELECT * FROM `<PRE>subdomains` WHERE id = '{$main->getvar['do']}'");
					if($db->num_rows($query) == 0) {
						echo "That subdomain doesn't exist!";	
					} else {
						if($_POST && $main->checkToken()) {			
							foreach($main->postvar as $key => $value) {
								if($value == "" && !$n) {
									$main->errors("Please fill in all the fields!");
									$n++;
								}
							}
							//if (!in_array($main->postvar['subdomain'], $subdomain_list)) {
								if(!$n) {
									if ($main->validDomain($main->postvar['subdomain'])) {
									
										$db->query("UPDATE `<PRE>subdomains` SET subdomain = '{$main->postvar['subdomain']}', 
																	  server = '{$main->postvar['server']}'
																	   WHERE id = '{$main->getvar['do']}'");
										$main->errors("Subdomain has been edited");
									} else {
										$main->errors("Enter a valid domain");
									}									
									$main->redirect('?page=sub&sub=edit&msg=1');
								}
							//} else {
								//$main->errors("Subdomain already taken");
								//$main->generateToken();
							//}

						}
						$data = $db->fetch_array($query);
						$array['SUBDOMAIN'] = $data['subdomain'];
						$query = $db->query("SELECT * FROM `<PRE>servers`");
						while($data = $db->fetch_array($query)) {
							$values[] = array($data['name'], $data['id']);	
						}
						$array['SERVER'] = $array['THEME'] = $main->dropDown("server", $values, $data['server']);
						echo $style->replaceVar("tpl/subdomain/editsubdomain.tpl", $array);
					}
				} else {
					$query = $db->query("SELECT * FROM `<PRE>subdomains`");
					echo "<ERRORS>";
					if($db->num_rows($query) == 0) {
						$style->showMessage("There are no Subdomains available!");	
					} else {
						
						while($data = $db->fetch_array($query)) {
							echo $main->sub("<strong>".$data['subdomain']."</strong>", '<a href="?page=sub&sub=edit&do='.$data['id'].'"><img src="'. URL .'themes/icons/pencil.png"></a>&nbsp;<a href="?page=sub&sub=delete&do='.$data['id'].'"><img src="'. URL .'themes/icons/delete.png"></a>');
						}
					}
				}
				break;
			
			case "delete":
				if(isset($main->getvar['do'])) {
					if($main->checkToken()) {	
						$db->query("DELETE FROM `<PRE>subdomains` WHERE `id` = '{$main->getvar['do']}'");
						$main->errors("Subdomain Deleted!");
						$main->redirect('?page=sub&sub=edit&msg=1');
					}		
				}				
			break;
		}
	}
}