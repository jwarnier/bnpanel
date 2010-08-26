<?php
/* For licensing terms, see /license.txt */
/**
			
	@author 	Julio Montoya <gugli100@gmail.com> BeezNest 2010
	@package	tht.addons	
*/

//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Addons Sub Menu";
		
		$this->navlist[] = array("View All Addons", "package_go.png", "view");
		$this->navlist[] = array("Add addons", "add.png", "add");		
	}
	
	public function description() {
		return "<strong>Managing Addons</strong><br />
		Welcome to the Addons Management Area. Here you can add, edit and delete web hosting Addons. Have fun :)<br />
		To get started, choose a link from the sidebar's SubMenu.";	
	}
	
	public function content() {
		require_once LINK.'validator.class.php';
		 
		# Displays the page 
		global $main, $style, $db, $billing, $addon;
		
		switch($main->getvar['sub']) {
			default:
			
				$asOption = array(
				    'rules' => array(
				        'name' 		=> 'required'			        
				     ),			    
				    'messages' => array(				        			       
				    )
				);
				
				$array['json_encode'] = json_encode($asOption);							
				$oValidator = new Validator($asOption);					
				$result = $oValidator->validate($_POST);
				
				if($_POST && $main->checkToken()) {	
									
					if (empty($result)) {													
						if ($main->postvar['status'] == 'on') {
							$main->postvar['status'] = ADDON_STATUS_ACTIVE;
						} else {
							$main->postvar['status'] = ADDON_STATUS_INACTIVE;
						}
  
					  	//Chamilo install
						if ($main->postvar['mandatory'] == 'on') {
							$main->postvar['mandatory'] = 1;
						} else {
							$main->postvar['mandatory'] = 0;
						}
						  		
						//Chamilo install
						if ($main->postvar['install_package'] == 'on') {
							$main->postvar['install_package'] = 1;
						} else {
							$main->postvar['install_package'] = 0;
						}
						
						//Addon creation
						$product_id = $addon->create($main->postvar);					
						$billing_list = $billing->getAllBillings();
						$billing_cycle_result = '';			
									
						foreach($billing_list as $billing_item) {
							$variable_name = 'billing_cycle_'.$billing_item['id'];
							if (isset($main->postvar[$variable_name])) {									
								$addon->createPackageAddons($billing_item['id'], $product_id, $main->postvar[$variable_name], BILLING_TYPE_ADDON);					
							}													
						}
						$main->errors('Addon has been added!');
						$main->redirect('?page=addons&sub=edit&msg=1');
												
					}
				}
				
				$billing_cycle_result 	= $billing->generateBillingInputs();
				$array['BILLING_CYCLE'] = $billing_cycle_result;
				$array['STATUS'] 		= $main->createCheckbox('', 'status');
				$array['INSTALL_PACKAGE']= $main->createCheckbox('', 'install_package');
				$array['MANDATORY'] 	= $main->createCheckbox('', 'mandatory');
	
				//----- Finish billing cycle					
				echo $style->replaceVar("tpl/addons/add.tpl", $array);
			break;
			case 'view':
			case 'edit':
				if(isset($main->getvar['do'])) {
															
					$query = $db->query("SELECT * FROM `<PRE>addons` WHERE id = '{$main->getvar['do']}'");
					
					if($db->num_rows($query) == 0) {
						echo "That Addon doesn't exist!";	
					} else {
						if($_POST && $main->checkToken()) {				
												
							if(!$n) {								
									
								if ($main->postvar['status'] == 'on') {
									$main->postvar['status'] = ADDON_STATUS_ACTIVE;
								} else {
									$main->postvar['status'] = ADDON_STATUS_INACTIVE;
								}
															
		  
							  	//Chamilo install
								if ($main->postvar['mandatory'] == 'on') {
									$main->postvar['mandatory'] = 1;
								} else {
									$main->postvar['mandatory'] = 0;
								}						
								
								
								if ($main->postvar['install_package'] == 'on') {
									$main->postvar['install_package'] = 1;
								} else {
									$main->postvar['install_package'] = 0;
								}
								
								
								//Editing addon											
								$addon->edit($main->getvar['do'], $main->postvar);
								
								//-----Adding billing cycles 
								
								//Deleting all billing_products relationship							
								$db->query("DELETE FROM `<PRE>billing_products` WHERE product_id = {$main->getvar['do']} AND type='".BILLING_TYPE_ADDON."' ");
								   								
								$billing_list = $billing->getAllBillingCycles();
								
								$product_id = $main->getvar['do'];
								$billing_cycle_result = '';
									
								//Add new relations
								foreach($billing_list as $data) {												
									$variable_name = 'billing_cycle_'.$data['id'];								
									if (isset($main->postvar[$variable_name]) && !empty($main->postvar[$variable_name]) ) {										
										$addon->createPackageAddons($data['id'], $product_id, $main->postvar[$variable_name], BILLING_TYPE_ADDON);														
									}
								}						
							
								//-----Finish billing cycles						
								$main->errors("Package #{$main->getvar['do']} has been edited!");
								$main->redirect('?page=addons&sub=edit&msg=1');
							}
						}
						
						$data = $db->fetch_array($query, 'ASSOC');
						
						$array['BACKEND'] 		= $data['backend'];
						$array['DESCRIPTION']	= $data['description'];						
						$array['STATUS'] 		= $main->createCheckbox('', 'status', $data['status']);							
						$array['INSTALL_PACKAGE']= $main->createCheckbox('', 'install_package', $data['install_package']);
						$array['MANDATORY']		= $main->createCheckbox('', 'mandatory', $data['mandatory']);											
						$array['NAME'] 			= $data['name'];
						
						$array['ID'] = $data['id'];
						
						
						//----- Adding billing cycle						
						$sql = "SELECT billing_id, b.name, amount FROM `<PRE>billing_cycles`  b INNER JOIN `<PRE>billing_products` bp on (bp.billing_id = b.id) WHERE product_id =".$data['id']." AND bp.type = '".BILLING_TYPE_ADDON."' ";
						$query = $db->query($sql);		
						
						while($data = $db->fetch_array($query)) {
							$myresults [$data['billing_id']] = $data['amount'];				
						}
						
						$billing_cycle_result = $billing->generateBillingInputs($myresults);
						
						$array['BILLING_CYCLE'] = $billing_cycle_result;						
						
						//----- Finish billing cycle						
						
						echo $style->replaceVar('tpl/addons/edit.tpl', $array);
					}
				} else {
					$query = $db->query("SELECT * FROM <PRE>addons");
					if($db->num_rows($query) == 0) {
						$style->showMessage('There are no Addons available');	
					} else {
						echo "<ERRORS>";
						while($data = $db->fetch_array($query)) {
							echo $main->sub("<strong>".$data['name']."</strong>", '<a href="?page=addons&sub=edit&do='.$data['id'].'"><img src="'. URL .'themes/icons/pencil.png"></a>&nbsp;<a href="?page=addons&sub=delete&do='.$data['id'].'"><img src="'. URL .'themes/icons/delete.png"></a>');
							$n++;
						}
					}
				}
				break;
				
			case 'delete':
				if($main->getvar['do'] && $main->checkToken()) {
					$addon->delete($main->getvar['do']);
					$main->errors("The Addon has been deleted");
					$main->redirect('?page=addons&sub=edit&msg=1');
				}				
			break;
		}
	}
}