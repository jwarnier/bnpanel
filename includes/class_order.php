<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if (THT != 1) {
	die();
}

class order extends model {
	
	public $columns 	= array('id', 'userid','username', 'password','domain','pid', 'signup', 'status', 'additional', 'billing_cycle_id','subdomain_id');	
	public $table_name 	= 'orders';
	//public $_modelName 	= 'order';
	
	//experimental object handlers inspired by Akelos
	public $has_many	= array('invoice'=> array('table_name'=>'order_invoices', 'columns'=>array('id', 'order_id', 'invoice_id')),
								'addon'  => array('table_name'=>'order_addons',   'columns'=>array('id', 'order_id', 'addon_id'))
								);
	
	/** 
	 * Creates an order
	 * 
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($params, $clean_token = true) {		
		global $main, $db, $email, $user;
		$order_id = $this->save($params);
		
		
		if (!empty($order_id) && is_numeric($order_id )) {
			
			$this->addAddons($order_id, $params['addon_list']);
			
			$main->addLog("order::create : $order_id");
		
			$emailtemp 				= $db->emailTemplate('orders_new');
			$order_info 			= $this->getOrder($order_id, true);
			$user_info 				= $user->getUserById($params['userid']);			
						
			$array['FIRSTNAME']		= $user_info['firstname'];
			$array['LASTNAME'] 		= $user_info['lastname'];			
			$array['SITENAME'] 		= $db->config('name');
			$array['ORDER_ID'] 		= $order_id;
			$array['PACKAGE'] 		= $order_info['PACKAGES'];
			$array['ADDONS'] 		= $order_info['ADDON'];
			$array['DOMAIN'] 		= $order_info['REAL_DOMAIN'];
			$array['BILLING_CYCLE'] = $order_info['BILLING_CYCLES'];
			$array['TOTAL'] 		= $order_info['TOTAL'];
			$array['TOS'] 		    = $db->config('TOS');
			$array['ADMIN_EMAIL'] 	= $db->config('EMAIL');
			
			$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);
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
		//Insert into user_pack_addons
		$order_id = intval($order_id);		
		if (is_array($addon_list) && count($addon_list) > 0) {
			foreach ($addon_list as $addon_id) {
				if (!empty($addon_id) && is_numeric($addon_id)) {
					$addon_id = intval($addon_id);
					$params['order_id'] = $order_id;
					$params['addon_id'] = $addon_id;
					$this->order_addons->save($params);
				}
			}
		}
		
	}
	
	/**
	 * Updates an order status. Also sends an email to the user order owner
	 */
	public function updateOrderStatus($order_id, $status) {	
			
		global $main, $server, $email, $user, $db, $package;		
		$this->setId($order_id);
		
		$order_info = $this->getOrderInfo($order_id, true);
		$user_info 	= $user->getUserById($order_info['userid']);		
		$order_status = array_keys($main->getOrderStatusList());
		
		$package_info   = $package->getPackage($order_info['pid']);		
		$serverphp		= $server->loadServer($package_info['server']); # Create server class		
		$site_info = false;
		if ($serverphp != false ) {				
			$site_info 		= $serverphp->getSiteStatus($order_id);
		}
		if (in_array($status, $order_status)) {				
			switch($status) {
				case ORDER_STATUS_ACTIVE:				
					//Setting email
					$send_email = false;					
					if ($site_info != false) {
						$result = $server->unsuspend($order_id);
						if($result) { $send_email = true; }																	
					} else {
						//Sent to ISPConfiG!!!!
						$result = $this->sendOrderToControlPanel($order_id);						
									
						if (!$result) {
							$result = true;
							$status = ORDER_STATUS_FAILED;					
						} else {
							$send_email = true;
						}				
					}						
					if ($send_email) {
						$emailtemp 	= $db->emailTemplate('orders_active');
						$array['ORDER_ID'] = $order_id;						
						$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);				
					}				
				break;
				case ORDER_STATUS_WAITING_ADMIN_VALIDATION:	
					$result = true;
					if ($site_info != false) { 				
						$emailtemp 	= $db->emailTemplate('orders_waiting_admin');
						$array['ORDER_ID'] 	= $order_id;					
						$array['USER'] 		= $order_info['username'];
						$array['PASS'] 		= $order_info['password'];
						$array['EMAIL'] 	= $user_info['email'];					
						$array['DOMAIN'] 	= $order_info['domain'];		
						$result = $server->suspend($order_id);
						if ($result)
							$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);
					}			
				break;
				case ORDER_STATUS_CANCELLED:
					$result = true;
					if ($site_info != false) { 
						$emailtemp 	= $db->emailTemplate('orders_cancelled');
						$array['ORDER_ID'] = $order_id;
						$result = $server->suspend($order_id);
						if ($result)
							$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);
					}
				break;
				case ORDER_STATUS_DELETED:				
				case ORDER_STATUS_WAITING_USER_VALIDATION:		
					$result = true;
					if ($site_info != false) { 		
						//We just suspend the order not delete it					
						$result = $server->suspend($order_id);
					}
				break;
				case ORDER_STATUS_FAILED:
					$result = true;
				break;
				default:
				break;
			}
			
			$main->addLog("order::updateOrderStatus function called: $order_id changed to $status");
			
			$params['status'] = $status;
			if ($result) {
				$this->update($params);
				$main->addLog("order::updateOrderStatus function called succeed");
				return true;
			}	
			$main->addLog("order::updateOrderStatus function called error");		
		}
		return false;	
	}	
	
	
	/**
	 * Deletes an order
	 */
	public function delete($id) { # Deletes invoice upon invoice id
		global $main, $invoice;
		$result = $this->updateOrderStatus($id, ORDER_STATUS_DELETED);		
		if($result ) {		
			$main->addLog("Order id $id deleted ");
			
			//Delete all invoices also
			$invoice_list= $this->getAllInvoicesByOrderId($id);
			foreach($invoice_list as $invoice_item) {
				$invoice->delete($invoice_item['id']);
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Edits an order
	 */
	public function edit($order_id, $params) {
		global $main;		
		$this->setId($order_id);
		//Here we will change the status of the package in the Server
		$result = true;
		if(isset($params['status']) && !empty($params['status'])) {		
			echo '7777777777777777777';	
			$result = $this->updateOrderStatus($order_id, $params['status']);
			unset($params['status']); //do not update twice 			
		}
		if ($result) {		
			$this->update($params);
			$main->addLog("order::edit  Order #$order_id ");
			return true;
		}
		$main->addLog("order::edit Trying to update the Order id $order_id ");
		return false;
	}
	
	/**
	 * Sends an order to the Control Panel. The additional order field must be filled
	 * @param	int		order id
	 * @return	bool	boolean 
	 */
	 
	public function sendOrderToControlPanel($order_id) {
		global $main, $package, $server, $addon;
		$main->addlog('order::sendOrderToControlPanel Order #'.$order_id);
		$order_info		= $this->getOrderInfo($order_id);
		$package_info 	= $package->getPackage($order_info['pid']);
		$serverphp 		= $server->loadServer($package_info['server']); # Create server class
		
		if ($serverphp != false ) {
			$result 	= $serverphp->signup($order_id);				
			if ($result) {
				$all_addons_info = $addon->getAllAddons();	
				$addon_list = $order_info['addons'];
				if(is_array($addon_list) && count($addon_list) > 0) {
					foreach($addon_list as $addon_item) {					
						if ($all_addons_info[$addon_item]['install_package']) {						
							$serverphp->installChamilo($order_id);
							$main->addlog('order::sendOrderToControlPanel Order #'.$order_id.' Install chamilo');
							break;// Install Chamilo just once	
						}
					}						
				}
				return true;	
			}
		}		
		return false;		
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
		global $db, $main;		
		$id = intval($id);
		$sql = "SELECT * FROM ".$this->getTableName()." up WHERE up.id = '{$id}'";
		$result = $db->query($sql);
		$array = array();	
		
		if ($db->num_rows($result) > 0 ) {
			$array = $db->fetch_array($result, 'ASSOC');
			$subdomain_list = $main->getSubDomains();
			
			if (!empty($array['subdomain_id'])) {
				$array['real_domain'] = $array['domain'].'.'.$subdomain_list[$array['subdomain_id']];	
			} else {
				$array['real_domain'] = $array['domain'];
			}
			
			$sql = "SELECT addon_id FROM  <PRE>order_addons WHERE order_id = '{$id}'";
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
			$per_page = intval($db->config('rows_per_page'));
			$page = intval($page);
			$start = ($page-1)*$per_page;	
			$limit = " LIMIT $start, $per_page";
		}		
		$user_id = intval($user_id);
		
		if (empty($user_id)) {
			$sql =  "SELECT * FROM ".$this->getTableName()." WHERE status <> '".ORDER_STATUS_DELETED."' ORDER BY id DESC  $limit ";	
		} else {			
			$sql = "SELECT * FROM ".$this->getTableName()."  WHERE status <> '".ORDER_STATUS_DELETED."' AND userid = '".$user_id."' ORDER BY id DESC $limit ";
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
    	$user_pack_status  	= $main->getOrderStatusList();
    	$subdomain_list		= $main->getSubDomains();
    	
		while($order_item = $db->fetch_array($result_order, 'ASSOC')) {
			
			//Getting the user info			
			$user_info 		= $user->getUserById($order_item['userid']);						
			$array['ID']	= $order_item['id'];
			
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
				$array['USERINFO']  = '<a href="index.php?page=users&sub=search&do='.$user_info['id'].'" title="'.$user_info['lastname'].', '.$user_info['firstname'].'" >'.$user_info['user'].'</a>';
			} else {
				$array['USERINFO']  = ' - ';
			}
			//$array['due'] 		= strftime("%D", $array['due']);
			
			//Getting the domain info					
			if (empty($order_item['subdomain_id'])) {
				$array['DOMAIN'] 	= $order_item['domain'];
			} else {
				$array['DOMAIN'] 	= $order_item['domain'].'.'.$subdomain_list[$order_item['subdomain_id']];
			}
			
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
				$array['EDIT']  	= '<a href="index.php?page=orders&sub=edit&do='.$order_item['id'].'"><img src="../themes/icons/pencil.png" title="Edit" alt="Edit" /></a>';			
				$array['DELETE']  	= '<a href="index.php?page=orders&sub=delete&do='.$order_item['id'].'"><img src="../themes/icons/delete.png" title="Delete"  alt="Delete" /></a>';
				$array['ADD_INVOICE']='<a href="index.php?page=orders&sub=add_invoice&do='.$order_item['id'].'"><img src="../themes/icons/note_add.png" title="Add invoice"  alt="Add invoice" /></a>';				
				$array['CHANGE_PASS']='<a href="index.php?page=orders&sub=change_pass&do='.$order_item['id'].'"><img src="../themes/icons/key.png" title="Change Control Panel password"  alt="Change Control Panel password" /></a>';
				
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
			$array['DOMAIN'] 	= $order_info['domain'];
			$array['REAL_DOMAIN']= $order_info['real_domain'];			
			$array['USERNAME'] 	= $order_info['username'];
			$array['PASSWORD'] 	= $order_info['password'];								
			$array['USER_ID'] 	= $order_info['userid'];			
			$package_id 	  	= $order_info['pid'];
			$billing_cycle_id 	= $order_info['billing_cycle_id'];
			$addon_selected_list= $order_info['addons'];	
						
			//User info
			$user_info = $user->getUserById($order_info['userid']);
						
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
			$show_checkboxes = false;	
			$result = $addon->showAllAddonsByBillingCycleAndPackage($billing_cycle_id, $package_id, array_flip($addon_selected_list), $show_checkboxes);
			
			$array['ADDON'] = $result['html'];
						 				
			$total = $total + $result['total'];

			//Package info
			$package_list		 = $package->getAllPackages();
		
			$package_with_amount = $package->getAllPackagesByBillingCycle($billing_cycle_id);				
			
			$total = $total + $package_with_amount[$package_id]['amount'];
			
			if($read_only == true) {		
				$array['PACKAGES'] 		 = $package_list[$package_id]['name'];
				$array['PACKAGE_AMOUNT'] = $currency->toCurrency($package_with_amount[$package_id]['amount']);									
			} else {
				foreach($package_list as $package_item) {
					$package_list[$package_item['id']] = $package_item['name'].' - '.$currency->toCurrency($package_with_amount[$package_item['id']]['amount']);									
				}				
				//$array['PACKAGES'] = $main->createSelect('package_id', $package_list, $package_id, array('onchange'=>'loadAddons(this);'));
				$array['PACKAGES'] 		 = $package_list[$package_id];				
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
		$order_id = intval($order_id);
		$query = $db->query("SELECT invoice_id FROM <PRE>order_invoices WHERE order_id = '{$order_id}' ORDER BY id DESC LIMIT 1");
		$data = $db->fetch_array($query);
		return $data['invoice_id'];
	}
	
	public function getAllInvoicesByOrderId($order_id) {
		global $db;
		$order_id = intval($order_id);
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
			$html  = '<br /><h3>Invoices for this Order</h3>';
			$html .= '<ul>';
			foreach($invoice_list as $invoice_item) {				
				$my_invoice = $invoice->getInvoiceInfo($invoice_item['invoice_id']);
				if (!empty($my_invoice)) {										
					$html .= '<li><a href="?page=invoices&sub=view&do='.$my_invoice['id'].'" title="Invoice id" >#'.$my_invoice['id'].'</a> ';
					//Status:'.$invoice_status[$my_invoice['status']].' 
					$html .=  'Due date: '.date('Y-m-d', $my_invoice['due']).' Total: '.$currency->toCurrency($my_invoice['total_amount']).'</li>';
				} else {
					$html .= '<li>Problem found with Invoice #'.$invoice_item['invoice_id'].'</li>';
				}
			}
			$html .= '</ul>';
		}
		return $html;
	}	
	
	/**
	 * Gets an order by user 
	 * IMPORTANT only 1 order per user
	 */
	public function	domainExistInOrder($domain, $subdomain_id = 0) {
		global $db;
		if (!empty($domain)) {
			$domain = trim($domain);
			$domain = $db->strip($domain);
			$subdomain_id = intval($subdomain_id);
			//Getting the domain info
			$sql 	= "SELECT domain FROM ".$this->getTableName()." WHERE domain = '".$domain."' AND subdomain_id = $subdomain_id ";
			$result = $db->query($sql);
			if($db->num_rows($result) > 0 ) {
				return true;
			}
		}
		return false;
	}
	
		
	function test() {		
		/*
		$my_order = $this->find(2,3);
		foreach($my_order as $item) {
		var_dump($item->id);	
		}*/	
	}
		
}