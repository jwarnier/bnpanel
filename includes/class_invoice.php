<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){
	die();
}

class invoice {
	
	/**
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($uid, $amount, $due, $notes, $addon_fee, $status) {
		global $db, $email;
		$client 		= $db->client($uid);		
		$emailtemp 		= $db->emailTemplate('newinvoice');
		$array['USER'] 	= $client['user'];
		$array['DUE'] 	= strftime("%D", $due);
		$email->send($client['email'], $emailtemp['subject'], $emailtemp['content'], $array);
		$insert_sql = "INSERT INTO `<PRE>invoices` (uid, amount, due, notes, addon_fee, status ) VALUES('{$uid}', '{$amount}', '{$due}', '{$notes}', '{$addon_fee}', '{$status}' )";
		return $db->query($insert_sql);
	}
	
	public function delete($id) { # Deletes invoice upon invoice id
		global $db;
		$query = $db->query("DELETE FROM `<PRE>invoices` WHERE `id` = '{$id}'"); //Delete the invoice
		return $query;
	}
	
	public function edit($iid, $uid, $amount, $due, $notes) { # Edit an invoice. Fields created can only be edited?
		global $db;
		$query = $db->query("UPDATE `<PRE>invoices` SET
						   `uid` = '{$uid}',
						   `amount` = '{$amount}',
						   `due` = '{$due}',
						   `notes` = '{$notes}',
						   WHERE `id` = '{$iid}'");
		return $query;
	}
	
	/**
	 * Pays an invoice
	 * 
	 */
	public function pay($invoice_id, $returnURL = "order/index.php") {
		global $db;
		require_once("paypal/paypal.class.php");

		$paypal 		= new paypal_class;
		$invoice_info 	= $this->getInvoiceInfo($invoice_id);
		
		if($_SESSION['cuser'] == $invoice_info['uid']) {

			if (SERVER_STATUS == 'test') {
				$paypal->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			} else {
				$paypal->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
			}
			$paypal->add_field('business', 			$db->config('paypalemail'));					
			$paypal->add_field('return', 			urlencode($db->config('url')."client/index.php?page=invoices&sub=paid&invoiceID=".$invoice_id));
			$paypal->add_field('cancel_return', 	urlencode($db->config('url')."client/index.php?page=invoices&sub=paid&invoiceID=".$invoice_id));
			$paypal->add_field('notify_url',  		urlencode($db->config('url')."client/index.php?page=invoices&sub=paid&invoiceID=".$invoice_id));
			$paypal->add_field('item_name', 		$db->config('name').' Order: '.$invoice_info['notes']);
			$paypal->add_field('amount', 			$invoice_info['total_amount']);
			$paypal->add_field('currency_code', 	$db->config('currency'));
			$paypal->submit_paypal_post(); // submit the fields to paypal
		} else {
			echo "You don't seem to be the person who owns that invoice!";	
		}
	}
	
	/**
	 * Gets invoice information 
	 * @param	int the invoice id
	 * @return	array 
	 * @author Julio Montoya <gugli100@gmail.com> BeezNest
	 */
	public function getInvoiceInfo($id) {
		global $db;
		
		$id = intval($id);
		$query = $db->query("SELECT * FROM `<PRE>invoices` WHERE `id` = '{$id}'");
		$array = $db->fetch_array($query);
		$total_amount = 0;
		
		//Getting addon information
		if (!empty($array['addon_fee'])) {
			//the addon_fee is a serialize string			
			$array['addon_fee'] = unserialize($array['addon_fee']);			
			foreach($array['addon_fee'] as $addon) {					
				//$addon_fee_string.= $addons_list[$addon['addon_id']].' - '.$addon['amount'].'<br />';
				$total_amount +=$addon['amount'];					
			}			
			$array['addon_fee'] = serialize($array['addon_fee']);					
		}
		$total_amount = $total_amount + $array['amount']; 
		$array['total_amount'] = $total_amount;		
		
		return $array;
	}
	
