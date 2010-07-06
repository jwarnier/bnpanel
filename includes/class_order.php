<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){
	die();
}

class order {
	
	/** 
	 * Creates an order
	 * 
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($user_id, $username, $domain, $package_id, $signup, $status, $additional, $billing_cycle_id) {
		global $db, $email;
		/*
		$emailtemp 		= $db->emailTemplate('neworder');
		$array['USER'] 	= $client['user'];
		$array['DUE'] 	= strftime("%D", $due);
		$email->send($client['email'], $emailtemp['subject'], $emailtemp['content'], $array);
		*/
		$db->query("INSERT INTO `<PRE>user_pack` (uid, username, domain, pid, signup, status, additional, billing_cycle_id )
						   VALUES('{$user_id}', '{$username}', '{$domain}', '{$package_id}','{$signup}','{$status}','{$additional}','{$billing_cycle_id}')");
		$order_id = mysql_insert_id();
	
		return	$order_id;
	}
	
	/** 
	 * Add addon to a order
	 * 
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	 
	public function addAddons($order_id, $addon_list) {
		global $db;
		//Insert into user_pack_addons
		if (is_array($addon_list) && count($addon_list) > 0) {
			foreach ($addon_list as $addon_id) {
				if (!empty($addon_id) && is_numeric($addon_id)) {
					$addon_id = intval($addon_id);
					$sql_insert = "INSERT INTO user_pack_addons(order_id, addon_id) VALUES ('$order_id', '$addon_id')";
					$db->query(	$sql_insert);					
				}
			}
		}
	}
	
	
	
	/**
	 * Deletes an order
	 */
	public function delete($id) { # Deletes invoice upon invoice id
		global $db;
		$query = $db->query("DELETE FROM `<PRE>user_packs` WHERE `user_id` = '{$id}'"); //Delete the order
		return true;
	}
	
	public function edit($order_id) {
		global $db;
	
		return ;
	}
	
