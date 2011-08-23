<?php
/* For licensing terms, see /license.txt */

class page {
	
	public function __construct() {
		$this->navtitle = "Invoice menu";		
		$this->navlist[] = array("View all invoices", "page_white_go.png", "all");
	}
	
	public function description() {
		return "<strong>Invoices Area</strong><br />This is the area where you can view invoices.";	
	}	
	
	public function content() {
		global $style, $db, $main, $invoice,  $addon, $billing, $order, $server, $package;

		$action = isset($main->getvar['sub']) ? $main->getvar['sub'] : 'all';
		
		switch($action) {		
			case 'paid':				
				//this is moved to the paypal/ipn.php file
			break;						
			case 'view':				
				if(isset($main->getvar['do'])) {					
					switch ($main->getvar['p']) {
						case 'cancel':
							$style->showMessage('Your Invoice #'.$main->getvar['do'].' is not paid. <br /> You can pay your Invoice clicking in the "Pay Now" button');
						break;						
						case 'success':
							$message = 'Your Invoice #'.$main->getvar['do'].' has been paid<br />';
							$message .= "Check your email for access information. You should be able to see your site working in a few minutes.<br />";
							$style->showMessage($message,'success');
						break;
					}		
					$return_array = $invoice->getInvoice($main->getvar['do'], true);
					
					if ($return_array['STATUS'] == 'Pending') {					
						$return_array['pay'] = '<input type="button" name="pay" id="pay" value="Pay Now" onclick="doswirl(\''.$main->getvar['do'].'\');" />';
					} else {
						$return_array['pay'] = '';
					}					
					echo $style->replaceVar('tpl/invoices/view-client.tpl', $return_array);					
				}
			break;					
			case 'all':
			default :
				$user_id = $main->getCurrentUserId();				
				$billing_cycle_name_list = $billing->getAllBillingCycles();
				
				//Addons
				$addons_list = $addon->getAllAddons();				
				
				// List of invoices. :)
				$invoice_list = $invoice->getAllInvoices($user_id);
				
				//Subdomains list
				$subdomain_list = $main->getSubDomains();
							
				$array2['list'] = "";
				if (is_array($invoice_list) && count($invoice_list) > 0) {
					foreach($invoice_list as $invoice_item) {
								
						$total_amount = 0;				
						$array['id'] 	= $invoice_item['id'];
						$order_id 		= $invoice->getOrderByInvoiceId($invoice_item['id']);
						$order_info 	= $order->getOrderInfo($order_id);
						
						
						//Getting the domain info
						$array['domain'] = null;
						if (!empty($order_info)) {			
							$array['domain'] 	= $order_info['real_domain'];
						}
											
						$array['due'] = date('Y-m-d', $invoice_item['due']);
						
						switch ($invoice_item['status']) {
							case INVOICE_STATUS_PAID:
								$array['paid']	=  '<span style="color:green">Paid</span>';
								$array['pay']	=  '<span style="color:green">Already Paid</span>';
								$array['due']	=  '<span style="color:green">'.$array['due'].'</span>' ;						  
							break;
							case INVOICE_STATUS_CANCELLED:
								$array['paid'] 	= '<span style="color:red">Canceled</span>';
								$array['pay'] 	= '<input type="button" name="pay" id="pay" value="Pay Now" onclick="doswirl(\''.$invoice_item['id'].'\')" />';
								$array['due']	= '<span style="color:red">'.$array['due'].'</span>';		
							break;
							case INVOICE_STATUS_WAITING_PAYMENT:
								$array['paid'] 	= '<span style="color:red">Pending</span>';
								$array['pay'] 	= '<input type="button" name="pay" id="pay" value="Pay Now" onclick="doswirl(\''.$invoice_item['id'].'\')" />';
								$array['due']	= '<span style="color:red">'.$array['due'].'</span>';		
							break;
							case INVOICE_STATUS_DELETED:
								///	$array['paid'] = "<span style='color:green'>Already Paid</span>";
							break;		
							default:
								//This is weird an invoice with no status?
								$array['paid']= '-';
								$array['pay']=  '-';
								//$array['due']=  '<span>'.$array['due'].'</span>';
						}
						
						$package_id 	  = $invoice_item['pid'];
						$billing_cycle_id = $invoice_item['billing_cycle_id'];
						
						$addon_fee_string = '';
						if (!empty($invoice_item['addon_fee'])) {
							
							$invoice_item['addon_fee'] = unserialize($invoice_item['addon_fee']);
							if (is_array($invoice_item['addon_fee']) && count($invoice_item['addon_fee']) > 0 ) {
								foreach($invoice_item['addon_fee'] as $addon) {					
									//$addon_fee_string.= $addons_list[$addon['addon_id']].' - '.$addon['amount'].'<br />';
									$total_amount = $total_amount + $addon['amount'];					
								}
							}					
						}
						$array['addon_fee'] = null;
						
						//$array['addon_fee'] = $addon_fee_string;
						$total_amount 		= $total_amount + $invoice_item['amount'];
						$array['amount'] 	= $total_amount." ".$db->config('currency');
						$array2['list'] 	.= $style->replaceVar("tpl/invoices/invoice-list-item-client.tpl", $array);
					}
					echo $style->replaceVar('tpl/invoices/client-page.tpl', $array2);
				} else {
					$style->showMessage('No Invoices available');
				}
				break;		
		}
	}
}