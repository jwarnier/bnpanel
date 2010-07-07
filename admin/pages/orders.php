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
		global $style, $db, $main, $invoice,$addon, $order, $billing, $currency, $package, $user;
		
		if(isset($_GET['iid']) && isset($_GET['pay'])){			
			$invoice->set_paid($_GET['iid']);
			echo "<span style='color:green'>Invoice #{$_GET['iid']} marked as paid. <a href='index.php?page=invoices&iid={$_GET['iid']}&unpay=true'>Undo this action</a></span>";
		} elseif(isset($_GET['iid']) && isset($_GET['unpay'])){		
			$invoice->set_unpaid($_GET['iid']);
			echo "<span style='color:red'>Invoice {$_GET['iid']} marked as unpaid. <a href='index.php?page=invoices&iid={$_GET['iid']}&pay=true'>Undo this action</a></span>";
		}
		
		switch($main->getvar['sub']) {						
			case 'add':
				if($_POST) {
					$signup 		= strtotime($main->postvar['created_at']);
					$user_id		= $main->postvar['user_id'];
					$username		= "";
					$domain			= $main->postvar['domain'];
					$package_id		= $main->postvar['package_id'];
					$status			= $main->postvar['status'];
					$additional 	= '';
					$billing_cycle_id = $main->postvar['billing_cycle_id'];
					 
					$order_id = $order->create($user_id, $username, $domain, $package_id, $signup, $status, $additional, $billing_cycle_id);
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
					} else {
						
					}
															
				}
				$array['CREATED_AT'] 	= date('Y-m-d');
				$billing_list = $billing->getBillingCycles();
				$new_billing_list = array();
				foreach($billing_list as $billing_item) {
					$new_billing_list[$billing_item['id']] =$billing_item['name']; 
				}
				
				$array['BILLING_CYCLES']= $main->createSelect('billing_cycle_id', $new_billing_list, '', 1,'', array('onchange'=>'loadPackages(this);'));
				
				$array['PACKAGES'] 		= '-';
				$array['ADDON'] 		= '-';
				$array['STATUS'] 		= $main->createSelect('status', $main->getOrderStatusList());
					
				echo $style->replaceVar("tpl/orders/add.tpl", $array);
			break;
			case 'edit':
				if(isset($main->getvar['do'])) {
					$query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$main->getvar['do']}'");
					if($db->num_rows($query) == 0) {
						echo "That order doesn't exist!";	
					} else {						
						if($_POST) {
							
							foreach($main->postvar as $key => $value) {
								//if($value == "" && !$n && $key != "admin") {
								
								/*if($value == "" && !$n && $key != "admin" && substr($key,0,13) != "billing_cycle"  && substr($key,0,5) != "addon" ) {
									$main->errors("Please fill in all the fields!");
									$n++;
								}*/
							}							
							if(!$n) {
								//var_dump($main->postvar);exit;
								$signup = strtotime($main->postvar['created_at']);
								
								$update_sql = "UPDATE `<PRE>user_packs` SET
										  		`domain` = '{$main->postvar['domain']}',
										   		`signup` = '{$signup}',
										   		`status` = '{$main->postvar['status']}',
										   		`additional` = '{$main->postvar['additional']}',
										   		`billing_cycle_id` = '{$main->postvar['billing_cycle_id']}',
										   		`pid` = '{$main->postvar['package_id']}'
										   		WHERE `id` = '{$main->getvar['do']}'";										   
								$db->query($update_sql);
								
								
								$addon_list = $addon->getAllAddonsByBillingCycleAndPackage($main->postvar['billing_cycle_id'], $main->postvar['package_id']);
								
								$new_addon_list = array();																
								foreach($addon_list as $addon_id=>$value) {																								
									$variable_name = 'addon_'.$addon_id;
									//var_dump($variable_name);
									if (isset($main->postvar[$variable_name]) && ! empty($main->postvar[$variable_name]) ) {										
										$new_addon_list[] = $addon_id;				
									}															
								}
								
								$addon->updateAddonOrders($new_addon_list, $main->postvar['order_id'], true);
								
								$main->errors("Order has been edited!");
								//$main->done();
							}
						}						
					}					
					$return_array = $order->getOrder($main->getvar['do'], false, false);
					echo $style->replaceVar("tpl/orders/edit.tpl", $return_array);
				}
			break;
			case 'delete':			
				if (isset($main->getvar['do'])) { 
					$order->delete($main->getvar['do']);
					$main->errors("The order has been deleted!");
				}
				echo "<ERRORS>";				
			break;			
			case 'view':				
				if(isset($main->getvar['do'])) {					
					$return_array = $order->getOrder($main->getvar['do'], true);									
					echo $style->replaceVar("tpl/orders/view.tpl", $return_array);					
				}
			break;
			
			case 'add_invoice':	
			
				if(isset($main->getvar['do'])) {					
					$order_info = $order->getOrderInfo($main->getvar['do']);
					$billing_id = $order_info['billing_cycle_id'];
															
					if($_POST) {						
						$due 		= strtotime($main->postvar['due']);
						$notes		= $main->postvar['notes'];
						$package_id	= $main->postvar['package_id'];
						
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
						$amount = $package_info['amount'];						
						$invoice->create($order_info['userid'], $amount, $due, $notes, $addon_serialized);
						
						$main->errors("Invoice created!");	
						$main->redirect("?page=invoices&sub=all");									
					}
					
					$user_info  =  $user->getUserById($order_info['userid']);
					
					
					$return_array['USER'] 			= $user_info['firstname'].' '.$user_info['lastname'];
					$return_array['DOMAIN'] 		= $order_info['domain'];
					
					$billing_info 					= $billing->getBilling($billing_id);
					$return_array['BILLING_CYCLES'] = $billing_info ['name'];
					$return_array['BILLING_ID'] = $billing_id;
					
					$addon_list = $addon->getAddonsByPackage($billing_id);
					
					//var_dump($addon_list);
					/*$new_addon_list = array();
					foreach($addon_list as $addon_item) {
						$new_addon_list[] = array($addon_item['name'],$addon_item['id']);
					}*/
					
					//$return_array['ADDON'] 	=  $addon->generateAddonCheckboxesWithList($addon_list, array_flip($order_info['addons']));
					$return_array['ADDON'] 	=  $addon->generateAddonCheckboxesWithBilling($billing_id, $order_info['pid'], array_flip($order_info['addons']));
										
/*					//Packages feature added				
					$query = $db->query("SELECT * FROM `<PRE>packages`");
					if($db->num_rows($query) == 0) {
						echo "There are no packages, you need to add a package first!";
						return;
					}
					
					$package_list = array();		
					while($data = $db->fetch_array($query)) {
						$package_list[$data['id']] = array($data['name'], $data['id']);
					}
	*/				
					$packages = $package->getAllPackagesByBillingCycle($billing_id);
					
			   		$package_list = array();
					foreach($packages as $package) {
						$package_list[$package['id']] = array($package['name'].' - '.$currency->toCurrency($package['amount']), $package['id']);				
					}			
					$return_array['PACKAGES']  =  $main->dropDown('package_id', $package_list, $order_info['pid'], 1, '', array('onchange'=>'loadAddons(this);'));
					
					//$return_array['PACKAGES'] = $main->dropDown('package_id', $package_list, $order_info['pid'],1 , '' , array('onchange'=>'changeAddons(this,'.$main->getvar['do'].' );'));	
					
					$return_array['DUE'] = date('Y-m-d');					
					$return_array['ID'] = $main->getvar['do'];					 
					
					//var_dump($order_info );
								
					echo $style->replaceVar("tpl/invoices/addinvoice.tpl", $return_array);
					
				
				} else {
					$main->errors("You need an order before create an invoice!");
				}			
			break;	
			
			
			default :	
			case 'all':
				$return_array = $order->getAllOrdersToArray();				
				echo $style->replaceVar("tpl/orders/admin-page.tpl", $return_array);				
			break;	
			
		}
	}
}
?>
