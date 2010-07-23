<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){
	die();
}

class order extends model {
	
	public $columns 	= array('id', 'userid','username', 'password','domain','pid', 'signup', 'status', 'additional', 'billing_cycle_id');	
	public $table_name 	= 'orders';	
	
	/** 
	 * Creates an order
	 * 
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($params, $clean_token = true) {		
		global $main, $db, $email, $user;
		$order_id = $this->save($params, $clean_token);
		if (!empty($order_id) && is_numeric($order_id )) {
			$main->addLog("Order created: $order_id");
			/*
			$emailtemp 				= $db->emailTemplate('neworder');
			$user_info 				= $user->getUserById($params['userid']);			
			$order_info 			= $this->getOrder($order_id, true);
						
			$array['FIRSTNAME']		= $user_info['firstname'];
			$array['LASTNAME'] 		= $user_info['lastname'];			
			$array['SITENAME'] 		= $db->config('name');
			$array['ORDER_ID'] 		= $order_id;
			$array['PACKAGE'] 		= $order_info['PACKAGES'];
			$array['ADDONS'] 		= $order_info['ADDON'];
			$array['DOMAIN'] 		= $order_info['domain'];
			$array['BILLING_CYCLE'] = $order_info['BILLING_CYCLES'];
			$array['TOTAL'] 		= $order_info['TOTAL'];
			$array['TOS'] 		    = $db->config('TOS');
			$array['ADMIN_EMAIL'] 	= $db->config('EMAIL');
			
			$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);*/
			return	$order_id;
		}			
		return false;
	}
	
	/** 
	 * Add addon to a order
	 * 
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	 
	public function addAddons($order_id, $addon_list) {
		global $db, $main;
		if ($main->checkToken()) {
			//Insert into user_pack_addons
			if (is_array($addon_list) && count($addon_list) > 0) {
				foreach ($addon_list as $addon_id) {
					if (!empty($addon_id) && is_numeric($addon_id)) {
						$addon_id = intval($addon_id);
						$order_id = intval($order_id);					
						$sql_insert = "INSERT INTO order_addons(order_id, addon_id) VALUES ('$order_id', '$addon_id')";
						$db->query(	$sql_insert);					
					}
				}
			}
		}
	}
	
	public function updateOrderStatus($order_id, $status) {
		global $main, $server;		
		$this->setPrimaryKey($order_id);
		$order_status = array_keys($main->getOrderStatusList());		
		if (in_array($status, $order_status)) {				
			switch($status) {
				case ORDER_STATUS_ACTIVE:
					$server->unsuspend($order_id);
				break;
				case ORDER_STATUS_WAITING_ADMIN_VALIDATION:
				case ORDER_STATUS_CANCELLED:
				case ORDER_STATUS_DELETED:
				case ORDER_STATUS_WAITING_USER_VALIDATION:
					$server->suspend($order_id);
				break;
				default:
				break;
			}		
			$params['status'] = $status;		
			$this->update($params);
			$main->addLog("updateOrderStatus function called: $order_id changed to $status");
		}		
	}	
	
	
	/**
	 * Deletes an order
	 */
	public function delete($id) { # Deletes invoice upon invoice id
		$this->updateOrderStatus($id, ORDER_STATUS_DELETED);		
		$main->addLog("Order id $id deleted ");
		return true;
	}
	
	/**
	 * Edits an order
	 */
	public function edit($order_id, $params) {
		$this->setPrimaryKey($order_id);
		/*//No updates of a order username/password
		unset($params['username']);
		unset($params['password']);
		*/
		
		//Here we will change the status of the package in the Server
		if(isset($params['status']) && !empty($params['status'])) {		
			$this->updateOrderStatus($order_id, $params['status']);
			unset($params['status']); //do not update twice 			
		}
		$main->addLog("Order id $order_id updated");
		$this->update($params);
	}
	
	/**
	 * Gets an order by user 
	 * IMPORTANT only 1 order per user
	 */
	public function getOrderByUser($user_id) {
		global $db;
		$user_id = intval($user_id);
		//Getting the domain info
		$sql = "SELECT id, pid, domain, billing_cycle_id FROM ".$this->getTableName()." WHERE `userid` = ".$user_id;
		$result 		= $db->query($sql);
		$order_info  	= $db->fetch_array($result, 'ASSOC');
		return $order_info;
	}
	
	/**
	 * Gets an order by user 
	 * IMPORTANT only 1 order per user
	 */
	public function getAllOrdersByUser($user_id) {
		global $db;
		$user_id = intval($user_id);
		//Getting the domain info
		$sql 	= "SELECT id, pid, domain, billing_cycle_id, status FROM ".$this->getTableName()." WHERE `userid` = ".$user_id;		
		$result = $db->query($sql);
		$orders = $db->store_result($result,'ASSOC');
		return $orders;
	}

	
	/**
	 * Gets Order information 
	 * @param	int the invoice id
	 * @return	array 
	 * @author Julio Montoya <gugli100@gmail.com> BeezNest
	 */
	public function getOrderInfo($id) {
		global $db;		
		$id = intval($id);
		$sql = "SELECT * FROM ".$this->getTableName()." up WHERE up.id = '{$id}'";
		$result = $db->query($sql);
		$array = array(); 
		if ($db->num_rows($result) > 0 ) {
			$array = $db->fetch_array($result);
						
			$sql = "SELECT addon_id FROM  `<PRE>order_addons` WHERE order_id = '{$id}'";
			$result_addons = $db->query($sql);
			$addon_list = array();
			while ($addon = $db->fetch_array($result_addons)) {
				$addon_list[] = $addon['addon_id'];
			}
			$array['addons'] = 	$addon_list;
		}
		return $array;
	}
	
		
	/**
	 * Gets all orders (use only with template) 
	 * @return	array 
	 * @author Julio Montoya <gugli100@gmail.com> BeezNest
	 */	 
	public function getAllOrdersToArray($user_id = 0, $page = 0) {
		global $main, $db, $style, $currency, $package, $billing, $addon, $user;
		
		$limit = '';
		if (empty($page)) {
			$page = 0;
		} else {			
			$per_page = $db->config('rows_per_page');
			$start = ($page-1)*$per_page;	
			$limit = " LIMIT $start, $per_page";
		}		
		
		if (empty($user_id)) {
			$sql =  "SELECT * FROM ".$this->getTableName()." WHERE status <> '".ORDER_STATUS_DELETED."' ORDER BY id DESC  $limit ";	
		} else {
			$user_id = intval($user_id);
			$sql = "SELECT * FROM ".$this->getTableName()." WHERE status <> '".ORDER_STATUS_DELETED."' AND userid = '".$user_id."' ORDER BY id DESC $limit ";
		}	
		
		$result_order  = $db->query($sql);
		
		$result['list'] = '';
		
		//Package info
		$package_list		= $package->getAllPackages();
				
		//Billing cycles
		$billing_cycle_list = $billing->getAllBillingCycles();
				
		//Selecting addons
		$addons_list 		= $addon->getAllAddons();
		
		$total_amount = 0;                
    	$user_pack_status = $main->getOrderStatusList();
    	
		while($order_item = $db->fetch_array($result_order)) {
			//Getting the user info			
			$user_info = $user->getUserById($order_item['userid']);
						
			$array['ID']		= $order_item['id'];
			
			if (in_array($order_item['status'], array_keys($user_pack_status))) {
				$array['STATUS'] = $user_pack_status[$order_item['status']];
			} else {
				$array['STATUS']    = 'Unknown';	
			}
			if (!empty($order_item['signup'])) {
				$array['DUE']    	= date('Y-m-d', $order_item['signup']);
			} else {
				$array['DUE'] = '-';
			}
			
			if (!empty($user_info)) {
				$array['USERINFO']  = '<a href="index.php?page=users&sub=search&do='.$user_info['id'].'" >'.$user_info['lastname'].', '.$user_info['firstname'].' ('.$user_info['user'].')</a>';
			} else {
				$array['USERINFO']  = ' - ';
			}
			//$array['due'] 		= strftime("%D", $array['due']);
			
			//Getting the domain info
			
			$array['DOMAIN'] 	= $order_item['domain'];
			$package_id 	  	= $order_item['pid'];
			$billing_cycle_id 	= $order_item['billing_cycle_id'];			
			
			//Getting the addons info
			
			$sql = "SELECT addon_id, amount  FROM `<PRE>order_addons` upa INNER JOIN `<PRE>billing_products` bp ON(addon_id=product_id)
						 WHERE type='addon' AND billing_id = $billing_cycle_id AND `order_id` = ".$order_item['id'];
			$query_addon 	= $db->query($sql);			
			while($addon_info = $db->fetch_array($query_addon)){				
				$addon_fee_string.= $addons_list[$addon_info['addon_id']]['name'].' - '.$addon_info['amount'].'<br />';
				$total_amount = $total_amount + $addon_info['amount'];	
			}
				
			$array['addon_fee'] = $addon_fee_string;			
			$total_amount = $total_amount + $array['amount'];			
			
			//Get the amount info
			$array['AMOUNT'] = $currency->toCurrency($total_amount);			

			//Paid configuration links
			$array['paid'] = ($array["is_paid"] == 1 ? "<span style='color:green'>Already Paid</span>" :
														"<span style='color:red'>Unpaid</span>");
			$array['due'] =  ($array["is_paid"] == 1 ? '<span style="color:green">'.$array['due'].'</span>' :  '<span style="color:red">'.$array['due'].'</span>');
			
			$array['PACKAGE']		 = $package_list[$package_id]['name'];
			$array['billing_cycle']  = $billing_cycle_list[$billing_cycle_id]['name'];
			if (empty($user_id)) {
				$array['EDIT']  	= '<a href="index.php?page=orders&sub=edit&do='.$order_item['id'].'"><img src="../themes/icons/note_edit.png" title="Edit" alt="Edit" /></a>';			
				$array['DELETE']  	= '<a href="index.php?page=orders&sub=delete&do='.$order_item['id'].'"><img src="../themes/icons/delete.png" title="Delete"  alt="Delete" /></a>';
				$array['ADD_INVOICE']='<a href="index.php?page=orders&sub=add_invoice&do='.$order_item['id'].'"><img src="../themes/icons/note_add.png" title="Add invoice"  alt="Add invoice" /></a>';
				
				$array['CHANGE_PASS']='<a href="index.php?page=orders&sub=change_pass&do='.$order_item['id'].'"><img src="../themes/icons/key.png" title="Change Panel password"  alt="Change Panel password" /></a>';
				
				$result['list'] .= $style->replaceVar("tpl/orders/list-item.tpl", $array);
			} else {
				//This is for the client view
				$result['list'] .= $style->replaceVar("tpl/orders/list-item-client.tpl", $array);
			}
		}	
		return $result;		
	}
	
	/**
	 * Gets all orders 
	 */
	 public function getAllOrders() {
		global $db;
		
		$result = $db->query("SELECT * FROM ".$this->getTableName()." ");
		$invoice_list = array();
		if($db->num_rows($result) >  0) {
			while($data = $db->fetch_array($result)) {
				$invoice_list[$data['id']] = $data;
			}
		}
		return $invoice_list;
	}
	
	/**
	 * Gets an Order to show using the templates
	 * @param	int	invoice id
	 * @return	array 
	 * @author Julio Montoya <gugli100@gmail.com> BeezNest
	 */
	public function getOrder($order_id, $read_only = false, $show_price = true) {
		global $main, $db, $currency, $addon, $package, $billing, $user;	
		
		$order_info = $this->getOrderInfo($order_id);
		
		if(empty($order_info)) {
			echo "That order doesn't exist!";	
		} else {			
			$total = 0;			
			$array['ID'] 		= $order_info['id'];
			$array['domain'] 	= $order_info['domain'];
			
			$array['USERNAME'] 	= $order_info['username'];
			$array['PASSWORD'] 	= $order_info['password'];	
					
			$user_id 			= $order_info['userid'];
			$array['USER_ID'] 	= $user_id;			
			$package_id 	  	= $order_info['pid'];
			$billing_cycle_id 	= $order_info['billing_cycle_id'];
			$addon_selected_list= $order_info['addons'];	
			
			
			//User info
			$user_info = $user->getUserById($user_id);
						
			$array['USER'] 		= $user_info['lastname'].', '.$user_info['firstname'].' ('.$user_info['user'].')';	
			if(!empty($order_info['signup'])) {								
				$array['CREATED_AT'] = date('Y-m-d', $order_info['signup']);
			} else {
				$array['CREATED_AT'] = '-';
			}
						
			//Addon feature added			
			if ($read_only) {
				$show_checkboxes = false;								
			} else {
				$show_checkboxes = true;	
			}
			
			$result = $addon->showAllAddonsByBillingCycleAndPackage($billing_cycle_id, $package_id, array_flip($addon_selected_list), $show_checkboxes);
			
			$array['ADDON'] = $result['html'];
						 				
			$total = $total + $result['total'];

			//Package info
			$package_list		= $package->getAllPackages();
		
			$package_with_amount = $package->getAllPackagesByBillingCycle($billing_cycle_id);				
			
			$total = $total + $package_with_amount[$package_id]['amount'];
			
			if($read_only == true) {		
				$array['PACKAGES'] 		 = $package_list[$package_id]['name'];
				$array['PACKAGE_AMOUNT'] = $currency->toCurrency($package_with_amount[$package_id]['amount']);									
			} else {
				foreach($package_list as $package_item) {
					$package_list[$package_item['id']] = $package_item['name'].' - '.$currency->toCurrency($package_with_amount[$package_item['id']]['amount']);									
				}				
				$array['PACKAGES'] = $main->createSelect('package_id', $package_list, $package_id, array('onchange'=>'loadAddons(this);'));				
			}		
			
			//Billing cycle
			$billing_list = $billing->getAllBillingCycles();
			
			foreach($billing_list as $billing_item) {
				$billing_list[$billing_item['id']] = $billing_item['name'];
			}						
			if ($read_only) {
				$array['BILLING_CYCLES'] = $billing_list[$billing_cycle_id];
			} else {
				$array['BILLING_CYCLES'] = $main->createSelect('billing_cycle_id', $billing_list, $billing_cycle_id, array('onchange'=>'loadPackages(this);'));
			}		
			
			$order_status = $main->getOrderStatusList();
			if($read_only == true) {							
				$array['STATUS'] = $order_status[$order_info['status']];
			} else {
				$array['STATUS'] = $main->createSelect('status', $order_status, $order_info['status']);
			}
			$array['TOTAL'] = $currency->toCurrency($total);
			  	
			return $array;
		}
	}
	
	/**
	 * Gets all invoices generated by an order id
	 */
	public function getLastInvoiceByOrderId($order_id) {
		global $db;
		$query = $db->query("SELECT invoice_id FROM `<PRE>order_invoices` WHERE `order_id` = '{$order_id}' ORDER BY id DESC LIMIT 1");
		$data = $db->fetch_array($query);
		return $data['invoice_id'];
	}
	
	public function getAllInvoicesByOrderId($order_id) {
		global $db;
		$sql = "SELECT DISTINCT invoice_id FROM `<PRE>order_invoices` WHERE `order_id` = '{$order_id}'";
		$query = $db->query($sql);
		$array = $db->store_result($query);	
		return $array;
	}
	
	public function showAllInvoicesByOrderId($order_id) {
		global $main, $invoice, $currency;
		$invoice_status = $main->getInvoiceStatusList();
		$invoice_list = $this->getAllInvoicesByOrderId($order_id);
		$html = '';
		if (is_array($invoice_list) && count($invoice_list) > 0) {
			$html  = '<h2>Invoice List for this Order</h2>';
			$html .= '<ul>';
			foreach($invoice_list as $invoice_item) {
				$my_invoice = $invoice->getInvoiceInfo($invoice_item['invoice_id']);						
				$html .= '<li><a href="?page=invoices&sub=view&do='.$my_invoice['id'].'">'.$my_invoice['id'].'</a> '.date('Y-m-d', $my_invoice['due']).' '.$invoice_status[$my_invoice['status']].' '.$currency->toCurrency($my_invoice['total_amount']).'</li>';
			}
			$html .= '</ul>';
		}
		return $html;
	}	
}