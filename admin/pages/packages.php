<?php
/* For licensing terms, see /license.txt */

/**
	BNPanel	
	@package	Admin Area - Packages
	@author 	Jonny H
	@author 	Julio Montoya <gugli100@gmail.com> BeezNest 2010 Addon feature implemented 
	@package	bnpanel.packages	
*/
class page extends Controller {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Packages Sub Menu";
		$this->navlist[] = array("Packages", "package_add.png", "view");
		$this->navlist[] = array("Add Package", "add.png", "add");
		
	}
	
	public function description() {
		return "<strong>Managing Packages</strong><br />
		Welcome to the Package Management Area. Here you can add, edit and delete web hosting packages. Have fun :)<br />
		To get started, choose a link from the sidebar's SubMenu.";	
	}
	
	public function content() { # Displays the page 
		global $main, $style, $db, $billing, $package,$addon, $server;
		require_once LINK.'validator.class.php';		
		
		switch ($main->get_variable('sub')) {
			default:								
				$asOption = array(
				    'rules' => array(
				        'name' 				=> 'required',
				        'backend' 			=> 'required',
				        'type' 				=> 'required',
				        'server' 			=> 'required'				        
				     ),			    
				    'messages' => array(				        			       
				    )
				);
				$array['json_encode'] = json_encode($asOption);							
				$oValidator = new Validator($asOption);	
				
				$result = $oValidator->validate($_POST);
				
				if (empty($result))				
				if ($_POST && $main->checkToken()) {					
					$n = 0; 
					$exist_billing_cycle = false;
					foreach($main->postvar as $key => $value) {						
						if ($main->postvar['type'] == 'paid' && $exist_billing_cycle == false) {
							if (substr($key,0,13) == "billing_cycle") {								
								$exist_billing_cycle = true;
							}	
						}						
					}
					if ($main->postvar['type'] == 'paid' && $exist_billing_cycle == false) {
						$main->errors("Please add a billing cycle first");			
						$n++;	
					}	
						
					if(!$n) {
						$package_params['name'] 		= $main->post_variable('name');
						$package_params['backend'] 		= $main->post_variable('backend');
						$package_params['description'] 	= $main->post_variable('description');
						$package_params['type'] 		= $main->post_variable('type');
						$package_params['server'] 		= $main->post_variable('server');
						$package_params['admin'] 		= $main->post_variable('admin');
						$package_params['is_hidden'] 	= $main->post_variable('hidden');
						$package_params['is_disabled'] 	= $main->post_variable('disabled');
						//$package_params['additional']	= $additional;
						$package_params['reseller'] 	= $main->postvar['reseller'];						
						$product_id  = $package->create($package_params);
												
						$billing_list = $billing->getAllBillingCycles();
						
						foreach($billing_list as $billing_id=>$value) {
							$variable_name = 'billing_cycle_'.$billing_id;
							if (isset($main->postvar[$variable_name])) {
								$params['billing_id'] 	= $billing_id;
								$params['product_id'] 	= $product_id;
								$params['amount']		= $main->postvar[$variable_name];
								$params['type'] 		= BILLING_TYPE_PACKAGE;
								
								$billing->billing_products->save($params);																	
							}
						}
						
						$query = $db->query("SELECT * FROM `<PRE>addons` WHERE status = ".ADDON_STATUS_ACTIVE);
						
						if($db->num_rows($query) > 0) {
							while($data = $db->fetch_array($query)) {		
										
								$variable_name = 'addon_'.$data['id'];
								if (isset($main->postvar[$variable_name]) && $main->postvar[$variable_name] == 'on') {
									$params['addon_id'] = $data['id'];
									$params['package_id'] = $product_id;
									$package->package_addons->save($params);
								
								}
							}						
						}
						$main->errors("Package has been added!");
						$main->redirect('?page=packages&sub=edit&msg=1');						
					}
				}
				
				$all_servers = $server->getAllServers();
				if (!empty($all_servers)) {
					$array['SERVER'] = $main->createSelect("server", $all_servers);
						
					//Addon feature added
					$array['ADDON'] = $addon->generateAddonCheckboxes();
					//finish 		
					echo $style->replaceVar("tpl/packages/addpackage.tpl", $array);
				} else {
					$main->errors('There are no servers, you need to add a Server first <a href="?page=servers&sub=add">here</a>');
					echo '<ERRORS>';
				}
				break;
			case 'view':
			case 'edit':
				if(isset($main->getvar['do'])) {
					$package_info = $package->getPackage($main->getvar['do']);
					
					if(empty($package_info)) {
						$main->errors('That package doesn\'t exist!');						
					} else {
						if($_POST && $main->checkToken() ) {
							$n = 0;
							foreach($main->postvar as $key => $value) {
								//if($value == "" && !$n && $key != "admin") {
								
								if($value == "" && !$n && $key != "admin" && substr($key,0,13) != "billing_cycle"  && substr($key,0,5) != "addon" ) {
									$main->errors("Please fill in all the fields!");
									$n++;
								}
							}
							//var_dump($n);
							if(!$n) {
								foreach($main->postvar as $key => $value) {
									if($key != "name" && $key != "backend" && $key != "description" && $key != "type" && $key != "server" && $key != "admin") {
										if($n) {
											$additional .= ",";	
										}
										$additional .= $key."=".$value;
										$n++;
									}
								}
										   
								$package_params['name'] 		= $main->postvar['name'];
								$package_params['backend'] 		= $main->postvar['backend'];
								$package_params['description'] 	= $main->postvar['description'];
								//$package_params['type'] 		= $main->postvar['type'];
								$package_params['server'] 		= $main->postvar['server'];
								$package_params['admin'] 		= $main->postvar['admin'];
								$package_params['is_hidden'] 	= $main->postvar['hidden'];
								$package_params['is_disabled'] 	= $main->postvar['disabled'];
								$package_params['additional']	= $additional;
								$package_params['reseller'] 	= $main->postvar['reseller'];						
								$package->edit($main->getvar['do'], $package_params);										
								
								//-----Adding billing cycles 
								
								//Deleting all billing_products relationship							
								$query = $db->query("DELETE FROM `<PRE>billing_products` WHERE product_id = {$main->getvar['do']} AND type='".BILLING_TYPE_PACKAGE."' ");								
								$product_id = $main->getvar['do'];
								$billing_list = $billing->getAllBillingCycles();
								foreach($billing_list as $billing_id=>$value) {
									$variable_name = 'billing_cycle_'.$billing_id;
									if (isset($main->postvar[$variable_name]) && ! empty($main->postvar[$variable_name]) ) {										
											$params['billing_id'] 	= $billing_id;
											$params['product_id'] 	= $product_id;
											$params['amount']		= $main->postvar[$variable_name];
											$params['type'] 		= BILLING_TYPE_PACKAGE;
											$billing->billing_products->save($params);																		
									}
								}					
								//-----Finish billing cycles
								
								
								//-----Adding addons cycles 
								
								//Deleting all billing_products relationship							
								
								//$query = $db->query("DELETE FROM `<PRE>package_addons` WHERE package_id = {$main->getvar['do']} ");
								
								$package->package_addons->setPrimaryKey('package_id');
								$package->package_addons->setId($main->getvar['do']);								
								$package->package_addons->delete();								
								   
								$query = $db->query("SELECT * FROM <PRE>addons");
								$product_id = $main->getvar['do'];
								if($db->num_rows($query) > 0) {
									
									//Add new relations
									while($data = $db->fetch_array($query)) {												
										$variable_name = 'addon_'.$data['id'];
										if (isset($main->postvar[$variable_name]) && ! empty($main->postvar[$variable_name]) ) {
											$params['addon_id'] = $data['id'];
											$params['package_id'] = $product_id;
											$package->package_addons->save($params);								
										}
									}						
								}								
								//-----Finish billing cycles							
								
								$main->errors("Package has been edited!");
								$main->redirect('?page=packages&sub=edit&msg=1');
							}
						}
												
						$array['TYPE'] 			= $package_info['type'];
						$array['BACKEND'] 		= $package_info['backend'];
						$array['DESCRIPTION'] 	= $package_info['description'];
						$array['NAME'] 			= $package_info['name'];
						$array['URL'] 			= $db->config('url');
						$array['ID'] 			= $package_info['id'];
						
						if($package_info['admin'] == 1) {
							$array['CHECKED'] = 'checked="checked"';	
						} else {
							$array['CHECKED'] = "";
						}
						if($package_info['reseller'] == 1) {
							$array['CHECKED2'] = 'checked="checked"';	
						} else {
							$array['CHECKED2'] = "";
						}
						if($package_info['is_hidden'] == 1) {
							$array['CHECKED3'] = 'checked="checked"';	
						} else {
							$array['CHECKED3'] = "";
						}
						if($package_info['is_disabled'] == 1) {
							$array['CHECKED4'] = 'checked="checked"';	
						} else {
							$array['CHECKED4'] = "";
						}
						
						//Getting info of the server
						$server_info = $server->getServerById($package_info['server']);
						
						if ($server_info['type'] == 'ispconfig') {
							$array['ADDITIONAL'] .='';
						} 
						$additional = explode(",", $package_info['additional']);
						$cform = array();
						
						if (!empty($additional)) {
							foreach($additional as $key => $value) {
								if (!empty($value)) {
									$me = explode("=", $value);
									if (!empty($me)) {									
										$cform[$me[0]] = $me[1];
									}
								}
							}
						}
						global $type;
						$array['FORM'] = $type->acpPedit($package_info['type'], $cform);
						
						$query = $db->query("SELECT * FROM `<PRE>servers`");
						while($data_server = $db->fetch_array($query)) {
							$values[] = array($data_server['name'], $data_server['id']);	
						}
						$array['SERVER'] = $array['THEME'] = $main->dropDown("server", $values, $data_server['server']);
						
						
						// Addon feature added						
						$sql = "SELECT addon_id FROM `<PRE>package_addons` WHERE package_id =".$package_info['id'];
						$query = $db->query($sql);		
						$myresults = array();
						while($data = $db->fetch_array($query)) {
							$myresults[$data['addon_id']]= 1;				
						}									
						$array['ADDON'] = $addon->generateAddonCheckboxes($myresults);	
						global $server;
						//Loading the server			
						
						$serverphp = $server->loadServer($package_info['server']);
						$message = '';
						$array['BACKEND_INFO'] = '-';
						
						if ($serverphp->name != 'Test' ) {
							if (isset($serverphp->status)) {	
								//Getting all client templates in ISPConfig
								$package_list = $serverphp->getAllPackageBackEnd();
								
								if (!empty($package_list)) {
									foreach($package_list as $package_item_panel) {
										if ($package_item_panel['template_id'] == $package_info['backend']) {
											$my_package_back_end = $package_item_panel;
											break;
										}							
									}		
									if (!empty($my_package_back_end)) {					
										$html_result = $serverphp->parseBackendInfo($my_package_back_end);
										$message = 'This Package is related with the Control Panel';
										$class = 'info';
										$array['BACKEND_INFO'] 			= $html_result;
									} else {								
										$message = 'This Package is not related with the Control Panel. Check Your Backend field.';
										$class = 'warning';						
										$array['BACKEND_INFO'] = 'Cannot load Package Info';
									}
								} else {
									$$class= 'warning';
									$message = 'There are problems while trying to connect to the Control Panel server, please check the logs';
								}						
							} else {
								$class = 'warning';
								$message = 'There are problems while trying to connect to the Control Panel server, please check the logs';
							}
						}						
						
						if (!empty($message)) {
							$message = $style->returnMessage($message, $class);
						}						
						$array['BACKEND_MESSAGE'] = $message;
						
						echo $style->replaceVar("tpl/packages/editpackage.tpl", $array);
					}
				} else {
					$query = $db->query("SELECT * FROM <PRE>packages");
					echo "<ERRORS>";
					
					if ($db->num_rows($query) == 0) {
						$style->showMessage('There are no packages', 'warning');						
					} else {						
						while($data = $db->fetch_array($query)) {
							echo $main->sub("<strong>".$data['name']."</strong>", '<a href="?page=packages&sub=edit&do='.$data['id'].'"><img src="'. URL .'themes/icons/pencil.png"></a>&nbsp;<a href="?page=packages&sub=delete&do='.$data['id'].'"><img src="'. URL .'themes/icons/delete.png"></a>');							
						}
					}
				}
				break;
				
			case 'delete':
				if($main->getvar['do']) {
					if ($main->checkToken()) {
						$main->getvar['do'] = intval($main->getvar['do']);
						$package->delete($main->getvar['do']);
						$db->query("DELETE FROM `<PRE>billing_products` WHERE product_id = '{$main->getvar['do']}' AND type = '".BILLING_TYPE_PACKAGE."'");
						
						//Deleting package relations
						$package->package_addons->setPrimaryKey('package_id');
						$package->package_addons->setId($main->getvar['do']);								
						$package->package_addons->delete();	
						
																
						$main->errors('Package #'.$main->getvar['do'].' has been Deleted');
						$main->redirect('?page=packages&sub=edit&msg=1');	
					}		
				}				
			break;
		}
	}
}