	/**
	 * Gets all invoices 
	 * @return	array 
	 * @author Julio Montoya <gugli100@gmail.com> BeezNest
	 */	 
	public function getAllInvoicesToArray() {
		global $main, $db, $style,$currency, $order;
		
		// List invoices. :)
		
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
		                
    	$query  = $db->query("SELECT * FROM `<PRE>invoices` ORDER BY id DESC");
    	
		while($array = $db->fetch_array($query)) {
			
			//Getting the user info
			$sql = "SELECT id, user, firstname, lastname FROM `<PRE>users` WHERE `id` = ".$array["uid"];
			$query_users 		= $db->query($sql);
			$user_info  		= $db->fetch_array($query_users);			
			
			$array['userinfo']  = '<a href="index.php?page=users&sub=search&do='.$user_info['id'].'" >'.$user_info['lastname'].', '.$user_info['firstname'].' ('.$user_info['user'].')</a>';
			$array['due'] 		= strftime("%D", $array['due']);
						
			
			//Getting the domain info
			$domain_info = $order->getOrderByUser($array['uid']);			
			
			$array['domain'] 	= $domain_info['domain'];
			$package_id 	  	= $domain_info['pid'];
			$billing_cycle_id 	= $domain_info['billing_cycle_id'];			
			
			$addon_fee_string = '';
			if (!empty($array['addon_fee'])) {
				
				$array['addon_fee'] = unserialize($array['addon_fee']);
				///var_dump($array['addon_fee']);
				if (is_array($array['addon_fee']) && count($array['addon_fee']) > 1) {
					
					foreach($array['addon_fee'] as $addon) {					
						$addon_fee_string.= $addons_list[$addon['addon_id']].' - '.$addon['amount'].'<br />';
						$total_amount = $total_amount + $addon['amount'];					
					}
				}					
			}
			
			$array['addon_fee'] = $addon_fee_string;		
				
			$total_amount = $total_amount + $array['amount'];
			
			
			//Get the amount info
			//$array['amount'] = $total_amount." ".$db->config('currency');
			$array['amount'] = $currency->toCurrency($total_amount);			

			//Paid configuration links
			$array['paid'] = ($array["is_paid"] == 1 ? "<span style='color:green'>Already Paid</span>" :
													   "<span style='color:red'>Unpaid</span>");
														
			$array['pay'] = ($array["is_paid"] == 0 ? 
			"<a href='index.php?sub=all&page=invoices&iid={$array['id']}&pay=true' title='Mark as paid'> <img src='../themes/icons/money_add.png' width=\"18px\" alt='Mark as paid' title='Mark as paid' /></a>" :
			"<a href='index.php?sub=all&page=invoices&iid={$array['id']}&unpay=true' title='Mark as unpaid'> <img src='../themes/icons/money_delete.png'  width=\"18px\" alt='Mark as unpaid'title='Mark as unpaid' /> </a>");
			
			$array['due'] =  ($array["is_paid"] == 1 ? '<span style="color:green">'.$array['due'].'</span>' :  '<span style="color:red">'.$array['due'].'</span>');
			
			$array['package']		 = $package_name_list[$package_id];
			$array['billing_cycle']  = $billing_cycle_name_list[$billing_cycle_id];
			
			$array['edit']  	= '<a href="index.php?page=invoices&sub=edit&do='.$array['id'].'"><img src="../themes/icons/note_edit.png" alt="Edit" /></a>';			
			$array['delete']  	= '<a href="index.php?page=invoices&sub=delete&do='.$array['id'].'"><img src="../themes/icons/delete.png" alt="Delete" /></a>';
			
			$array2['list'] .= $style->replaceVar("tpl/invoices/invoice-list-item.tpl", $array);
		}
				
		$array2['num'] 			= mysql_num_rows($query);
		$array2['numpaid'] 		= intval($array2['num']-mysql_num_rows($query2));
		$array2['numunpaid'] 	= mysql_num_rows($query2);
		
		return $array2;		
	}
	
	public function getAllInvoices() {
		global $db;
		
		$result = $db->query("SELECT * FROM `<PRE>invoices`");
		$invoice_list = array();
		if($db->num_rows($result) >  0) {
			while($data = $db->fetch_array($result)) {
				$invoice_list[$data['id']] = $data;
			}
		}
		return $invoice_list;
	}
	