	/**
	 * Gets an order by user
	 */
	public function getOrderByUser($user_id) {
		global $db;
		//Getting the domain info
		$sql = "SELECT id, pid, domain, billing_cycle_id FROM `<PRE>user_packs` WHERE `userid` = ".$user_id;
		$result 		= $db->query($sql);
		$order_info  	= $db->fetch_array($result);
		return $order_info;
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
		$sql = "SELECT * FROM `<PRE>user_packs` up WHERE up.id = '{$id}'";
		$result = $db->query($sql);
		$array = array(); 
		if ($db->num_rows($result) > 0 ) {
			$array = $db->fetch_array($result);
			
			//$addon->getAddonByBillingCycle();
			
			$sql = "SELECT addon_id FROM  `<PRE>user_pack_addons` WHERE order_id = '{$id}'";
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
	public function getAllOrdersToArray() {
		global $main, $db, $style,$currency;
		
		// List invoices. :)
		$result_order  = $db->query("SELECT * FROM `<PRE>user_packs` ORDER BY id DESC");
		
		$query2 = $db->query("SELECT * FROM `<PRE>invoices` WHERE `is_paid` = 0 ");
		$array2['list'] = "";
		
		//Package info
		$sql 		= "SELECT id, name  FROM `<PRE>packages`";
		$packages	= $db->query($sql);
		while ($data= $db->fetch_array($packages)) {
			$package_name_list[$data['id']] = $data['name'];
		}
		
		//Billing cycles
		$sql = "SELECT id, name  FROM `<PRE>billing_cycles`  WHERE status = ".BILLING_CYCLE_STATUS_ACTIVE;
		$billings 	= $db->query($sql);
		while ($data = $db->fetch_array($billings)) {
			$billing_cycle_name_list[$data['id']] = $data['name'];
		}
		
		//Selecting addons
		$sql 	= "SELECT id, name  FROM `<PRE>addons` WHERE status = ".ADDON_STATUS_ACTIVE;
		$addons	= $db->query($sql);
		
		while ($data = $db->fetch_array($addons)) {
			$addons_list[$data['id']] = $data['name'];
		}
		
		$total_amount = 0;                
    
		while($order_item = $db->fetch_array($result_order)) {
			//Getting the user info
			$sql = "SELECT id, user, firstname, lastname FROM `<PRE>users` WHERE `id` = ".$order_item['userid'];
			$query_users 		= $db->query($sql);
			$user_info  		= $db->fetch_array($query_users);	
			$array['ID']		= $order_item['id'];
					
			$user_pack_status = $main->getOrderStatusList();
			
			if (in_array($order_item['status'], array_keys($user_pack_status))) {
				$array['STATUS'] = $user_pack_status[$order_item['status']];
			} else {
				$array['STATUS']    = 'Unknown';	
			}
			
			$array['DUE']    	= date('Y-m-d', $order_item['signup']);
			$array['USERINFO']  = '<a href="index.php?page=users&sub=search&do='.$user_info['id'].'" >'.$user_info['lastname'].', '.$user_info['firstname'].' ('.$user_info['user'].')</a>';
			//$array['due'] 		= strftime("%D", $array['due']);
			
			//Getting the domain info
			
			$array['DOMAIN'] 	= $order_item['domain'];
			$package_id 	  	= $order_item['pid'];
			$billing_cycle_id 	= $order_item['billing_cycle_id'];			
			
			//Getting the addons info
			
			$sql = "SELECT addon_id, amount  FROM `<PRE>user_pack_addons` upa INNER JOIN `<PRE>billing_products` bp ON(addon_id=product_id)
						 WHERE type='addon' AND billing_id = $billing_cycle_id AND `order_id` = ".$order_item['id'];
			$query_addon 	= $db->query($sql);			
			while($addon_info = $db->fetch_array($query_addon)){				
				$addon_fee_string.= $addons_list[$addon_info['addon_id']].' - '.$addon_info['amount'].'<br />';
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
			
			$array['PACKAGE']		 = $package_name_list[$package_id];
			$array['billing_cycle']  = $billing_cycle_name_list[$billing_cycle_id];
			
			$array['EDIT']  	= '<a href="index.php?page=orders&sub=edit&do='.$order_item['id'].'"><img src="../themes/icons/note_edit.png" title="Edit" alt="Edit" /></a>';			
			$array['DELETE']  	= '<a href="index.php?page=orders&sub=delete&do='.$order_item['id'].'"><img src="../themes/icons/delete.png" title="Delete"  alt="Delete" /></a>';
			$array['ADD_INVOICE']='<a href="index.php?page=orders&sub=add_invoice&do='.$order_item['id'].'"><img src="../themes/icons/note_add.png" title="Add invoice"  alt="Add invoice" /></a>';
			
			
			//var_dump($array);
			$array2['list'] .= $style->replaceVar("tpl/orders/list-item.tpl", $array);
		}
				
		//$array2['num'] 			= mysql_num_rows($query);
		//$array2['numpaid'] 		= intval($array2['num']-mysql_num_rows($query2));
		//$array2['numunpaid'] 	= mysql_num_rows($query2);
		
		return $array2;		
	}
	
	/**
	 * Gets all orders 
	 */
	 public function getAllOrders() {
		global $db;
		
		$result = $db->query("SELECT * FROM `<PRE>user_packs`");
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
		global $main, $db, $currency, $addon, $package;	
		
		$order_info = $this->getOrderInfo($order_id);
		
		if(empty($order_info)) {
			echo "That order doesn't exist!";	
		} else {			
			$total = 0;
			
			//	var_dump($data);
			$array['ID'] 		= $order_info['id'];
			$user_id 			= $order_info['userid'];
			
			//User info
			$sql = "SELECT id, user, firstname, lastname FROM `<PRE>users` WHERE `id` = ".$user_id;
			
			$query_users 		= $db->query($sql);
			$user_info  		= $db->fetch_array($query_users);
			$array['USER'] 		=  $user_info['lastname'].', '.$user_info['firstname'].' ('.$user_info['user'].')';									
			$array['CREATED_AT'] 	= date('Y-m-d', $order_info['signup']);
			
		
			$addon_selected_list = $order_info['addons'];
			
			
			
			//Billing cycle
			$sql = "SELECT id, pid, domain, billing_cycle_id FROM `<PRE>user_packs` WHERE `userid` = ".$user_id;
			$query_domain 		= $db->query($sql);
			
			if($db->num_rows($query_domain) > 0) {					
				$domain_info  		= $db->fetch_array($query_domain);		
						
				$array['domain'] 	= $domain_info['domain'];
				$package_id 	  	= $domain_info['pid'];
				$billing_cycle_id 	= $domain_info['billing_cycle_id'];					
			}
			
			//Addon feature added
			
			$addon_list = $addon->getAllAddonsByBillingCycleAndPackage($billing_cycle_id, $package_id);
								
			$array['ADDON'] = ' - ';
			$addong_result_string = '';
			$array['ADDON'] = 	$addon->generateAddonCheckboxesWithBilling($billing_cycle_id, $package_id, array_flip($addon_selected_list));
			
			//Packages feature added				
			$query = $db->query("SELECT * FROM `<PRE>packages`");
			if($db->num_rows($query) == 0) {
				echo "There are no packages, you need to add a package first!";
				return;
			}
			
			$package_list = array();		
			while($package_data = $db->fetch_array($query)) {
				$package_list[$package_data['id']] = array($package_data['name'], $package_data['id']);				
			}
			
			if($read_only == true) {		
				$array['PACKAGES'] = $package_list[$package_id][0];
			} else {			
				//$array['PACKAGES'] = $main->dropDown('package_id', $package_list, $package_id, 1 , '', array('onchange'=>'loadAddons(this);'));	
				
				$packages = $package->getAllPackagesByBillingCycle($billing_cycle_id);
					
		   		$package_list = array();
				foreach($packages as $package) {
					$package_list[$package['id']] = array($package['name'].' - '.$currency->toCurrency($package['amount']), $package['id']);				
				}				
				$array['PACKAGES'] = $main->dropDown('package_id', $package_list, $order_info['pid'], 1, '', array('onchange'=>'loadAddons(this);'));				
			}
			
			//Billing cycle
			$query = $db->query("SELECT * FROM `<PRE>billing_cycles` WHERE status = ".BILLING_CYCLE_STATUS_ACTIVE);
			if($db->num_rows($query) > 0) {						
				$values = array();		
				while($billing_cycle_data = $db->fetch_array($query)) {
					$values[$billing_cycle_data['id']] = array($billing_cycle_data['name'], $billing_cycle_data['id']);				
				}
			}
			$array['BILLING_CYCLES'] .= $main->dropDown('billing_cycle_id', $values, $billing_cycle_id, 1,'', array('onchange'=>'loadPackages(this);'));
			
			$user_pack_status = $main->getOrderStatusList();
			
			$new_order_list = array();
			foreach($user_pack_status as $key=>$value) {
				$new_order_list[] = array($value, $key);
			}
			
			$array['STATUS'] = $main->dropDown('status', $new_order_list, $order_info['status']);	
			return $array;
		}
	}


}
?>