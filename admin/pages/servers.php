<?php
/* For licensing terms, see /license.txt */
//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();	
	public $array_type = null;
	
							
	public function __construct() {
		global $main, $server;
		$this->navtitle = "Servers Sub Menu";
		$this->navlist[] = array("View Servers", "server_go.png", "view");
		$this->navlist[] = array("Add Server", "server_add.png", "add");
		$this->navlist[] = array("Delete Server", "server_delete.png", "delete");
		
		$files = $main->folderFiles(LINK."servers/");
		require_once LINK.'servers/panel.php';
		if(is_array($files) && count($files) > 0) {
			foreach($files as $value) {						
				if ($value != 'panel.php') {
					require_once LINK."servers/".$value;
					$fname = explode(".", $value);					
					$stype = new $fname[0];
					$values[] = array($stype->name, $fname[0]);	
				}
			}
		}
		
		$this->array_type = $main->dropDown("type", $values, 0, 0);
		
		/*$server_list = $server->getAvailablePanels();
		$this->array_type = $main->createSelect('type', $server_list);*/
		
	}
	
	public function description() {
		return "<strong>Managing Hosting Servers</strong><br />
		Welcome to the Servers Management Area. Here you can view, add, and delete servers.<br />
		To get started, choose a link from the sidebar's SubMenu.";	
	}
	
	public function content() { # Displays the page 
		global $main, $style, $db, $server;
		switch($main->getvar['sub']) {
			default:
				if($_POST) {
					foreach($main->postvar as $key => $value) {
						if($value == "" && !$n) {
							$main->errors("Please fill in all the fields!");
							$n++;
						}
					}
					if(!$n) {
						$main->postvar['accesshash'] = $main->postvar['hash']; 
						$server->create($main->postvar);
						$main->errors("Server has been added!");
					}
				}
				$array['TYPE'] = $this->array_type;
				echo $style->replaceVar("tpl/addserver.tpl", $array);
			break;
			
			case 'view':
				if(isset($main->getvar['do'])) {
					$query = $db->query("SELECT * FROM `<PRE>servers` WHERE `id` = '{$main->getvar['do']}'");
					if($db->num_rows($query) == 0) {
						echo "That server doesn't exist!";	
					}
					else {
						if($_POST) {
							foreach($main->postvar as $key => $value) {
								if($value == "" && !$n) {
									$main->errors("Please fill in all the fields!");
									$n++;
								}
							}
							if(!$n) {
								$main->postvar['accesshash'] = $main->postvar['hash']; 
								$server->edit($main->getvar['do'], $main->postvar);
								$main->errors("Server edited!");
								$main->done();
							}
						}
						$data = $db->fetch_array($query);
						
						$array['USER'] = $data['user'];
						$array['HOST'] = $data['host'];
						$array['NAME'] = $data['name'];
						$array['HASH'] = $data['accesshash'];
						$array['ID'] = $data['id'];
						$array['TYPE'] = $this->array_type;
						
						global $server;
						
						$server_php = $server->loadServer($data['id']);						
						$server_php->testConnection();
						echo $style->replaceVar("tpl/viewserver.tpl", $array);
					}
					
			
					
					
				} else {
					$query = $db->query("SELECT * FROM `<PRE>servers`");
					if($db->num_rows($query) == 0) {
						echo "There are no servers to view!";	
					}
					else {
						echo "<ERRORS>";
						while($data = $db->fetch_array($query)) {
							echo $main->sub("<strong>".$data['name']."</strong>", '<a href="?page=servers&sub=view&do='.$data['id'].'"><img src="'. URL .'themes/icons/pencil.png"></a>');
							if($n) {
								echo "<br />";	
							}
							$n++;
						}
					}
				}
				break;			
			case 'delete':
				if($main->getvar['do']) {
					$server->delete($main->getvar['do']);
					$main->errors("Server Account Deleted!");		
				}
				$query = $db->query("SELECT * FROM `<PRE>servers`");
				if($db->num_rows($query) == 0) {
					echo "There are no servers to delete!";	
				} else {
					echo "<ERRORS>";
					while($data = $db->fetch_array($query)) {
						echo $main->sub("<strong>".$data['name']."</strong>", '<a href="?page=servers&sub=delete&do='.$data['id'].'"><img src="'. URL .'themes/icons/delete.png"></a>');
						if($n) {
							echo "<br />";	
						}
						$n++;
					}
				}
			break;
		}
	}
}