	/**
	 * Gets an Invoice info to show using the templates
	 * @param	int	invoice id
	 * @return	array 
	 * @author Julio Montoya <gugli100@gmail.com> BeezNest
	 */
	public function getInvoice($invoice_id, $read_only = false, $show_price = true) {
		global $main, $db, $currency, $addon, $email, $package, $order;	
		
		$query = $db->query("SELECT * FROM `<PRE>invoices` WHERE `id` = '{$invoice_id}'");
		if($db->num_rows($query) == 0) {
			echo "That invoice doesn't exist!";	
		} else {
			
			$total = 0;			
			$invoice_info = $db->fetch_array($query);
			//	var_dump($data);
			$array['ID'] 		= $invoice_info['id'];
			$user_id 			= $invoice_info['uid'];
			$total				= $total + $invoice_info['amount'];
			
			//User info
			$sql = "SELECT id, user, firstname, lastname FROM `<PRE>users` WHERE `id` = ".$user_id;
			
			$query_users 		= $db->query($sql);
			$user_info  		= $db->fetch_array($query_users);
			$array['USER'] 		=  $user_info['lastname'].', '.$user_info['firstname'].' ('.$user_info['user'].')';				

			
			if ($read_only == true) {
				$array['IS_PAID']  = ($invoice_info['is_paid'] == 1) ? 'yes' : 'no';
			} else {
				$array['IS_PAID']  = $main->createCheckbox('', 'is_paid', $invoice_info['is_paid']);
			}				
			
			$array['CREATED'] 	= $invoice_info['created'];
			$array['NOTES'] 	= $invoice_info['notes'];	
			$array['DUE'] 		= date('Y-m-d h:i:s', $invoice_info['due']);
		
			//$array['ADDON_FEE'] = $invoice_info['addon_fee'];
			$addon_selected_list = array();
			if (!empty($invoice_info['addon_fee'])) {				
				/* 
				 * Addon_fee structure
				 * array
					  0 => 
					    array
					      'addon_id' 	=> int 9
					      'billing_id'  => string '3' 
					      'amount'		=> string '10.600000'
				 */
				 //see also addon::generate function 
				$invoice_info['addon_fee'] = unserialize($invoice_info['addon_fee']);							
				
				if (is_array($invoice_info['addon_fee']) && !empty($invoice_info['addon_fee'])) {							
					foreach ($invoice_info['addon_fee'] as $addon_item) {
						//var_dump($addon);
						$addon_selected_list[$addon_item['addon_id']]= $addon_item['amount'];
					}							
				}
			}			
					
			$domain_info = $order->getOrderByUser($user_id);				
			$array['domain'] 	= $domain_info['domain'];
			$package_id 	  	= $domain_info['pid'];
			$billing_cycle_id 	= $domain_info['billing_cycle_id'];					
			
			
			//Addon feature added
			
			$array['ADDON'] = ' - ';
			$addong_result_string = '';
			/*foreach($addon_list as $invoice_info) {
				$checked = false;
				if (in_array($invoice_info['id'], array_keys($addon_selected_list))) {
					$checked = true;
					$total = $total + $addon_selected_list[$invoice_info['id']];
					if ($read_only == true) {
						if ($show_price) {
							$addong_result_string .= $invoice_info['name'].' - '.$currency->toCurrency($addon_selected_list[$invoice_info['id']]).'<br />';
						} else {
							$addong_result_string .= $invoice_info['name'].'<br />';
						}
					}
				}		
				
				if ($read_only == false) {
					$my_amount = $addon_selected_list[$invoice_info['id']];
					if (empty($my_amount)) {
						$my_amount = $addon_list[$invoice_info['id']]['amount'];					 
					}
					$my_amount = $currency->toCurrency($my_amount);
					if ($show_price) {
						$check_box_name = $invoice_info['name'].' - '.$my_amount;
					} else {
						$check_box_name = $invoice_info['name'];
					}
					$addong_result_string .= $main->createCheckbox($check_box_name, 'addon_'.$invoice_info['id'], $checked);
				}					
			}*/
			
			$addon_list = $addon->getAddonsByPackage($package_id);
			//var_dump($addon_list, $addon_selected_list);
			//$addong_result_string =  $addon->generateAddonCheckboxesWithBilling($addon_list, $addon_selected_list);
			//$addong_result_string =  $addon->generateAddonCheckboxesWithBilling($billing_cycle_id, $package_id, $addon_selected_list);
			
			$addon_list = $addon->getAllAddonsByBillingId($billing_cycle_id);
			
			
			foreach($addon_selected_list as $addon_id => $addon_amount) {
				if ($read_only == false) {
					$array['ADDON'] .= $addon_list[$addon_id]['name'].' - <input id="addon_'.$addon_id.'" name="addon_'.$addon_id.'" value="'.$addon_amount.'"><br />';				
				} else {
					$array['ADDON'] .= $addon_list[$addon_id]['name'].' '.$currency->toCurrency($addon_amount).'<br />';
				}
				$total = $total + $addon_amount;
			}
			/*
			if (!empty($addong_result_string)) {
				$array['ADDON'] = $addong_result_string;
			}*/		
			
			//Packages feature added				
			$query = $db->query("SELECT * FROM `<PRE>packages`");
			$package_list = array();
			if($db->num_rows($query) > 0) {
				while($data = $db->fetch_array($query)) {
					$package_list[$data['id']] = array($data['name'], $data['id']);				
				}
			}
			
			/*
			if($read_only == true) {		
				$array['PACKAGES'] = $package_list[$package_id][0];
			} else {			
				//$array['PACKAGES'] = $main->dropDown('packages', $package_list, $package_id);					
				$packages = $package->getAllPackagesByBillingCycle($billing_cycle_id);
					
		   		$package_list = array();
				foreach($packages as $package) {
					$package_list[$package['id']] = array($package['name'].' - '.$currency->toCurrency($package['amount']), $package['id']);				
				}				
				$array['PACKAGES'] = $main->dropDown('packages', $package_list, $package_id, 1, '', array('onchange'=>'loadAddons(this);'));				
			}*/
			
			$array['PACKAGE_ID']	 = $package_id;
			$array['PACKAGE_NAME']	 = $package_list[$package_id][0];
			if ($read_only) {
				$array['PACKAGE_AMOUNT'] = $currency->toCurrency($invoice_info['amount']);
			} else {
				$array['PACKAGE_AMOUNT'] = $invoice_info['amount'];
			}
			
			//Billing cycle
			$query = $db->query("SELECT * FROM `<PRE>billing_cycles` WHERE status = ".BILLING_CYCLE_STATUS_ACTIVE);
			if($db->num_rows($query) > 0) {						
				$values = array();		
				while($data = $db->fetch_array($query)) {
					$values[$data['id']] = array($data['name'], $data['id']);				
				}
				//$array['BILLING_CYCLES'] .= $main->dropDown('billing_cycles', $values, $billing_cycle_id);
			}
			if ($read_only) {
				$array['BILLING_CYCLES'] = $values[$billing_cycle_id][0];
			} else {
				$array['BILLING_CYCLES'] = $values[$billing_cycle_id][0].'<input type="hidden" id="billing_id" name="billing_id" value="'.$billing_cycle_id.'">';
			}

			$array['TOTAL'] = $currency->toCurrency($total);
			return $array;
		}
	}
	
