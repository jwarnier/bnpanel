<?php
/* For licensing terms, see /license.txt */

/** 
 * @author 	Julio Montoya <gugli100@gmail.com>	BeezNest 2010	
 */

class page extends Controller {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Billing Sub Menu";
		$this->pagename = 'billing';		
		$this->navlist[] = array("List All Billing cycles", "package_add.png", "listing");
		$this->navlist[] = array("Add Billing cycle", "add.png", "add");
	}
	
	public function description() {
		return "<strong>Managing Billing cycles</strong><br />
				Welcome to the Package Management Area. Here you can add, edit and delete web hosting packages. Have fun :)<br />
				To get started, choose a link from the sidebar's SubMenu.";	
	}	
	
	public function add() {
		global $main, $style, $billing;
		
		if($_POST && $main->checkToken()) {
			$n = 0;
			foreach($main->postvar as $key => $value) {
				if($value == "" && !$n && $key != "admin") {
					$main->errors("Please fill in all the fields!");
					$n++;
				}
			}
			if(!$n) {
				$additional = '';
				foreach($main->postvar as $key => $value) {
					if($key != "name" && $key != "number_months") {
						if($n) {
							$additional .= ",";	
						}
						$additional .= $key."=".$value;
						$n++;
					}
				}
				
				if ($main->post_variable('status') == 'on') {						
					$main->postvar['status'] = BILLING_CYCLE_STATUS_ACTIVE;
				} else {
					$main->postvar['status'] = BILLING_CYCLE_STATUS_INACTIVE;
				}
				
				//$billing_list = $billing->getAllBillings();
																							
				$billing->create($main->postvar);					
				$main->errors("Billing cycle has been added!");
				$main->redirect('?page=billing&sub=listing&msg=1');
			}
		}
		
		for($i = 1; $i<=MAX_NUMBER_MONTHS; $i++) {
			$values[] = array($i,$i);
		}						
		$array['NUMBER_MONTHS'] = $main->dropDown("number_months", $values, '');				
		$array['STATUS'] = $main->createCheckbox('', 'status');
		$this->replaceVar("billing/add.tpl", $array);
	}
	
	public function edit() {
		global $main, $style, $db, $billing;
		if(isset($main->getvar['do'])) {
			$main->getvar['do'] = intval($main->getvar['do']);
			$query = $db->query("SELECT * FROM `<PRE>billing_cycles` WHERE `id` = '{$main->getvar['do']}'");
			if($db->num_rows($query) == 0) {
				echo "That billing cycle doesn't exist!";	
			} else {
				if($_POST && $main->checkToken()) {
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
						if ($main->postvar['status'] == 'on') {							
							$main->postvar['status'] = BILLING_CYCLE_STATUS_ACTIVE;
						} else {
							$main->postvar['status'] = BILLING_CYCLE_STATUS_INACTIVE;
						}
						$billing->edit($main->getvar['do'],$main->postvar);								
						$main->errors('Billing cycle has been edited!');
						$main->redirect('?page=billing&sub=listing&msg=1');
					}							
				}
				
				$data = $db->fetch_array($query);
				$array['ID'] = $data['id'];					
				$array['NAME'] = $data['name'];
										
				for($i = 1; $i<=MAX_NUMBER_MONTHS; $i++) {
					$values[] = array($i,$i);
				}						
				$array['NUMBER_MONTHS'] = $main->dropDown("number_months", $values, $data['number_months']);						
				$array['STATUS'] = $main->createCheckbox('', 'status', $data['status']);
				
				$this->replaceVar("tpl/billing/edit.tpl", $array);
				
			}
		}
	}
		
	public function delete() {
		global $main, $billing;			
		if($main->getvar['do'] && $main->checkToken()) {
			$billing->setId($main->getvar['do']);
			$billing->delete();
			$main->errors("Billing cycle #{$main->getvar['do']} has been deleted!");
			$main->redirect('?page=billing&sub=listing&msg=1');	
		}
	}
	
	/**
	 * Experimental changes
	 * 
	 * */
	public function listing() {
		global $main, $style, $db, $billing;		
		$query = $db->query("SELECT * FROM `<PRE>billing_cycles`");
		if($db->num_rows($query) == 0) {
			$this->content = "There are no billing cycles to edit!";	
		} else {
			echo "<ERRORS>";
			while($data = $db->fetch_array($query)) {
				$this->content .= $main->sub('<strong><a href="?page=billing&sub=show&do='.$data['id'].'">'.$data['name']."</a></strong>", '<a href="?page=billing&sub=edit&do='.$data['id'].'"><img src="'. URL .'themes/icons/pencil.png"></a>&nbsp;<a href="?page=billing&sub=delete&do='.$data['id'].'"><img src="'. URL .'themes/icons/delete.png"></a>');						
				
			}
		}		
	}
	
	public function show() {
		global $style, $main, $billing;		
		if ($main->getvar['do']) {
			$result = $billing->find($main->getvar['do']);			
			$array['id'] 			= $result->id;
			$array['number_months'] = $result->number_months;
			$array['name'] 			= $result->name;
			$array['status'] 		= ($result->status)? 'Active': 'Inactive';
			$this->replaceVar("billing/show.tpl", $array);
		}
	}			
	
	public function content() { # Displays the page 
		//default page		
	}
}