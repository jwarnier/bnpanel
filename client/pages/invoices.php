<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){die();}

class page {
	
	public function __construct() {
		$this->navtitle = "Invoice menu";		
		$this->navlist[] = array("View all invoices", "page_white_go.png", "all");
	}
	
	public function description() {
		return "<strong>Invoices Area</strong><br />
		This is the area where you can view invoices.";	
	}	
	
	public function content() {
		global $style, $db, $main, $invoice, $server, $addon, $billing;

		switch($main->getvar['sub']) {
			case 'paid':				
				if(isset($_GET['invoiceID'])) {				
					require_once "../includes/paypal/paypal.class.php";
					$paypal = new paypal_class;
					//This is a very important step, this thing checks if the payment was sucessfull or not
					if($paypal->validate_ipn()) {
						$invoice->set_paid(intval($_GET['invoiceID']));
						$main->errors("Your invoice has been paid!");
						$user_id = $main->getCurrentUserId();
						$client = $db->fetch_array($db->query("SELECT * FROM `<PRE>orders` WHERE `userid` = '{$user_id}'"));
						if($client['status'] == USER_STATUS_SUSPENDED) {
							$server->unsuspend($client['id']);
						}
					} else {						
						$main->errors("Your invoice hasn't been paid!");
						echo '<ERRORS>';
					}
				}
			break;				
			case 'view':				
				if(isset($main->getvar['do'])) {					
					$return_array = $invoice->getInvoice($main->getvar['do'], true);									
					echo $style->replaceVar('tpl/invoices/viewinvoice.tpl', $return_array);					
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
							
				$array2['list'] = "";
				foreach($invoice_list as $invoice_item) {			
							
					$total_amount = 0;				
					$array['id'] = $invoice_item['id'];
					$array['due'] = strftime("%D", $invoice_item['due']);
					
					switch ($invoice_item['status']) {
						case INVOICE_STATUS_PAID:
							$array['paid']	=  '<span style="color:green">Already Paid</span>';
							$array['pay']	=  '<span style="color:green">Already Paid</span>';
							$array['due']	=  '<span style="color:green">'.$array['due'].'</span>' ;
							  
						break;
						case INVOICE_STATUS_CANCELLED:
							$array['paid'] 	= "<span style='color:red'>Canceled</span>";
							$array['pay'] 	= '<input type="button" name="pay" id="pay" value="Pay Now" onclick="doswirl(\''.$invoice_item['id'].'\')" />';
							$array['due']	=  '<span style="color:red">'.$array['due'].'</span>';		
						break;
						case INVOICE_STATUS_WAITING_PAYMENT:
							$array['paid'] = "<span style='color:red'>Pending</span>";
							$array['pay'] 	= '<input type="button" name="pay" id="pay" value="Pay Now" onclick="doswirl(\''.$invoice_item['id'].'\')" />';
							$array['due']	=  '<span style="color:red">'.$array['due'].'</span>';		
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
					$array2['list'] .= $style->replaceVar("tpl/invoices/invoice-list-item-client.tpl", $array);
				}
				echo $style->replaceVar('tpl/invoices/client-page.tpl', $array2);
				break;		
		}
	}
}