	/**
	 * Creates a new invoice depending in the billing cycle
	 * @author Julio Montoya <gugli100@gmail.com> BeezNest 2010 - Adding the billing cycle feature
	 */
	public function cron() {
		global $db, $main, $server, $billing, $invoice, $email,$user;
		$today = time();
		//For every package 
		$query = $db->query("SELECT * FROM `<PRE>packages` WHERE `type` = 'paid'");
		while($package = $db->fetch_array($query)) {
			$id = intval($package['id']);
			//For every user order
			$result_user_packs = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `pid` = '{$id}'");
			
			while($user_pack = $db->fetch_array($result_user_packs)) {
				$uid = intval($user_pack['userid']);
				$result_invoices = $db->query("SELECT * FROM `<PRE>invoices` WHERE `uid` = '{$uid}' ORDER BY id DESC LIMIT 1");
				$billing_info 	 = $billing->getBilling($user_pack['billing_cycle_id']);
				
				//2592000 seconds = 30 days
				//If today is bigger than 30 days since the creation then create a new invoice
				$billing_number_months_in_seconds  = intval($billing_info['number_months'])*30*24*60*60; //Instead of 2592000 seconds = 30 days
									
				if($db->num_rows($result_invoices) > 0) {
					$my_invoice = $db->fetch_array($result_invoices);
										
					//Generate a new invoice if time is exceed (first time)
					//var_dump($today, strtotime($my_invoice['created']) + $billing_number_months_in_seconds);
					/*
					echo 'Now : '.$today = date('Y-m-d h:i:s', time());
					echo '<br />Invoice info :<br />';					
					echo 'Created date '.date('Y-m-d h:i:s', strtotime($my_invoice['created'])).'<br />';
					echo 'Due date  '.date('Y-m-d h:i:s', $my_invoice['due']).'<br />';
					*/
					//"Terminating" a user if he does not pay
					
					//$lastmonth 			= $today-2592000;					
					$suspendseconds 	= intval($db->config('suspensiondays')) *24*60*60;
					$terminateseconds 	= intval($db->config('terminationdays'))*24*60*60;
					
					//If normal time is over and he does not paid
			//		var_dump(date('Y-m-d h:i:s',$today), date('Y-m-d h:i:s', strtotime($my_invoice['created']) + $billing_number_months_in_seconds));
					//If the package is paid 
					//if ($package['paid']) {
						
					//Not paid
					if ($my_invoice['is_paid'] == 0) {
						//1. Due date of the last invoice has exceed
						//    25                   29
						if ($my_invoice['due'] < $today) {
							
							//My invoice has expired need to suspend if time has past the limits	
												
							//echo 'My invoice has expired need to suspend if time has past<br />';
							
							//Suspend conditions
							//var_dump('Suspend : '.date('Y-m-d h:i:s',$today - $suspendseconds));
							//Terminate conditions
							//var_dump('Terminate: '.date('Y-m-d h:i:s',$today-$suspendseconds-$terminateseconds));
							
							$is_terminate = false;							
							//Terminate is a bad thing we would do nothing
							
							/*						
							if(($today-$suspendseconds-$terminateseconds) > intval($my_invoice['due']) ){
								echo 'Terminate <br />';
								$server->terminate($user_pack['id']);
								$is_terminate = true;
							} */			
											
							//2. Proceed to suspend							 
							if ($is_terminate == false ) {
								// 29        -    1 =  28                             25                             
								if(($today - $suspendseconds) > intval($my_invoice['due'])) {
									//echo 'Suspend <br />';
									$server->suspend($user_pack['id']);
								}
							}					
						}
					} else {
						//If I'm already pay	
						//Check the invoice creation date + 30 days (monthly)
						//  29                    2010-06-28                  30   
						//var_dump(date('Y-m-d', strtotime($my_invoice['created']) + $billing_number_months_in_seconds));
						
						if($today > strtotime($my_invoice['created']) + $billing_number_months_in_seconds) {
							//Creates an invoice
							$my_new_invoice = $invoice->getInvoiceInfo($my_invoice['id']);							
							//	var_dump($my_new_invoice);							
							//echo 'Create invoice<br />';
							//$this->create($uid, $my_new_invoice['amount'], $today + intval($db->config('suspensiondays')*24*60*60), $my_new_invoice['notes'], $my_new_invoice['addon_fee']);						
							$this->create($uid, $my_new_invoice['amount'], $today + $billing_number_months_in_seconds, $my_new_invoice['notes'], $my_new_invoice['addon_fee']);
						} else {
							//echo 'Not created';
						}
					}
															
					// Generate warning messages		
								
					switch ($billing_info['number_months']) {
						case  $billing_info['number_months'] == 1 :
							$before_list_of_days = array(7);	
						break;					
						case  $billing_info['number_months'] == 2 :
							$before_list_of_days = array(7,30);	
						break;
						case  $billing_info['number_months'] == 3 :
							$before_list_of_days = array(7, 30, 60);	
						break;
						case  $billing_info['number_months'] >=  4 :
							$before_list_of_days = array(7, 30, 60, 120);
						break;						
					}
									
					sort($before_list_of_days);
					
					$email_day_count = '';
										
					//$my_invoice['due'] = '1280451661';
					//$my_invoice['due'] = '1278378061';					
					
					//echo date('Y-m-d', $my_invoice['due']);
					foreach($before_list_of_days as $days) {
						$my_time_in_seconds = $my_invoice['due'] - $days*24*60*60;
						$due_date = date('Y-m-d', $my_time_in_seconds);
						$date_now = date('Y-m-d', time());
						if ( $date_now  == $due_date) {
							$email_day_count = $days;
							break;
						}						
					}
					
					//var_dump($email_day_count);
					if ($my_invoice['is_paid'] == 0 && !empty($email_day_count)) {
					
						$user_info = $user->getUserById($uid);
						$emaildata = $db->emailTemplate('notification');
						
						$invoice_info = $this->getInvoice($my_invoice['id'], true); 
											
						$replace_array['USERNAME'] 				=  $user_info['firstname'].' '.$user_info['lastname'];
						$replace_array['INVOICE_CREATE_DATE'] 	=  substr($invoice_info['CREATED'], 0,10);
						
						$replace_array['PAYMENT_METHOD'] 		=  'Paypal';
						$replace_array['DOMAIN'] 				=  $user_pack['domain'];
						
						$replace_array['BILLING_CYCLE'] 		=  $invoice_info['BILLING_CYCLE'];
						$replace_array['INVOICE_NUMBER'] 		=  $invoice_info['ID'];
						$replace_array['AMOUNT'] 				=  $invoice_info['TOTAL'];
						$replace_array['INVOICE_DUE_DATE'] 		=  substr($invoice_info['DUE'], 0, 10);
						$replace_array['PACKAGE_INFO'] 			=  $invoice_info['PACKAGES'].' - '.$invoice_info['AMOUNT'];
						$replace_array['ADDONS'] 				=  $invoice_info['ADDON'];
						
						$invoice_summary = '---------------------------------<br />';
						$invoice_summary .= 'Total: '.$replace_array['AMOUNT'].'<br />';
						$invoice_summary .= '---------------------------------<br />';
						
						$replace_array['INVOICE_SUMMARY'] 		=  $invoice_summary;
						$replace_array['COMPANY_NAME'] 			=  $db->config('name');
						$replace_array['URL'] 					=  $db->config('url');
						$replace_array['USER_LOGIN'] 			=  $user_info['user'];					
						
						$emaildata['subject'] = $email_day_count.'-Day Renewal Notice';						
						echo 'Email sent to user '.$user_info['email'].' Days'.$email_day_count;						
						$email->send($user_info['email'], $emaildata['subject'], $emaildata['content'], $replace_array);						
					}
					
				} else { # User has no invoice yet
					//What to do here
					//$this->create($uid, $amount, $today +$billing_number_months_in_seconds, ""); # Create Invoice
				}
			}
		}
	}
	
