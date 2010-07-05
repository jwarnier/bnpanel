<?php

//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Billing Sub Menu";
		$this->navlist[] = array("Add Billing cycle", "package_add.png", "add");
		$this->navlist[] = array("Edit Billing cycle", "package_go.png", "edit");
		$this->navlist[] = array("Delete Billing cycle", "package_delete.png", "delete");
	}
	
	public function description() {
		return "<strong>Managing Billing cycles</strong><br />
				Welcome to the Package Management Area. Here you can add, edit and delete web hosting packages. Have fun :)<br />
				To get started, choose a link from the sidebar's SubMenu.";	
	}
	
	public function content() { # Displays the page 
		global $main;
		global $style;
		global $db;
		switch($main->getvar['sub']) {
			default:
				if($_POST) {
					foreach($main->postvar as $key => $value) {
						if($value == "" && !$n && $key != "admin") {
							$main->errors("Please fill in all the fields!");
							$n++;
						}
					}
					if(!$n) {
						foreach($main->postvar as $key => $value) {
							if($key != "name" && $key != "number_months") {
								if($n) {
									$additional .= ",";	
								}
								$additional .= $key."=".$value;
								$n++;
							}
						}
						$status = BILLING_CYCLE_STATUS_INACTIVE;
						if ($main->postvar['status'] == 'on') {
							$status = BILLING_CYCLE_STATUS_ACTIVE;
						}
						$sql = "INSERT INTO `<PRE>billing_cycles` (name, number_months, status) VALUES('{$main->postvar['name']}', '{$main->postvar['number_months']}' , '$status')";
						$db->query($sql);						
						$main->errors("Biiling cycle has been added!");
					}
				}
				
				for($i = 1; $i<=48; $i++) {
					$values[] = array($i,$i);
				}						
				$array['NUMBER_MONTHS'] = $main->dropDown("number_months", $values, '');				
				$array['STATUS'] = $main->createCheckbox('', 'status');
				echo $style->replaceVar("tpl/billing/addbillingcycle.tpl", $array);
				break;
				
			case 'edit':
				if(isset($main->getvar['do'])) {
					$query = $db->query("SELECT * FROM `<PRE>billing_cycles` WHERE `id` = '{$main->getvar['do']}'");
					if($db->num_rows($query) == 0) {
						echo "That billing cycle doesn't exist!";	
					} else {
						if($_POST) {
							foreach($main->postvar as $key => $value) {
								if($value == "" && !$n && $key != "admin") {
									$main->errors("Please fill in all the fields!");
									$n++;
								}
							}
							if(!$n) {
								foreach($main->postvar as $key => $value) {
									if($key != "name" && $key != "number_months") {
										if($n) {
											$additional .= ",";	
										}
										$additional .= $key."=".$value;
										$n++;
									}
								}
								$status = BILLING_CYCLE_STATUS_INACTIVE;
								if ($main->postvar['status'] == 'on') {
									$status = BILLING_CYCLE_STATUS_ACTIVE;
								}
								
								$sql = "UPDATE `<PRE>billing_cycles` SET
										   `name` = '{$main->postvar['name']}',
										    `status` = '{$status}',
										   `number_months` = '{$main->postvar['number_months']}'										  
										   WHERE `id` = '{$main->getvar['do']}'";
								$db->query($sql);
								$main->errors('Billing cycle has been edited!');
							}
							
						}
						$data = $db->fetch_array($query);
						$array['ID'] = $data['id'];					
						$array['NAME'] = $data['name'];
						
						
						for($i = 1; $i<=48; $i++) {
							$values[] = array($i,$i);
						}						
						$array['NUMBER_MONTHS'] = $main->dropDown("number_months", $values, $data['number_months']);						
						$array['STATUS'] = $main->createCheckbox('', 'status', $data['status']);
						
						echo $style->replaceVar("tpl/billing/editbillingcycles.tpl", $array);
					}
				} else {
					$query = $db->query("SELECT * FROM `<PRE>billing_cycles`");
					if($db->num_rows($query) == 0) {
						echo "There are no billing cycles to edit!";	
					}
					else {
						echo "<ERRORS>";
						while($data = $db->fetch_array($query)) {
							echo $main->sub("<strong>".$data['name']."</strong>", '<a href="?page=billing&sub=edit&do='.$data['id'].'"><img src="'. URL .'themes/icons/pencil.png"></a>');
							$n++;
						}
					}
				}
				break;
				
			case 'delete':
				if($main->getvar['do']) {
					$db->query("DELETE FROM `<PRE>billing_cycles` WHERE `id` = '{$main->getvar['do']}'");
					$main->errors("Billing cycles has been Deleted!");		
				}
				$query = $db->query("SELECT * FROM `<PRE>billing_cycles`");
				if($db->num_rows($query) == 0) {
					echo "There are no billing cycles to delete!";	
				} else {
					echo "<ERRORS>";
					while($data = $db->fetch_array($query)) {
						echo $main->sub("<strong>".$data['name']."</strong>", '<a href="?page=billing&sub=delete&do='.$data['id'].'"><img src="'. URL .'themes/icons/delete.png"></a>');
						$n++;
					}
				}
			break;
		}
	}
}
?>
