<?php
/* For licensing terms, see /license.txt */
/**
 * BNPanel
 * 
 * @author Julio Montoya <gugli100@gmail.com> BeezNest 2010
 *  
 */

if(THT != 1){die();}

class page {
	
	public function __construct() {
		$this->navtitle = "Order Sub Menu";
		$this->navlist[] = array("View all orders", "package_go.png", "all");
		$this->navlist[] = array("Add a new order", "add.png", "add");
	}
	
	public function description() {
		return "<strong>Managing Orders</strong><br />
		Welcome to the Invoice Management Area. Here you can add, edit and delete Invoices. <br />
		To get started, choose a link from the sidebar's SubMenu.";	
	}
		
	public function content() {		
		global $style, $server, $db, $main, $invoice,$addon, $order, $billing, $currency, $package, $user;
		
		if(isset($_GET['iid']) && isset($_GET['pay'])){			
			$invoice->set_paid($_GET['iid']);
			echo "<span style='color:green'>Invoice #{$_GET['iid']} marked as paid. <a href='index.php?page=invoices&iid={$_GET['iid']}&unpay=true'>Undo this action</a></span>";
		} elseif(isset($_GET['iid']) && isset($_GET['unpay'])){		
			$invoice->set_unpaid($_GET['iid']);
			echo "<span style='color:red'>Invoice {$_GET['iid']} marked as unpaid. <a href='index.php?page=invoices&iid={$_GET['iid']}&pay=true'>Undo this action</a></span>";
		}
		require_once LINK.'validator.class.php';
		
		switch($main->getvar['sub']) {					
			case 'add':					
			
				$asOption = array(
				    'rules' => array(
				        'domain' 			=> 'required',
				        'billing_cycle_id' 	=> 'required',
				        'package_id' 		=> 'required',
				        'status' 			=> 'required',
				        'created_at' 		=> 'required',
				        'username' 			=> 'required',
				        'password' 			=> 'required'			            
				     ),			    
				    'messages' => array(
				        //'domain' => array( 'required' => 'Domain is required'),			       
				    )
				);				
				$array['json_encode'] = json_encode($asOption);
				
				$oValidator = new Validator($asOption);
				
			
				if($_POST) {	
					$result = $oValidator->validate($_POST);
					if (empty($result)) {				
						
						//Creating an order		
						$params['userid'] 		= $main->postvar['user_id'];
						
						$params['username'] 	= $main->postvar['username'];
						$params['password'] 	= $main->postvar['password'];
						
						$params['domain'] 		= $main->postvar['domain'];
						$params['pid'] 			= $main->postvar['package_id'];
						$params['signup'] 		= strtotime($main->postvar['created_at']);
						$params['status'] 		= $main->postvar['status'];
						$params['additional']	= '';
						$params['billing_cycle_id'] = $main->postvar['billing_cycle_id'];
						
						if (!empty($params['userid']) && !empty($params['pid'])) {
							$order_id = $order->create($params);	
						}
						//Add addons to a new order	
						if (!empty($order_id) && is_numeric($order_id)) {
							//Add addons
							$addon_list = $addon->getAllAddonsByBillingCycleAndPackage($main->postvar['billing_cycle_id'], $main->postvar['package_id']);
							$new_addon_list = array();
																				
							foreach($addon_list as $addon_id=>$value) {																								
								$variable_name = 'addon_'.$addon_id;
								//var_dump($variable_name);
								if (isset($main->postvar[$variable_name]) && ! empty($main->postvar[$variable_name]) ) {										
									$new_addon_list[] = $addon_id;				
								}															
							}												
							$order->addAddons($order_id, $new_addon_list);
							$main->errors("Order has been added!");
						} else {
							$main->errors("There was a problem!");
						}
					} else {
						//$main->errors("There was a problem!");
					}
																		
				}
				$array['CREATED_AT'] 	= date('Y-m-d');
				$billing_list = $billing->getAllBillingCycles();
				$new_billing_list = array();
				foreach($billing_list as $billing_item) {
					$new_billing_list[$billing_item['id']] =$billing_item['name']; 
				}
				$array['BILLING_CYCLES']= $main->createSelect('billing_cycle_id', $new_billing_list, array('onchange'=>'loadPackages(this);','class'=>'required'));				
				$array['PACKAGES'] 		= '-';
				$array['ADDON'] 		= '-';
				$array['STATUS'] 		= $main->createSelect('status', $main->getOrderStatusList(),'', array('class'=>'required'));
				
				$array['DOMAIN_USERNAME'] = $main->generateUsername();
				$array['DOMAIN_PASSWORD'] = $main->generatePassword();
				
					
				echo $style->replaceVar("tpl/orders/add.tpl", $array);
			break;
			case 'change_pass':			
				if(isset($main->getvar['do'])) {
					if($_POST) {						
						if ($main->postvar['password'] == $main->postvar['confirm']) {
							$server->changeOrderPassword($main->getvar['do'], $main->postvar['password']);
						}
					}
					$return_array = $order->getOrder($main->getvar['do'], false, false);					
					echo $style->replaceVar("tpl/orders/change-password.tpl", $return_array);
				}
				
			break;
			case 'edit':
				if(isset($main->getvar['do'])) {
					$order_info = $order->getOrderInfo($main->getvar['do']);
					if (is_array($order_info) && !empty($order_info )) {
						if($_POST) {
							
							foreach($main->postvar as $key => $value) {
								//if($value == "" && !$n && $key != "admin") {
								
								/*if($value == "" && !$n && $key != "admin" && substr($key,0,13) != "billing_cycle"  && substr($key,0,5) != "addon" ) {
									$main->errors("Please fill in all the fields!");
									$n++;
								}*/
							}							
							if(!$n) {			
								$main->postvar['signup'] = strtotime($main->postvar['created_at']);
								$main->postvar['pid'] 	 = $main->postvar['package_id'];
								
								//Editing the Order								
								$order->edit($main->getvar['do'], $main->postvar);
								$addon_list = $addon->getAllAddonsByBillingCycleAndPackage($main->postvar['billing_cycle_id'], $main->postvar['package_id']);
																
								$new_addon_list = array();																
								foreach($addon_list as $addon_id=>$value) {																								
									$variable_name = 'addon_'.$addon_id;
									//var_dump($variable_name);
									if (isset($main->postvar[$variable_name]) && ! empty($main->postvar[$variable_name]) ) {										
										$new_addon_list[] = $addon_id;				
									}															
								}
								//Updating addons of an Order													
								$addon->updateAddonOrders($new_addon_list, $main->postvar['order_id'], true);		
								$main->errors("Order has been edited!");
								
								if ($main->postvar['status'] == ORDER_STATUS_DELETED) {
									$main->redirect('?page=orders&sub=all');	
								}
							}
						}
					} else {
						echo "That order doesn't exist!";	
					}			
					$return_array = $order->getOrder($main->getvar['do'], false, false);
					$return_array['INVOICE_LIST'] = $order->showAllInvoicesByOrderId($main->getvar['do']);					
					echo $style->replaceVar("tpl/orders/edit.tpl", $return_array);
				}
			break;			
			case 'view':				
				if(isset($main->getvar['do'])) {					
					$return_array = $order->getOrder($main->getvar['do'], true);	
					$return_array['INVOICE_LIST'] = $order->showAllInvoicesByOrderId($main->getvar['do']);											
					echo $style->replaceVar("tpl/orders/view.tpl", $return_array);					
				}				
			break;			
			case 'add_invoice':			
			
				$asOption = array(
				    'rules' => array(
				        'due' 				=> 'required',
				        'status' 			=> 'required',
				        'package_id' 		=> 'required'
				     ),			    
				    'messages' => array(
				        			       
				    )
				);				
				$return_array['json_encode'] = json_encode($asOption);
				
				$oValidator = new Validator($asOption);
				
				
				if(isset($main->getvar['do'])) {											
					$order_info = $order->getOrderInfo($main->getvar['do']);
					$billing_id = $order_info['billing_cycle_id'];
															
					if($_POST) {
						$result = $oValidator->validate($_POST);
						if (empty($result)) {					
							$due 		= strtotime($main->postvar['due']);
							$notes		= $main->postvar['notes'];
							$package_id	= $main->postvar['package_id'];
							$status		= $main->postvar['status'];
							
							$addong_list = $addon->getAllAddonsByBillingCycleAndPackage($billing_id, $package_id);							
							
							$new_addon_list = array();																						
							foreach($addong_list as $addon_id=>$value) {																								
								$variable_name = 'addon_'.$addon_id;
								//var_dump($variable_name);
								if (isset($main->postvar[$variable_name]) && ! empty($main->postvar[$variable_name]) ) {										
									$new_addon_list[] = $addon_id;				
								}															
							}						
							$amount = 0;								
							$addon_serialized = $addon->generateAddonFee($new_addon_list, $billing_id, true);
							
							$package_info = $package->getPackageByBillingCycle($package_id, $billing_id);
																			
												
							$invoice_params['uid'] 		= $order_info['userid'];
							$invoice_params['amount'] 	= $package_info['amount'];
							$invoice_params['due'] 		= $due;
							$invoice_params['notes'] 	= $notes;
							$invoice_params['addon_fee']= $addon_serialized;
							$invoice_params['status'] 	= $status;
							$invoice_params['order_id'] = $main->getvar['do'];
										
							$invoice_id = $invoice->create($invoice_params, false);
			
							$invoice->create($invoice_params);
							
							$main->errors("Invoice created!");
							//$main->redirect("?page=invoices&sub=all");
						} else {
							$main->errors("Please fill all the fields");
						}						
					}
					
					$user_info  =  $user->getUserById($order_info['userid']);				
					
					$return_array['USER'] 			= $user_info['firstname'].' '.$user_info['lastname'];
					$return_array['DOMAIN'] 		= $order_info['domain'];					
					$billing_info 					= $billing->getBilling($billing_id);
					$return_array['BILLING_CYCLES'] = $billing_info ['name'];
					$return_array['BILLING_ID'] 	= $billing_id;
					
					$addon_list = $addon->getAddonsByPackage($billing_id);
					$result 	= $addon->showAllAddonsByBillingCycleAndPackage($billing_id, $order_info['pid'], array_flip($order_info['addons']));
					
					$return_array['ADDON'] 	=  $result['html'];

					$packages = $package->getAllPackagesByBillingCycle($billing_id);
										
			   		$package_list = array();
			   		
					foreach($packages as $package) {
						$package_list[$package['id']] = $package['name'].' - '.$currency->toCurrency($package['amount']);				
					}			
					$return_array['PACKAGES']  		=  $main->createSelect('package_id', $package_list, $order_info['pid'], array('onchange'=>'loadAddons(this);','class'=>'required'));									
					$return_array['DUE'] 			= date('Y-m-d');					
					$return_array['ID'] 			= $main->getvar['do'];
					$invoice_status 				= $main->getInvoiceStatusList();
					$return_array['STATUS'] 		= $main->createSelect('status', $invoice_status,'', array('class'=>'required'));
					$return_array['INVOICE_LIST'] 	= $order->showAllInvoicesByOrderId($main->getvar['do']);
													
					echo $style->replaceVar("tpl/invoices/addinvoice.tpl", $return_array);					
				
				} else {
					$main->errors("You need an order before create an invoice!");
				}			
			break;		
			case 'delete':			
				if (isset($main->getvar['do'])) { 
					$order->delete($main->getvar['do']);
								
				} else {
					$main->redirect("?page=orders&sub=all");										
				}		
				if (isset($main->getvar['confirm']) && $main->getvar['confirm'] == 1) {
					$main->errors("The order #".$main->getvar['do']." has been  deleted!");
				}		
			default :	
			case 'all':									
				$per_page = $db->config('rows_per_page');
				$count_sql = "SELECT count(*)  as count FROM ".$order->getTableName()." WHERE status <> '".ORDER_STATUS_DELETED."'";
				$result_max = $db->query($count_sql);		
				$count = $db->fetch_array($result_max);
				$count = $count['count'];					
				$quantity = ceil($count / $per_page);
				$return_array['COUNT'] = $quantity;
				
				echo $style->replaceVar("tpl/orders/admin-page.tpl", $return_array);				
			break;			
		}
	}
}