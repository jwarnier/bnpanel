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
		$this->navlist[] = array("View All Orders", "package_go.png", "all");
		$this->navlist[] = array("Add a New Order", "add.png", "add");
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
				        'domain_type' 		=> 'required',				        
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
				
				if ($db->config('domain_options') == DOMAIN_OPTION_SUBDOMAIN)
					$asOption['rules']['csub2'] = 'required';
				
				$array['json_encode'] = json_encode($asOption);				
				$oValidator = new Validator($asOption);				
			
				if ($_POST && $main->checkToken()) {					
					$result = $oValidator->validate($_POST);								
					if (empty($result)) {				
						
						//Creating an order		
						$params['userid'] 		= $main->postvar['user_id'];						
						$params['username'] 	= $main->postvar['username'];
						$params['password'] 	= $main->postvar['password'];						
						$params['pid'] 			= $main->postvar['package_id'];
						$params['domain'] 		= $main->postvar['domain'];
						
						$url_parts = $main->parseUrl($params['domain']);
												
						$subdomain_id = 0;
						$package_data	= $package->getPackage($params['pid']);
						$domain_correct = true;
						
						if ($main->postvar['domain_type'] == DOMAIN_OPTION_SUBDOMAIN) {
							//Is a subdomain	
							$subdomain_id 	= $main->postvar['csub2'];
							if (empty($subdomain_id)) {
								$main->errors('Subdomain is not valid');
								$domain_correct = false;
							}
						} elseif ($main->postvar['domain_type'] == DOMAIN_OPTION_DOMAIN) {
							//is a domain
							if (empty($url_parts['domain']) || empty($url_parts['extension'])) {
								$main->errors('Select a correct domain');	
								$domain_correct = false;							
							}
						}
						
						if ($domain_correct) {
							
							$params['signup'] 		= strtotime($main->postvar['created_at']);
							$params['status'] 		= $main->postvar['status'];			
							$params['additional']	= '';
							$params['subdomain_id']	= $subdomain_id;
							$params['billing_cycle_id'] = $main->postvar['billing_cycle_id'];	
							
							//Add addons
							$addon_list = $addon->getAllAddonsByBillingCycleAndPackage($main->postvar['billing_cycle_id'], $main->postvar['package_id']);
							$new_addon_list = array();			
							if (is_array($addon_list)) {																		
								foreach($addon_list as $addon_id=>$value) {																								
									$variable_name = 'addon_'.$addon_id;
									if (isset($main->postvar[$variable_name]) && ! empty($main->postvar[$variable_name]) ) {										
										$new_addon_list[] = $addon_id;				
									}															
								}
							}
							
							$params['addon_list'] = $new_addon_list;							
																			
							if (!empty($params['userid']) && !empty($params['pid'])) {
								$order_id = $order->create($params);
							}
							
							//Add addons to a new order	
							if (!empty($order_id) && is_numeric($order_id)) {
								
								$main->errors("Order #$order_id has been created");																									
								
								$send_control_panel = false;
								if ($main->postvar['status'] == ORDER_STATUS_ACTIVE) {
									
									//Creating the account in ISPconfig only if order is active									
									$send_control_panel = $order->sendOrderToControlPanel($order_id);
									if (!$send_control_panel) {
										$order->edit($order_id, ORDER_STATUS_FAILED);
									}
								}
								
								//Creating an auto Invoice
								$package_info 		= $package->getPackageByBillingCycle($main->postvar['package_id'], $main->postvar['billing_cycle_id']);
								
								$addon_serialized 	= $addon->generateAddonFee($new_addon_list, $main->postvar['billing_cycle_id'], true);
												
								$billing_info 		= $billing->getBilling($main->postvar['billing_cycle_id']);
																				
								$invoice_params['uid'] 		= $main->postvar['user_id'];
								$invoice_params['amount'] 	= $package_info['amount'];
								$invoice_params['due'] 		= strtotime($main->postvar['created_at']) + $billing_info['number_months']*30*24*60*60;
								$invoice_params['notes'] 	= 'Invoice created automatically';
								$invoice_params['addon_fee']= $addon_serialized;
								$invoice_params['status'] 	= INVOICE_STATUS_WAITING_PAYMENT;
								$invoice_params['order_id'] = $order_id;										
								$invoice_id = $invoice->create($invoice_params);
								
								if (is_numeric($invoice_id)) {
									$main->errors("Invoice #$invoice_id has been created");								
								} else {
									$main->errors("Invoice not created");
								}
								
								if ($main->postvar['status'] == ORDER_STATUS_ACTIVE && $send_control_panel == false) {
									$main->errors("There was a problem, while creating the Web package in the Control Panel, please check the logs");
								}								
							}							
							
							if (is_numeric($order_id)) {
								$main->redirect('?page=orders&sub=view&msg=1&do='.$order_id);
							} else {
								$main->redirect('?page=orders&sub=all&msg=1');
							}																	
						} else {
							$main->errors("Check domain");
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
				$array['BILLING_CYCLES']= $main->createSelect('billing_cycle_id', $new_billing_list, '', array('onchange'=>'loadPackages(this);', 'class'=>'required'));				
				$array['PACKAGES'] 		= '-';
				$array['ADDON'] 		= '-';
				$order_list = $main->getOrderStatusList();
				
				//Removing the deleted/cancelled option useless when creating an order I hope!
				unset($order_list[ORDER_STATUS_DELETED]);
				unset($order_list[ORDER_STATUS_CANCELLED]);
				unset($order_list[ORDER_STATUS_FAILED]);
				
				$array['STATUS']			= $main->createSelect('status', $order_list, '', array('class'=>'required'));				
				$array['DOMAIN_USERNAME'] 	= $main->generateUsername();
				$array['DOMAIN_PASSWORD'] 	= $main->generatePassword();	
				
				switch($db->config('domain_options')) {					
					case DOMAIN_OPTION_DOMAIN:
						$values = array(DOMAIN_OPTION_DOMAIN=>'Domain');
					break;
					case DOMAIN_OPTION_SUBDOMAIN:
						$values = array(DOMAIN_OPTION_SUBDOMAIN=>'Subdomain');
					break;
					case DOMAIN_OPTION_BOTH:
						$values = array(DOMAIN_OPTION_DOMAIN=>'Domain', DOMAIN_OPTION_SUBDOMAIN=>'Subdomain');
					break;
				}
							
    			$array['DOMAIN_TYPE'] = $main->createSelect('domain_type',$values, '', array('onchange'=>'changeDomain();'));
    			 
				if ($db->config('domain_options') == DOMAIN_OPTION_SUBDOMAIN) {
					$subdomain_list = $main->getSubDomains();
					if( empty($subdomain_list)) {
						$style->showMessage('No subdomains available. Click <a href="?page=sub&sub=add">here</a> to add new Subdomain', 'warning');								
					}
				}				
				echo $style->replaceVar("tpl/orders/add.tpl", $array);
			break;
			
			case 'change_pass':			
				if(isset($main->getvar['do'])) {
					if($_POST && $main->checkToken()) {					
						if ($main->postvar['password'] == $main->postvar['confirm']) {													
							if ($server->changePwd($main->getvar['do'], $main->postvar['password'])) {
								$main->errors("Password has been changed");
							} else {
								$main->errors("There was an error. Please try again.");
							}
						}
						$main->redirect('?page=orders&sub=view&msg=1&do='.$main->getvar['do']);			
					}
					$return_array = $order->getOrder($main->getvar['do'], false, false);										
					echo $style->replaceVar("tpl/orders/change-password.tpl", $return_array);
				}				
			break;
			case 'edit':
				if(isset($main->getvar['do'])) {
					$order_info = $order->getOrderInfo($main->getvar['do']);					
					if (is_array($order_info) && !empty($order_info )) {
						if($_POST && $main->checkToken()) {
											
							$main->postvar['pid'] 	 = $main->postvar['package_id'];								
							//Editing the Order								
							$result = $order->edit($main->getvar['do'], $main->postvar);
							if ($result) {
								//We dont update addons for the moment....
								/*
								$addon_list = $addon->getAllAddonsByBillingCycleAndPackage($main->postvar['billing_cycle_id'], $main->postvar['package_id']);																	
								$new_addon_list = array();																
								foreach($addon_list as $addon_id=>$value) {																								
									$variable_name = 'addon_'.$addon_id;
									if (isset($main->postvar[$variable_name]) && ! empty($main->postvar[$variable_name]) ) {										
										$new_addon_list[] = $addon_id;				
									}															
								}
								//Updating addons of an Order													
								$addon->updateAddonOrders($new_addon_list, $main->postvar['order_id']);		*/
								$main->errors("Order has been edited!");
								$main->redirect('?page=orders&sub=view&do='.$main->getvar['do'].'&msg=1');
							} else {
								$main->errors("Cannot update Order #".$main->getvar['do']." please check the connection with the Control Panel");
								$main->redirect('?page=orders&sub=all&msg=1');
							}						
						}
					}				
				} else {
					echo "That order doesn't exist!";	
				}	
						
				$return_array = $order->getOrder($main->getvar['do'], false, false);
				
				$return_array['PACKAGE_ID'] = intval($order_info['pid']);
				
				$return_array['INVOICE_LIST'] = $order->showAllInvoicesByOrderId($main->getvar['do']);
				
				$order_info 	= $order->getOrderInfo($main->getvar['do']);				
				$package_info 	= $package->getPackage($order_info['pid']);		
				$site_info = false;		
				if (!empty($package_info)) {		
					$serverphp		= $server->loadServer($package_info['server']); # Create server class
					if ($serverphp != false) {
						$site_info 		= $serverphp->getSiteStatus($main->getvar['do']);
						$user_status	= $serverphp->getUserStatus($main->getvar['do']);
					}
				}				
				
				if ($site_info != false) { 									
					if($site_info['active'] == 'y') {	
						$return_array['SITE_STATUS_CLASS'] = 'success';
						$return_array['SITE_STATUS_INFO'] .= 'Status: Active <br />';
					} else {
						$return_array['SITE_STATUS_CLASS'] = 'warning';
						$return_array['SITE_STATUS_INFO'] .= 'Status: Inactive <br />';					
					}
					
					$return_array['SITE_STATUS'] = '<strong>Site exists in Control Panel</strong><br />';					
					$return_array['SITE_STATUS_INFO'] .= 'Registered Domain: '.$site_info['domain'].' <br /> Domain id: '.$site_info['domain_id'].' <br /> Document root: '.$site_info['document_root'];					
					
				} else {
					$return_array['SITE_STATUS_CLASS'] = 'warning';
					$return_array['SITE_STATUS'] = '<strong>Site doesn\'t exist in Control Panel</strong><br />';		
					$return_array['SITE_STATUS'] .= 'The current order is not registered in the Control Panel Server. <br />To send this order to the Control Panel just change the status to Active';
					$return_array['SITE_STATUS_INFO'] = '';
				}	
				
				if ($user_status != false) {					
					$return_array['SITE_STATUS_INFO'] .= '<br /><br /><b>Account Information</b><br /> User '.$order_info['username'].' registered in Control Panel #'.$user_status['client_id'];
				} else {
					$return_array['SITE_STATUS_INFO'] .= '<br /><br /><b>Account Information</b><br /> User '.$order_info['username'].' not registered in Control Panel';
				}
				$return_array['DOMAIN'] = $order_info['real_domain'];
				
				echo $style->replaceVar("tpl/orders/edit.tpl", $return_array);			
			break;			
			case 'view':				
				if(isset($main->getvar['do'])) {					
					$return_array = $order->getOrder($main->getvar['do'], true);
					$return_array['INVOICE_LIST'] = $order->showAllInvoicesByOrderId($main->getvar['do']);											
					echo $style->replaceVar("tpl/orders/view-admin.tpl", $return_array);					
				}				
			break;			
			case 'add_invoice':			
				$asOption = array(
				    'rules' => array(
				        'due' 				=> 'required',
				        'status' 			=> 'required'				        
				     ),			    
				    'messages' => array(				        			       
				    )
				);				
				$return_array['json_encode'] = json_encode($asOption);				
				$oValidator = new Validator($asOption);			
				
				if(isset($main->getvar['do'])) {									
					$order_info = $order->getOrderInfo($main->getvar['do']);
					$billing_id = $order_info['billing_cycle_id'];
							 								
					if($_POST && $main->checkToken())  {						
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
										
							$invoice_id = $invoice->create($invoice_params);							
							
							$main->errors('Invoice created #'.$invoice_id);												
						} else {
							$main->errors('Please fill all the fields');
						}	
						$main->redirect('?page=orders&sub=all&msg=1');					
					} else {					
						$user_info  					= $user->getUserById($order_info['userid']);						
						$return_array['USER'] 			= $user->formatUsername($user_info['firstname'], $user_info['lastname']);
						$return_array['DOMAIN'] 		= $order_info['real_domain'];					
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
						//$return_array['PACKAGES']   	=  $main->createSelect('package_id', $package_list, $order_info['pid'], array('onchange'=>'loadAddons(this);','class'=>'required'));									
						$return_array['PACKAGES']  		= $package_list[$order_info['pid']];
						$return_array['PACKAGE_ID']  	= $order_info['pid'];
						//$return_array['DUE'] 			= date('Y-m-d', time() + $billing_info['number_months']*30*24*60*60);					
						$return_array['DUE'] 			= date('Y-m-d');
						$return_array['ID'] 			= $main->getvar['do'];
						$invoice_status 				= $main->getInvoiceStatusList();
						//No need to add a deleted invoices!! daaa
						unset($invoice_status[INVOICE_STATUS_DELETED]);
						$return_array['STATUS'] 		= $main->createSelect('status', $invoice_status,'', array('class'=>'required'));
						$return_array['INVOICE_LIST'] 	= $order->showAllInvoicesByOrderId($main->getvar['do']);
														
						echo $style->replaceVar('tpl/invoices/addinvoice.tpl', $return_array);	
					}			
					
				} else {
					$main->errors('You need an order before create an invoice!');
				}			
			break;		
			case 'delete':			
				if (isset($main->getvar['do'])) { 
					$result = $order->delete($main->getvar['do']);
				} else {
					$main->redirect('?page=orders&sub=all&msg=1');								
				}		
				if (isset($main->getvar['confirm']) && $main->getvar['confirm'] == 1) {
					if ($result == true) {
						$main->errors('The order #'.$main->getvar['do'].' has been  deleted!');						
						//$main->redirect("?page=orders&sub=all");
					} else {
						$main->errors("Order cannot be deleted. There is a problem please check the logs of Order #".$main->getvar['do']);
					}
					echo '<ERRORS>';
				} 
				$main->redirect('?page=orders&sub=all&msg=1');	
			break;
			default :	
			case 'all':									
				$per_page = $db->config('rows_per_page');
				$count_sql = "SELECT count(*)  as count FROM ".$order->getTableName()." WHERE status <> '".ORDER_STATUS_DELETED."'";
				$result_max = $db->query($count_sql);		
				$count = $db->fetch_array($result_max);
				$count = $count['count'];
				
				if (!empty($count)) {	
					$quantity = ceil($count / $per_page);
					$return_array['COUNT'] = $quantity;				
					echo $style->replaceVar("tpl/orders/admin-page.tpl", $return_array);
				} else {
					$main->errors('No orders available');
					echo '<ERRORS>';
				}				
			break;			
		}
	}
}