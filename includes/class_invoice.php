<?php
/* For licensing terms, see /license.txt */

class invoice extends model {
	
	public $columns 	= array('id', 'uid','amount', 'is_paid','created', 'due', 'is_suspended', 'notes', 'uniqueid', 'addon_fee', 'status', 'transaction_id');	
	public $table_name 	= 'invoices';	
	//public $_modelName 	= 'invoice';
	
	function __construct() {
		
	}
	
	/**
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($params) {
		global $main, $db, $user, $email, $order;
		
		$params['created'] = date('Y-m-d h:i:s');		
		$invoice_id = $this->save($params);
		
		$order_id 	= intval($params['order_id']);
		
		if (!empty($invoice_id) && is_numeric($invoice_id)) {
			$main->addLog("invoice::create $invoice_id");
			
			if (!empty($order_id)) {
				$params['order_id'] 	= $order_id;
				$params['invoice_id'] 	= $invoice_id;
				$order->order_invoices->save($params);
			}			
			
			$user_info 		= $user->getUserById($params['uid']);		
			$emaildata 		= $db->emailTemplate('invoices_new');	
			$order_info     = $order->getOrderInfo($order_id);
			$invoice_info 	= $this->getInvoice($invoice_id, true);	
							
			$replace_array['USERNAME'] 				=  $user->formatUsername($user_info['firstname'], $user_info['lastname']);
			$replace_array['INVOICE_CREATE_DATE'] 	=  substr($invoice_info['CREATED'], 0,10);
			
			$replace_array['PAYMENT_METHOD'] 		=  'Paypal';
			$replace_array['DOMAIN'] 				=  $order_info['real_domain'];
			
			$replace_array['BILLING_CYCLE'] 		=  $invoice_info['BILLING_CYCLES'];
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
							
			$email->send($user_info['email'], $emaildata['subject'], $emaildata['content'], $replace_array);
			
			
			
			return	$invoice_id;		
		}
		return false;		
	}
	
	public function delete($id) { # Deletes invoice upon invoice id
		global $main;	
		$this->updateInvoiceStatus($id, INVOICE_STATUS_DELETED);
		$main->addLog("invoice::delete id #$id ");		
	}
	
	public function edit($id, $params) { # Edit an invoice. Fields created can only be edited?
		global $main;
		$this->setId($id);
		$this->update($params);
		$main->addLog("invoice::edit #$id updated");	
		$this->loadHook(__FUNCTION__, $params);
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
		if (!empty($invoice_info)) {
			if ($user_id == $invoice_info['uid']) {
				
				if ($db->config('paypal_mode') == PAYPAL_STATUS_LIVE) {
					$paypal->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';				
				} else {
					$paypal->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				}
				//More infor for paypal variables : https://www.paypal.com/cgi-bin/webscr?cmd=p/pdn/howto_checkout-outside
				
				$paypal->add_field('business', 			$db->config('paypalemail'));
				
				// Will only work if Auto Return is set in the Paypal account  								
				$paypal->add_field('return', 			urlencode($db->config('url')."client/index.php?page=invoices&sub=view&p=success&do=$invoice_id")); // Paypal Sucess
				
				$paypal->add_field('cancel_return', 	urlencode($db->config('url')."client/index.php?page=invoices&sub=view&p=cancel&do=".$invoice_id)); // Paypal Cancel 
				
				$paypal->add_field('notify_url',  		urlencode($db->config('url')."includes/paypal/ipn.php?do=".$invoice_id)); // IPN
				
				$paypal->add_field('item_name', 		$db->config('name').' - '.$order_info['real_domain'].' Invoice id: '.$invoice_id);
				$paypal->add_field('invoice', 			$invoice_id); //When trying to buy something with the same Invoice id Paypal will send a message that the invoice was already done 
				$paypal->add_field('no_note', 			0);			
				$paypal->add_field('no_shipping', 		1);
				
				$paypal->add_field('continue_button_text', 'Continue >>');
				$paypal->add_field('cbt', 'Continue >>');			
				
				$paypal->add_field('background_color', ''); //""=white 1=black
				$paypal->add_field('display_shipping_address', '1'); //""=yes 1=no
				$paypal->add_field('display_comment', '1'); //""=yes 1=no
							
				//Image is 150*50px otherwise the image will not work
				//@todo add a new paypal parameter to the URL image 
				//$paypal->add_field('image_url', 		'http://demo.contidos.cblue.be/logo-beez.png');			
	
				$paypal->add_field('amount', 			$invoice_info['total_amount']);
				$paypal->add_field('currency_code', 	$db->config('currency'));
				
				$main->addLog("invoice::pay Invoice #$invoice_id Order #$order_id Total amount: {$invoice_info['total_amount']}");
				$paypal->submit_paypal_post(); // submit the fields to paypal
			} else {
				echo "You don't seem to be the person who owns that invoice!";	
			}
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
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE id = '{$id}'");
		$array = array();
		if ($db->num_rows($query) > 0) {
			$array = $db->fetch_array($query, 'ASSOC');
			$total_amount = 0;			
			//Getting addon information
			if (!empty($array['addon_fee'])) {
				//the addon_fee is a serialize string
				$array['addon_fee'] = @unserialize($array['addon_fee']);
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
		}
		$this->loadHook(__FUNCTION__, null);
		return $array;
	}
	
	public function getInvoicesByUser($user_id) {
		global $db;		
		$user_id = intval($user_id);		
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE uid = '{$user_id}'");
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
	public function getAllInvoicesToArray($user_id = 0 , $page = 0, $status_id = 0) {
		global $main, $db, $style,$currency, $order, $package, $billing, $addon, $user;
		
		$limit = '';
		if (empty($page)) {
			$page = 0;
		}
		
		if (empty($status_id)){
			$status_id = 0;
		} else {
			$status_id = intval($status_id);
		}
		
		if (empty($user_id)) {
			$invoice_list	= $this->getAllInvoices('', $page, $status_id);  
		} else {
			$user_id = intval($user_id);
			$invoice_list	= $this->getAllInvoices($user_id, $page, $status_id);  
		}	
		
		$result['list'] = '';
		
				           
		//Package info
		$package_list 	= $package->getAllPackages();				
		//Billing cycles
		$billing_list 	= $billing->getAllBillingCycles();		
		//Addons		
		$addon_list		= $addon->getAllAddons();		
		//Subdomain list
		$subdomain_list = $main->getSubDomains();
		
		foreach($invoice_list as $array) {
			
			$total_amount = 0;
			
			//Getting the user info
			if(!empty($array['uid'])) {
				$user_info = $user->getUserById($array['uid']);
				$array['userinfo']  = '<a href="index.php?page=users&sub=search&do='.$user_info['id'].'" >';
				$array['userinfo']  .= $user->formatUsername($user_info['firstname'], $user_info['lastname'], $user_info['user']).'</a>';
			} else {
				$array['userinfo']  = '-';
			}
			
			if( !empty($array['due'])) {
				$array['due'] 		= strftime("%D", $array['due']);
			} else {
				$array['due'] = '-';
			}				
			
			//Getting the k info
			$order_id 		 = $this->getOrderByInvoiceId($array['id']);
			$order_info 	 = $order->getOrderInfo($order_id);
			if (!empty($order_info)) {			
				$array['domain'] 	= $order_info['real_domain'];					
				$package_id 	  	= $order_info['pid'];
				$billing_cycle_id 	= $order_info['billing_cycle_id'];
			}			
			
			$addon_fee_string = '';
			//@todo fix the addon_fee
			if (!empty($array['addon_fee']) && $array['addon_fee'] != 'Array') {				
				
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
					//$array['pay']	=  "<a href='index.php?sub=all&page=invoices&iid={$array['id']}&unpay=true' title='Mark as Pending'> <img src='../themes/icons/money_delete.png'  width=\"18px\" alt='Mark as Pending' title='Mark as Pending' /> </a>";
					$array['pay'] 	= '';
					$array['due']	=  '<span style="color:green">'.$array['due'].'</span>' ;
					  
				break;
				case INVOICE_STATUS_CANCELLED:
					$array['paid'] 	= "<span style='color:red'>Cancelled</span>";
					//$array['pay'] 	= "<a href='index.php?sub=all&page=invoices&iid={$array['id']}&pay=true' title='Mark as Paid'> <img src='../themes/icons/money_add.png' width=\"18px\" alt='Mark as Paid' title='Mark as Paid' /></a>";
					$array['pay'] 	= '';
					$array['due']	=  '<span style="color:red">'.$array['due'].'</span>';		
				break;
				case INVOICE_STATUS_WAITING_PAYMENT:
					$array['paid'] 	= "<span style='color:red'>Pending</span>";
					//$array['pay']  	= "<a href='index.php?sub=all&page=invoices&iid={$array['id']}&pay=true' title='Mark as Paid'> <img src='../themes/icons/money_add.png' width=\"18px\" alt='Mark as Paid' title='Mark as Paid' /></a>";
					$array['pay'] 	= '';
					$array['due']	= '<span style="color:red">'.$array['due'].'</span>';		
				break;
				case INVOICE_STATUS_DELETED:
					///	$array['paid'] = "<span style='color:green'>Already Paid</span>";
				break;
				default:
					$array['paid']= '-';
					//$array['pay']=  ' <img src="../themes/icons/money_add_na.png" width="18px" alt="Mark as Paid" title="Mark as Paid" /> ';
					$array['pay'] 	= '';
					$array['due']=  '<span>'.$array['due'].'</span>';						
			}
			$array['package']		 = $package_list[$package_id]['name'];
			$array['billing_cycle']  = $billing_list[$billing_cycle_id]['name'];
			
			$array['edit']  	= '<a href="index.php?page=invoices&sub=edit&do='.$array['id'].'"><img src="../themes/icons/pencil.png" alt="Edit" /></a>';			
			$array['delete']  	= '<a href="index.php?page=invoices&sub=delete&do='.$array['id'].'"><img src="../themes/icons/delete.png" alt="Delete" /></a>';
			
			$result['list'] .= $style->replaceVar("tpl/invoices/invoice-list-item.tpl", $array);
		}
		return $result;		
	}
	
	public function getAllInvoices($user_id = '', $page = 0, $status_id = 0) {
		global $db;
		
		$user_where = '';
		if (!empty($user_id)) {
			$user_id 	= intval($user_id);
			$user_where = " AND uid = $user_id ";
		}
		
		$status_where = '';
		if (!empty($status_id)) {
			$status_id 	= intval($status_id);
			$status_where = " AND status = $status_id ";
		}
		
		$limit = '';
		if (!empty($page)) {
			$page = intval($page);
			$per_page = intval($db->config('rows_per_page'));
			$start = ($page-1)*$per_page;
			if ($start < 0) {
				$start = 0;
			}
			$limit = "LIMIT $start , $per_page";
		}		
		$sql = "SELECT * FROM ".$this->getTableName()." WHERE status <> '".INVOICE_STATUS_DELETED."' $user_where $status_where ORDER BY id DESC $limit  ";
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
		$invoice_id = intval($invoice_id);
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE id = $invoice_id ");
		if($db->num_rows($query) == 0) {
			echo "That invoice doesn't exist!";	
		} else {			
			$total = 0;			
			$invoice_info 		= $db->fetch_array($query, 'ASSOC');		
			$array['ID'] 		= $invoice_info['id'];
			$user_id 			= $invoice_info['uid'];
			$total				= $total + $invoice_info['amount'];
						
			//User info
			$user_info = $user->getUserById($user_id);
			//Invoice status list
			$invoice_status = $main->getInvoiceStatusList();						
			$array['USER'] 		=  $user->formatUsername($user_info['firstname'], $user_info['lastname'], $user_info['user']);
			
			if ($read_only == true) {
				$array['STATUS'] 	= $invoice_status[$invoice_info['status']];
			} else {
				$array['STATUS'] 	= $main->createSelect('status', $invoice_status, $invoice_info['status']);
			}			
			
			$array['CREATED'] 	= $invoice_info['created'];
			$array['NOTES'] 	= $invoice_info['notes'];	
			$array['DUE'] 		= date('Y-m-d', $invoice_info['due']);
		
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
				$invoice_info['addon_fee'] = @unserialize($invoice_info['addon_fee']);
				
				if (is_array($invoice_info['addon_fee']) && !empty($invoice_info['addon_fee'])) {							
					foreach ($invoice_info['addon_fee'] as $addon_item) {						
						$addon_selected_list[$addon_item['addon_id']]= $addon_item['amount'];
					}							
				}
			}							
						
			$order_id 	= $this->getOrderByInvoiceId($invoice_id);	
					
			$order_info = $order->getOrderInfo($order_id);
			
			
			$array['DOMAIN'] 		= isset($order_info['domain']) ? $order_info['domain'] : '';
			$array['REAL_DOMAIN'] 	= isset($order_info['real_domain']) ? $order_info['real_domain'] : ''; 
			$package_id 	  		= $order_info['pid'];
			$billing_cycle_id 		= $order_info['billing_cycle_id'];							
						
			//Addon feature added			
			$array['ADDON'] = ' - ';
			$addong_result_string = '';			
			$addon_list = $addon->getAddonsByPackage($package_id);		
			$addon_list = $addon->getAllAddonsByBillingId($billing_cycle_id);
			
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
			
			$query = $db->query("SELECT * FROM <PRE>order_invoices WHERE invoice_id = $invoice_id ");
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
		$main->addLog('invoice::cron executed');
				
		//Getting all orders 
		$orders 			 = $order->getAllOrders();
		$invoice_list_status = $main->getInvoiceStatusList();
		$order_list_status	 = $main->getOrderStatusList();
			
		if($debug) {
			echo '<h1>Invoice Cron</h1><br />';			
			echo 'Today is  : '.date('Y-m-d', time()).'<br />';			
			echo 'Total others: '.count($orders).'<br />';
		}		
		
		if(is_array($orders) && count($orders) >0)
		foreach($orders as $order_item) {
			
			//If the Order was deleted pass to the next order
			if ($order_item['status'] == ORDER_STATUS_DELETED) {
				continue;
			}
			
			if ($debug) { echo '<h2>Order id:'.$order_item['id'].'</h2>';}	
			
			//Get the last invoice of this order
			$last_invoice_id_by_order_id = $order->getLastInvoiceByOrderId($order_item['id']);
			$user_id = $order_item['userid'];
			$user_info = $user->getUserById($user_id);
			
			//If invoice exists 
			if (!empty($last_invoice_id_by_order_id)) {
				
				if ($debug) { echo '<h3>Latest Invoice id: '.$last_invoice_id_by_order_id.'</h3>';}
				
				//Get invoice info					
				$my_invoice 	= $invoice->getInvoiceInfo($last_invoice_id_by_order_id);				
				
				if (empty($my_invoice)) {
					echo 'Invoice does not exist';
					continue;					
				}			
				
				//Get billing info
				$billing_info	= $billing->getBilling($order_item['billing_cycle_id']);					
			
				
				//Calculating the billing time
				$billing_number_months_in_seconds  = intval($billing_info['number_months'])*30*24*60*60; 
																	
				//Generate a new invoice if time is exceed (first time)
				//var_dump($today, strtotime($my_invoice['created']) + $billing_number_months_in_seconds);
				if ($debug) {					
					echo '<strong>Invoice info :</strong><br />';					
					echo 'Created at : '.date('Y-m-d', strtotime($my_invoice['created'])).'<br />';
					echo '<strong>Due     date	: '.date('Y-m-d', $my_invoice['due']).'</strong><br />';
				}
				//"Terminating" a user if he does not pay
				
				//Check the suspension days parameter	
				$suspendseconds 	= intval($db->config('suspensiondays')) *24*60*60;
				
				//I don't want to use this termination parameter. Not a priority
				//$terminateseconds 	= intval($db->config('terminationdays'))*24*60*60;
				
				//If normal time is over and he does not paid
		//		var_dump(date('Y-m-d h:i:s',$today), date('Y-m-d h:i:s', strtotime($my_invoice['created']) + $billing_number_months_in_seconds));
				//If the package is paid 
				//if ($package['paid']) {
					
				//If the invoices isn't paid == is pending
				//if ($my_invoice['status'] != INVOICE_STATUS_PAID ) {
				
				//If the invoice was deleted pass to the next order
					
				if ($debug) { echo '<br/>Invoice status: <strong>' .$invoice_list_status [$my_invoice['status']].'</strong> (Status Id  '.$my_invoice['status'].') '; }
				if ($my_invoice['status'] == INVOICE_STATUS_DELETED) {
					continue;					
				}			
				$email_day_count = $this->calculateDaysToSendNotification($billing_info['number_months'], $my_invoice['due'], $debug);
								
				switch($my_invoice['status']) {
					case INVOICE_STATUS_WAITING_PAYMENT: //Pending
						
						//1. Checks the INVOICE DUE DATE
						
						//Check if the due date is dead if so, we suspend the service	
			
						if ($today > $my_invoice['due']) {
							
							echo 'Today is bigger than the Invoice due date, so we suspend the Order (Site)';
							
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
									$result = $order->updateOrderStatus($order_item['id'], ORDER_STATUS_CANCELLED);															
									//@todo send an email
								}
							}			
						}
						
						//2. Checks the INVOICE CREATE DATE
						
						//Send a reminder to users that have an Invoice but he does not pay check every 2 days
						$days = 2;						
						$send_reminder_every = $days*24*60*60; //1 day
						$time_passed = strtotime($my_invoice['created']) + $send_reminder_every;			
						$time_passed_to_date = date('Y-m-d', $time_passed);
						
						if( $time_passed < $today ) {
							echo "<br />Send reminder because the invoice was created on ".$my_invoice['created']." and user does not pay til ".$time_passed_to_date;		
							$emailtemp 	= $db->emailTemplate('invoices_pending');									
							$array['USERNAME']	= $user->formatUsername($user_info['firstname'], $user_info['lastname']);						
							$array['INVOICE_ID'] = $my_invoice['id'];							
							$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);	
						}						
						
						//Send a notification
						if (!empty($email_day_count)) {
							//If the invoices is pending and the user do nothing
						}							
					break;
					
					case INVOICE_STATUS_PAID:							
						//If I'm already pay	
						//Check the invoice creation date + billing cycle of the order
						//  29                    2010-06-28                  30   
						//var_dump(date('Y-m-d', strtotime($my_invoice['created']) + $billing_number_months_in_seconds));
						$mytemp = date('Y-m-d', $my_invoice['due'] + $billing_number_months_in_seconds);
						
						if($today > $my_invoice['due'] + $billing_number_months_in_seconds) {
							if ($debug) echo '<br />Invoice created because the due date of the last invoice '.date('Y-m-d',$my_invoice['due']).' +  '.$billing_info['number_months'].  '  months  = '.$mytemp.' is smaller than today '.date('Y-m-d',$today).' <br />';
							//$this->create($uid, $my_new_invoice['amount'], $today + intval($db->config('suspensiondays')*24*60*60), $my_new_invoice['notes'], $my_new_invoice['addon_fee']);
							$invoice_params['uid'] 		= $user_id;
							$invoice_params['amount'] 	=$my_invoice['amount'];							
							$invoice_params['due'] 		= $today + $billing_number_months_in_seconds;
							$invoice_params['notes'] 	= $my_invoice['notes'];
							$invoice_params['addon_fee']= $my_invoice['addon_fee'];
							$invoice_params['status'] 	= INVOICE_STATUS_WAITING_PAYMENT;
							$invoice_params['order_id'] = $order_item['id'];
							 						
							$invoice_id = $this->create($invoice_params);
							$main->addLog('invoice::cron  Invoice Id created #'.$invoice_id);							
						} else {
							if ($debug) echo '<br />Invoice Not created because the due date of the last invoice '.date('Y-m-d',$my_invoice['due']).' +  '.$billing_info['number_months'].  '  months  = '.$mytemp.'  is greater than today ('.date('Y-m-d',$today).') this means that the invoice is already Active<br />';								
						}
						
						//Generating email notification
						if (!empty($email_day_count)) {
							
							$user_info = $user->getUserById($user_id);
							$emaildata = $db->emailTemplate('invoices_renewal'); 
							//var_dump($emaildata);
							
							$invoice_info = $this->getInvoice($my_invoice['id'], true); 
												
							$replace_array['USERNAME'] 				=  $user->formatUsername($user_info['firstname'], $user_info['lastname']);
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
				if ($debug) { echo '<h3>No Invoice id. This is weird check this order.</h3><br />';}
			}
		}
	}
	
	public function calculateDaysToSendNotification($number_months, $my_due_date, $debug) {
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
		if ($debug) {
			echo '<br /><br />Searching if today we are going to send a reminder:<br />';
			echo 'Number of months for this Order : '.$number_months.'<br /><br />';
		}
		
		foreach($before_list_of_days as $days) {
			//var_dump($days);
			$my_time_in_seconds = $my_due_date - $days*24*60*60;
			$date_now = date('Y-m-d', time());
			$due_date = date('Y-m-d', $my_time_in_seconds);
			if ($debug) 
				echo 'Today is: '.$date_now.' The script will send an email in : '.$due_date.' - '.$days.' days reminder before the due date ';
				
			if ($date_now  == $due_date  ) {
				$email_day_count = $days;
				break;
			}
			
			if ($debug) 
			if (empty($email_day_count)) {
				echo '(Nothing to send)<br/>';	
			} else {
				echo '(Sent an email Today the due date dies '.$email_day_count.')';
			}
		}
		return $email_day_count;
	}
	
	public function getLastInvoiceByUser($user_id) {
		global $db;
		$user_id = intval($user_id);
		$sql = "SELECT id FROM ".$this->getTableName()." WHERE uid = ".$user_id." ORDER BY id DESC LIMIT 1";
		
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
	
	/**
	 * Gets the order of the invoice
	 */
	public function getOrderByInvoiceId($invoice_id) {
		global $db;
		$invoice_id = intval($invoice_id);
		$query = $db->query("SELECT order_id FROM <PRE>order_invoices WHERE invoice_id = '{$invoice_id}' LIMIT 1");
		$data = $db->fetch_array($query);
		return $data['order_id'];
	}
	