	public function getLastInvoiceByUser($user_id) {
		global $db;
		$user_id = intval($user_id);
		$sql = "SELECT id FROM `<PRE>invoices` WHERE `uid` = ".$user_id." ORDER BY id DESC LIMIT 1";
		
		$result	= $db->query($sql);
		$invoice = array();
		if ($db->num_rows($result) > 0 ) {		
			$invoice = $db->fetch_array($result);			
		}
		return $invoice;
	}
	
	public function set_paid($iid) { # Pay the invoice by giving invoice id
		global $db, $server;
		
		$query = $db->query("UPDATE `<PRE>invoices` SET `is_paid` = '1' WHERE `id` = '{$iid}'");
		$query2 = $db->query("SELECT * FROM `<PRE>invoices` WHERE `id` = '{$iid}' LIMIT 1");
		$data2 = $db->fetch_array($query2);
		$query3 = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$data2['uid']}'");
		$data3 = $db->fetch_array($query3);
		$server->unsuspend($data3['id']);
		return $query;
	}
	
	public function set_unpaid($iid) { # UnPay the invoice by giving invoice id - Don't think this will be useful
		global $db;
		$query = $db->query("UPDATE `<PRE>invoices` SET `is_paid` = '0' WHERE `id` = '{$iid}'");
		return $query;
	}
	
	public function is_paid($id) { # Is the invoice paid - True = Paid / False = Not
		global $db;
		$data = $db->fetch_array($db->query("SELECT is_paid FROM `<PRE>invoices` WHERE `id` = '{$id}'"));
		if($data['is_paid']) {
			return true;	
		}
		else {
			return false;	
		}
	}
}
?>