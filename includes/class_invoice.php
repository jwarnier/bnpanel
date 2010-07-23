<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){
	die();
}

class invoice extends model {
	
	public $columns 	= array('id', 'uid','amount', 'is_paid','created', 'due', 'is_suspended', 'notes', 'uniqueid', 'addon_fee', 'status', 'transaction_id');	
	public $table_name 	= 'invoices';	
	
	/**
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($params, $clean_token = true) {
		global $db, $email;
		$invoice_id = $this->save($params, $clean_token);
		if (!empty($invoice_id) && is_numeric($invoice_id )) {
			
			$client 		= $db->client($params['uid']);		
			$emailtemp 		= $db->emailTemplate('newinvoice');
			$array['USER'] 	= $client['user'];
			$array['DUE'] 	= strftime("%D", $params['due']);
			$email->send($client['email'], $emailtemp['subject'], $emailtemp['content'], $array);
			$order_id = $params['order_id'];
			if (!empty($order_id)) {
				$insert_sql = "INSERT INTO `<PRE>order_invoices` (order_id, invoice_id) VALUES('{$order_id}', '{$invoice_id}')";				
				$db->query($insert_sql);		
			}			
			$main->addLog("Invoice created: $invoice_id");
			return	$invoice_id;
		}
		return false;		
	}
	
	public function delete($id) { # Deletes invoice upon invoice id	
		$this->updateInvoiceStatus($id, INVOICE_STATUS_DELETED);
		$main->addLog("Invoice id $id deleted ");		
	}
	
	public function edit($id, $params) { # Edit an invoice. Fields created can only be edited?
		$this->setPrimaryKey($id);
		$this->update($params);
		$main->addLog("Invoice updated $id deleted ");	
	}
	
	/**
	 * Pays an invoice
	 * 
	 */
	public function pay($invoice_id, $returnURL = "order/index.php") {
		global $db, $main, $order;
		require_once "paypal/paypal.class.php";
		$paypal 		= new paypal_class();
		$invoice_info 	= $this->getInvoiceInfo($invoice_id);
		$user_id 		= $main->getCurrentUserId();
		
		$order_id 		= $this->getOrderByInvoiceId($invoice_id);
		$order_info		= $order->getOrderInfo($order_id);
		
		if($user_id == $invoice_info['uid']) {
			
			if ($db->config('paypal_mode') == PAYPAL_STATUS_LIVE) {
				$paypal->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';				
			} else {
				$paypal->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			}
			//More infor for paypal variables : https://www.paypal.com/cgi-bin/webscr?cmd=p/pdn/howto_checkout-outside
			
			$paypal->add_field('business', 			$db->config('paypalemail'));					
			$paypal->add_field('return', 			urlencode($db->config('url')."client/index.php?page=invoices&sub=paid&invoiceID=".$invoice_id));
			$paypal->add_field('cancel_return', 	urlencode($db->config('url')."client/index.php?page=invoices&sub=paid&invoiceID=".$invoice_id));
			$paypal->add_field('notify_url',  		urlencode($db->config('url')."client/index.php?page=invoices&sub=paid&invoiceID=".$invoice_id));
			
			$paypal->add_field('item_name', 		$db->config('name').' - '.$order_info['domain'].' Invoice id: '.$invoice_id);
			//$paypal->add_field('item_number', 		$invoice_id);
			$paypal->add_field('invoice', 			$invoice_id);
			$paypal->add_field('no_note', 			0);
			
			$paypal->add_field('no_shipping', 		1);			
			//Image is 150*50
			//$paypal->add_field('image_url', 		'http://www.beeznest.com/sites/all/themes/beeznest/images/logo-beez.png');

			$paypal->add_field('amount', 			$invoice_info['total_amount']);
			$paypal->add_field('currency_code', 	$db->config('currency'));
			$main->addLog("Invoice pay function called Invoice id: $invoice_id Order id: $order_id Total amount: {$invoice_info['total_amount']}");
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
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE `id` = '{$id}'");
		$array = $db->fetch_array($query, 'ASSOC');
		$total_amount = 0;
		
		//Getting addon information
		if (!empty($array['addon_fee'])) {
			//the addon_fee is a serialize string			
			$array['addon_fee'] = unserialize($array['addon_fee']);
			if (is_array($array['addon_fee']) && count($array['addon_fee']) > 0) {		
				foreach($array['addon_fee'] as $addon) {					
					//$addon_fee_string.= $addons_list[$addon['addon_id']].' - '.$addon['amount'].'<br />';
					$total_amount +=$addon['amount'];
				}			
			}
			$array['addon_fee'] = serialize($array['addon_fee']);					
		}
		$total_amount = $total_amount + $array['amount']; 
		$array['total_amount'] = $total_amount;		
		
		return $array;
	}
	
	public function getInvoicesByUser($user_id) {
		global $db;		
		$user_id = intval($user_id);		
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE `uid` = '{$user_id}'");
		$invoice_list = array();
		while ($array = $db->fetch_array($query, 'ASSOC')) {				
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
			$invoice_list[] = $array;
		}	
		return $invoice_list;
	}
	
	
	/**
	 * Gets all invoices 
	 * @return	array 
	 * @author Julio Montoya <gugli100@gmail.com> BeezNest
	 */	 
	public function getAllInvoicesToArray($user_id = 0 , $page = 0) {
		global $main, $db, $style,$currency, $order, $package, $billing, $addon, $user;
		
		$limit = '';
		if (empty($page)) {
			$page = 0;
		}
		
		if (empty($user_id)) {
			$invoice_list	=$this->getAllInvoices('', $page);  
		} else {
			$user_id = intval($user_id);
			$invoice_list	=$this->getAllInvoices($user_id, $page);  
		}	
		
		$result['list'] = "";
		
		//Package info
		$package_list 	= $package->getAllPackages();				
		//Billing cycles
		$billing_list 	= $billing->getAllBillingCycles();		
		//Addons		
		$addon_list		= $addon->getAllAddons();
		
		$total_amount = 0;		           
		 	    	
		foreach($invoice_list as $array) {
			
			//Getting the user info
			if(!empty($array['uid'])) {
				$user_info = $user->getUserById($array['uid']);
				$array['userinfo']  = '<a href="index.php?page=users&sub=search&do='.$user_info['id'].'" >'.$user_info['lastname'].', '.$user_info['firstname'].' ('.$user_info['user'].')</a>';
			} else {
				$array['userinfo']  = '-';
			}
			
			if( !empty($array['due'])) {
				$array['due'] 		= strftime("%D", $array['due']);
			} else {
				$array['due'] = '-';
			}				
			
			//Getting the domain info
			$order_info = $order->getOrderByUser($array['uid']);			
			
			$array['domain'] 	= $order_info['domain'];
			$package_id 	  	= $order_info['pid'];
			$billing_cycle_id 	= $order_info['billing_cycle_id'];			
			
			$addon_fee_string = '';
			if (!empty($array['addon_fee'])) {
				
				$array['addon_fee'] = unserialize($array['addon_fee']);				
				///var_dump($array['addon_fee']);
				if (is_array($array['addon_fee']) && count($array['addon_fee']) > 0) {					
					foreach($array['addon_fee'] as $addon) {
						$addon_fee_string.= $addon_list[$addon['addon_id']]['name'].' - '.$addon['amount'].'<br />';
						$total_amount = $total_amount + $addon['amount'];					
					}
				}					
			}
			
			$array['addon_fee'] = $addon_fee_string;	
			$total_amount = $total_amount + $array['amount'];			
			
			//Get the amount info
			$array['amount'] = $currency->toCurrency($total_amount);			


			//Paid configuration links
			switch ($array['status']) {
				case INVOICE_STATUS_PAID:
					$array['paid']	= "<span style='color:green'>Already Paid</span>";
					$array['pay']	=  "<a href='index.php?sub=all&page=invoices&iid={$array['id']}&unpay=true' title='Mark as Pending'> <img src='../themes/icons/money_delete.png'  width=\"18px\" alt='Mark as Pending' title='Mark as Pending' /> </a>";
					$array['due']	=  '<span style="color:green">'.$array['due'].'</span>' ;
					  
				break;
				case INVOICE_STATUS_CANCELLED:
					$array['paid'] 	= "<span style='color:red'>Cancelled</span>";
					$array['pay'] 	= "<a href='index.php?sub=all&page=invoices&iid={$array['id']}&pay=true' title='Mark as Paid'> <img src='../themes/icons/money_add.png' width=\"18px\" alt='Mark as Paid' title='Mark as Paid' /></a>";
					$array['due']	=  '<span style="color:red">'.$array['due'].'</span>';		
				break;
				case INVOICE_STATUS_WAITING_PAYMENT:
					$array['paid'] 	= "<span style='color:red'>Pending</span>";
					$array['pay']  	= "<a href='index.php?sub=all&page=invoices&iid={$array['id']}&pay=true' title='Mark as Paid'> <img src='../themes/icons/money_add.png' width=\"18px\" alt='Mark as Paid' title='Mark as Paid' /></a>";
					$array['due']	= '<span style="color:red">'.$array['due'].'</span>';		
				break;
				case INVOICE_STATUS_DELETED:
					///	$array['paid'] = "<span style='color:green'>Already Paid</span>";
				break;
				default:
					$array['paid']= '-';
					$array['pay']=  ' <img src="../themes/icons/money_add_na.png" width="18px" alt="Mark as Paid" title="Mark as Paid" /> ';
					$array['due']=  '<span>'.$array['due'].'</span>';						
			}
			$array['package']		 = $package_list[$package_id]['name'];
			$array['billing_cycle']  = $billing_list[$billing_cycle_id]['name'];
			
			$array['edit']  	= '<a href="index.php?page=invoices&sub=edit&do='.$array['id'].'"><img src="../themes/icons/note_edit.png" alt="Edit" /></a>';			
			$array['delete']  	= '<a href="index.php?page=invoices&sub=delete&do='.$array['id'].'"><img src="../themes/icons/delete.png" alt="Delete" /></a>';
			
			$result['list'] .= $style->replaceVar("tpl/invoices/invoice-list-item.tpl", $array);
		}
		return $result;		
	}
	
	public function getAllInvoices($user_id = '', $page = 0) {
		global $db;
		$status = intval($status);
		if (!empty($user_id)) {
			$user_where = " AND uid = $user_id ";
		}
		$limit = '';
		if (!empty($page)) {
			$page = intval($page);
			$per_page = $db->config('rows_per_page');
			$start = ($page-1)*$per_page;
			$limit = "LIMIT $start , $per_page";
		}
		
		$sql = "SELECT * FROM ".$this->getTableName()." WHERE status <> '".INVOICE_STATUS_DELETED."' $user_where ORDER BY id DESC $limit  ";
		$result = $db->query($sql);
		$invoice_list = array();
		if($db->num_rows($result) >  0) {
			while($data = $db->fetch_array($result,'ASSOC')) {
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
		global $main, $db, $currency, $addon, $email, $package, $order, $user,$billing;	
		
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE `id` = '{$invoice_id}'");
		if($db->num_rows($query) == 0) {
			echo "That invoice doesn't exist!";	
		} else {
			
			$total = 0;			
			$invoice_info 		= $db->fetch_array($query);
			
			$array['ID'] 		= $invoice_info['id'];
			$user_id 			= $invoice_info['uid'];
			$total				= $total + $invoice_info['amount'];
			
			//User info
			$user_info = $user->getUserById($user_id);
			//Invoice status list
			$invoice_status = $main->getInvoiceStatusList();
						
			$array['USER'] 		=  $user_info['lastname'].', '.$user_info['firstname'].' ('.$user_info['user'].')';				
			
			
			if ($read_only == true) {
				$array['STATUS'] 	= $invoice_status[$invoice_info['status']];
			} else {
				$array['STATUS'] 	= $main->createSelect('status', $invoice_status, $invoice_info['status']);
			}			
			
			$array['CREATED'] 	= $invoice_info['created'];
			$array['NOTES'] 	= $invoice_info['notes'];	
			$array['DUE'] 		= date('Y-m-d', $invoice_info['due']);
		
			//$array['ADDON_FEE'] = $invoice_info['addon_fee'];
			$addon_selected_list = array();
			if (!empty($invoice_info['addon_fee'])) {				
				/**
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
						$addon_selected_list[$addon_item['addon_id']]= $addon_item['amount'];
					}							
				}
			}							
			
			$order_id 	= $this->getOrderByInvoiceId($invoice_id);			
			$order_info = $order->getOrderInfo($order_id);			
				
			$array['domain'] 	= $order_info['domain'];
			$package_id 	  	= $order_info['pid'];
			$billing_cycle_id 	= $order_info['billing_cycle_id'];
							
						
			//Addon feature added			
			$array['ADDON'] = ' - ';
			$addong_result_string = '';			
			$addon_list = $addon->getAddonsByPackage($package_id);		
			$addon_list = $addon->getAllAddonsByBillingId($billing_cycle_id);
			
			$array['ADDON']='-';
			
			if (is_array($addon_selected_list) && count($addon_selected_list) > 0) {
				$array['ADDON']='<fieldset style="width:200px">';
				foreach($addon_selected_list as $addon_id => $addon_amount) {
					if ($read_only == false) {
						$array['ADDON'] .= $addon_list[$addon_id]['name'].'<br /><input id="addon_'.$addon_id.'" name="addon_'.$addon_id.'" value="'.$addon_amount.'"><br />';				
					} else {
						$array['ADDON'] .= $addon_list[$addon_id]['name'].' - '.$currency->toCurrency($addon_amount).'<br />';
					}
					$total = $total + $addon_amount;
				}	
				$array['ADDON'].='</fieldset>';			
			}
			//Packages feature added	
			$package_list = $package->getAllPackages();			
	
			$array['PACKAGE_ID']	 = $package_id;
			$array['PACKAGE_NAME']	 = $package_list[$package_id]['name'];
			
			if ($read_only) {
				$array['PACKAGE_AMOUNT'] = $currency->toCurrency($invoice_info['amount']);
			} else {
				$array['PACKAGE_AMOUNT'] = $invoice_info['amount'];
			}
			
			//Billing cycle
			$billing_list = $billing->getAllBillingCycles();

			
			if ($read_only) {
				$array['BILLING_CYCLES'] = $billing_list[$billing_cycle_id]['name'];
			} else {
				$array['BILLING_CYCLES'] = $billing_list[$billing_cycle_id]['name'].'<input type="hidden" id="billing_id" name="billing_id" value="'.$billing_cycle_id.'">';
			}
			
			$query = $db->query("SELECT * FROM `<PRE>order_invoices` WHERE `invoice_id` = '{$invoice_id}'");
			$order_item = $db->fetch_array($query);
			
			if (!empty($order_item['order_id'])) {
				$array['ORDER_ID'] = $order_item['order_id'];
			} else {
				$array['ORDER_ID'] = ' - ';
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
		$debug = true;
		global $db, $main, $server, $billing, $invoice, $order, $email, $user;
		$today = time();
		
		$main->addLog('Cron executed');
				
		//Gets all orders 
		$orders 			 =  $order->getAllOrders();
		$invoice_list_status = $main->getInvoiceStatusList();
		$order_list_status	 = $main->getOrderStatusList();
			
		if($debug) {
			echo '<h1>Invoice Cron</h1><br />';
			echo 'Total others: '.count($orders).'<br />';
		}		
			
		foreach($orders as $order_item) {
			
			//If the Order was deleted pass to the next order
			if ($order_item['status'] == ORDER_STATUS_DELETED) {
				continue;
			}
			
			if ($debug) { echo '<h2>Order id:'.$order_item['id'].'</h2>';}	
			
			//Get the last invoice of that order
			$last_invoice_id_by_order_id = $order->getLastInvoiceByOrderId($order_item['id']);
			$user_id = $order_item['userid'];
			
			// GEtting the info of that invoice
			if (!empty($last_invoice_id_by_order_id)) {
				
				if ($debug) { echo '<h2>Invoice id:'.$last_invoice_id_by_order_id.'</h2>';}
				
				//Get invoice info					
				$my_invoice 	= $invoice->getInvoiceInfo($last_invoice_id_by_order_id);					
				
				//Get billing info
				$billing_info	= $billing->getBilling($order_item['billing_cycle_id']);					
				
				//$uid = intval($order_item['userid']);
				//Select the *last* invoice of that order
				//$sql = "SELECT * FROM `<PRE>invoices` WHERE `uid` = '{$uid}' AND status <> '".INVOICE_STATUS_DELETED."'ORDER BY id DESC LIMIT 1";
				//$result_invoices = $db->query($sql);				
				
				
				//If today is bigger than 30 days since the creation then create a new invoice
				$billing_number_months_in_seconds  = intval($billing_info['number_months'])*30*24*60*60; 
									
								
				//Generate a new invoice if time is exceed (first time)
				//var_dump($today, strtotime($my_invoice['created']) + $billing_number_months_in_seconds);
				if ($debug) {
					echo 'Today is  : '.date('Y-m-d', time());
					echo '<br />Invoice info :<br />';					
					echo 'Created date	:'.date('Y-m-d', strtotime($my_invoice['created'])).'<br />';
					echo 'Due date	:'.date('Y-m-d', $my_invoice['due']).'<br />';
				}
				//"Terminating" a user if he does not pay
				
				//Check the suspension days parameter	
				$suspendseconds 	= intval($db->config('suspensiondays')) *24*60*60;
				
				//I don't want to use this termination parameter. Not a priority
				$terminateseconds 	= intval($db->config('terminationdays'))*24*60*60;				
				
				
				//If normal time is over and he does not paid
		//		var_dump(date('Y-m-d h:i:s',$today), date('Y-m-d h:i:s', strtotime($my_invoice['created']) + $billing_number_months_in_seconds));
				//If the package is paid 
				//if ($package['paid']) {
					
				//If the invoices isn't paid == is pending
				//if ($my_invoice['status'] != INVOICE_STATUS_PAID ) {
				
				//If the invoice was deleted pass to the next order
					
				if ($debug) { echo '<br/>Invoice status: ' .$invoice_list_status [$my_invoice['status']].' - Id  '.$my_invoice['status']; }
				
				$email_day_count = $this->calculateDaysToSendNotification($billing_info['number_months'], $my_invoice['due']);
				var_dump($email_day_count);
				
				switch($my_invoice['status']) {
					case INVOICE_STATUS_WAITING_PAYMENT: //Pending
						
						//1. Due date of the last invoice has exceed
						if ($my_invoice['due'] < $today) {
							
							echo 'Invoice due is bigger than today so we suspend the hosting';
							
							//My invoice has expired need to suspend if time has past the limits	
												
							//echo 'My invoice has expired need to suspend if time has past<br />';
							
							//Suspend conditions
							var_dump('Suspend : '.date('Y-m-d', $today - $suspendseconds));
							//Terminate conditions
							//var_dump('Terminate: '.date('Y-m-d h:i:s',$today-$suspendseconds-$terminateseconds));
							
							$is_terminate = false;							
							//Terminate is a bad thing we would do nothing
							
							/*						
							if(($today-$suspendseconds-$terminateseconds) > intval($my_invoice['due']) ){
								echo 'Terminate <br />';
								$server->terminate($order_item['id']);
								$is_terminate = true;
							} */			
											
							//2. Proceed to suspend							 
							if ($is_terminate == false ) {
								if(($today - $suspendseconds) > intval($my_invoice['due'])) {
									echo 'Checking the tolerance (suspensiondays variable) <br />';
									echo 'Order Suspended <br />';
									$server->suspend($order_item['id']);
								}
							}			
						}
						//Send a notification
						if (!empty($email_day_count)) {
							//If the invoices is pending and the user do nothing
						}							
					break;
					
					case INVOICE_STATUS_PAID:							
						//If I'm already pay	
						//Check the invoice creation date + 30 days (monthly)
						//  29                    2010-06-28                  30   
						//var_dump(date('Y-m-d', strtotime($my_invoice['created']) + $billing_number_months_in_seconds));
						$mytemp = date('Y-m-d', $my_invoice['due'] + $billing_number_months_in_seconds);
						
						if($today > $my_invoice['due'] + $billing_number_months_in_seconds) {														
							//	var_dump($my_new_invoice);							
							if ($debug) echo '<br />Invoice created because the due date of the last invoice '.date('Y-m-d',$my_invoice['due']).' +  '.$billing_info['number_months'].  '  months  = '.$mytemp.' is smaller than today '.date('Y-m-d',$today).' <br />';
							//$this->create($uid, $my_new_invoice['amount'], $today + intval($db->config('suspensiondays')*24*60*60), $my_new_invoice['notes'], $my_new_invoice['addon_fee']);						
							$invoice_id = $this->create($user_id, $my_invoice['amount'], $today + $billing_number_months_in_seconds, $my_invoice['notes'], $my_invoice['addon_fee'], INVOICE_STATUS_WAITING_PAYMENT,  $order_item['id']); //will send an email to the user

							$main->addLog('Invoice created Id:'.$invoice_id);
							
						} else {
							if ($debug) echo '<br />Invoice Not created because the due date of the last invoice '.date('Y-m-d',$my_invoice['due']).' +  '.$billing_info['number_months'].  '  months  = '.$mytemp.'  is greater than today - '.date('Y-m-d',$today).' this means that the invoice was already created and active<br />';								
						}
						echo 'Send email if no empty: $email_day_count';							
						
						//Generating email notification
						if (!empty($email_day_count)) {
							
							$user_info = $user->getUserById($user_id);
							$emaildata = $db->emailTemplate('renewalnotice');
							//var_dump($emaildata);
							
							$invoice_info = $this->getInvoice($my_invoice['id'], true); 
												
							$replace_array['USERNAME'] 				=  $user_info['firstname'].' '.$user_info['lastname'];
							$replace_array['INVOICE_CREATE_DATE'] 	=  substr($invoice_info['CREATED'], 0,10);
							
							$replace_array['PAYMENT_METHOD'] 		=  'Paypal';
							$replace_array['DOMAIN'] 				=  $order_item['domain'];
							
							$replace_array['BILLING_CYCLE'] 		=  $invoice_info['BILLING_CYCLE'];
							$replace_array['INVOICE_NUMBER'] 		=  $invoice_info['ID'];
							$replace_array['AMOUNT'] 				=  $invoice_info['TOTAL'];
							$replace_array['INVOICE_DUE_DATE'] 		=  substr($invoice_info['DUE'], 0, 10);
							$replace_array['PACKAGE_INFO'] 			=  $invoice_info['PACKAGE_NAME'].' - '.$invoice_info['PACKAGE_AMOUNT'];
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
							
							$main->addLog('Reminder sent to '.$user_info['email'].' - Subject: '.$emaildata['subject']);	
						}							
					break;						
					case INVOICE_STATUS_CANCELLED:
					case INVOICE_STATUS_DELETED:
						//When the invoice is deleted or cancelled we do *nothing* pass to the next order
						continue;
					default:						
				}	
			} else {
				if ($debug) { echo '<h3>No Invoice id</h3><br />';}
			}
		}
	}
	
	public function calculateDaysToSendNotification($number_months, $my_due_date) {
		
		$before_list_of_days = array();
		/*
		switch ($number_months) {
			case 1 :
				$before_list_of_days = array(7);	
			break;
			case $number_months >=  2 :
				$before_list_of_days = array(7);
				for($i=1; $i< $number_months; $i++) {
					$before_list_of_days[]= $i*30;
				}
			break;							
		}*/
		switch ($number_months) {
			case 1 :
				$before_list_of_days = array(7);	
			break;					
			case 2 :
				$before_list_of_days = array(7,30);	
			break;
			case $number_months >=  3 && $number_months <=  6 :
				$before_list_of_days = array(7, 30, 60);	
			break;
			case $number_months > 6 :
				$before_list_of_days = array(7, 30, 60, 180);
			break;						
		}				
						
		sort($before_list_of_days);
		
		//var_dump($before_list_of_days);
					
		$email_day_count = '';
							
		//$my_invoice['due'] = '1280451661';							
		
		echo '<br /><br />Searching if today we are going to shoot an email:<br />';
		echo '$number_months = '.$number_months.'<br />';
		
		foreach($before_list_of_days as $days) {
			//var_dump($days);
			$my_time_in_seconds = $my_due_date - $days*24*60*60;
			$date_now = date('Y-m-d', time());
			$due_date = date('Y-m-d', $my_time_in_seconds);
			
			echo $date_now.' - Shoots an email in : '.$due_date.' - Calculated for '.$days.' days<br />';
			if ($date_now  == $due_date  ) {
				$email_day_count = $days;
				break;
			}						
		}
		return $email_day_count;
	}
	
	public function getLastInvoiceByUser($user_id) {
		global $db;
		$user_id = intval($user_id);
		$sql = "SELECT id FROM ".$this->getTableName()." WHERE `uid` = ".$user_id." ORDER BY id DESC LIMIT 1";
		
		$result	= $db->query($sql);
		$invoice = array();
		if ($db->num_rows($result) > 0 ) {		
			$invoice = $db->fetch_array($result);			
		}
		return $invoice;
	}
	
	public function set_paid($invoice_id) { # Pay the invoice by giving invoice id
		//global  $server, $invoice;
		$this->updateInvoiceStatus($invoice_id, INVOICE_STATUS_PAID);
		//$order_id = $this->getOrderByInvoiceId($invoice_id);
		//$server->unsuspend($order_id);		
	}
	
	public function set_unpaid($invoice_id) { # UnPay the invoice by giving invoice id - Don't think this will be useful
		///global $server;
		$this->updateInvoiceStatus($invoice_id, INVOICE_STATUS_WAITING_PAYMENT);
		//$order_id = $this->getOrderByInvoiceId($invoice_id);	
		//$server->suspend($order_id);	
	}
	
	
	public function getOrderByInvoiceId($invoice_id) {
		global $db;
		$query = $db->query("SELECT order_id FROM `<PRE>order_invoices` WHERE `invoice_id` = '{$invoice_id}' LIMIT 1");
		$data = $db->fetch_array($query);
		return $data['order_id'];
	}
	
	public function is_paid($id) { # Is the invoice paid - True = Paid / False = Not
		global $db;
		$data = $db->fetch_array($db->query("SELECT status FROM ".$this->getTableName()." WHERE `id` = '{$id}'"));
		if($data['status'] == INVOICE_STATUS_PAID) {
			return true;	
		} else {
			return false;	
		}
	}
	
	public function updateInvoiceStatus($invoice_id, $status) {
		global $main;		
		$this->setPrimaryKey($invoice_id);
		$invoice_status = array_keys($main->getInvoiceStatusList());		
		if (in_array($status, $invoice_status)) {		
			$params['status'] = $status;
			$this->update($params);
		}		
	}
}