	public function is_paid($id) { # Is the invoice paid - True = Paid / False = Not
		global $db;
		$id = intval($id);
		$data = $db->fetch_array($db->query("SELECT status FROM ".$this->getTableName()." WHERE id = '{$id}'"));
		if($data['status'] == INVOICE_STATUS_PAID) {
			return true;	
		} else {
			return false;	
		}
	}
	
	/**
	 * Updates an invoice status. Also sends an email to the user order owner
	 */
	public function updateInvoiceStatus($invoice_id, $status) {
		global $db, $main, $email, $user;		
		$this->setId($invoice_id);
		$order_info = $this->getOrderByInvoiceId($invoice_id);
		$user_info 	= $user->getUserById($order_info['userid']);
		
		$array['USERNAME']	= $user->formatUsername($user_info['firstname'], $user_info['lastname']);
		
		$invoice_status = array_keys($main->getInvoiceStatusList());		
		if (in_array($status, $invoice_status)) {	
			switch($status) {
				case INVOICE_STATUS_PAID:
					$emailtemp 			= $db->emailTemplate('invoices_paid');					
					$array['INVOICE_ID']= $invoice_id;					
					$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);
				break;
				case INVOICE_STATUS_CANCELLED:
					$emailtemp 	= $db->emailTemplate('invoices_cancelled');
					$array['INVOICE_ID'] = $invoice_id;					
					$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);
				break;
				case INVOICE_STATUS_WAITING_PAYMENT:
					$emailtemp 	= $db->emailTemplate('invoices_pending');
					$array['INVOICE_ID'] = $invoice_id;					
					$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);
				break;
				case INVOICE_STATUS_DELETED:
				default:
				break;
			}
			$main->addlog("invoice::updateInvoiceStatus $invoice_id");
			$params['status'] = $status;
			$this->update($params);
		}		
	}
}