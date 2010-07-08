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
						$client = $db->fetch_array($db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$_SESSION['cuser']}'"));
						if($client['status'] == 2) {
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
					echo $style->replaceVar("tpl/invoices/viewinvoice.tpl", $return_array);					
				}
			break;					
			case 'all':
			default :	
					
				//Package info
				$sql = "SELECT id, name  FROM `<PRE>packages`";
				$packages 		= $db->query($sql);
				while ($data = $db->fetch_array($packages)) {
					$package_name_list[$data['id']] = $data['name'];
				}
				
				$billing_cycle_name_list = $billing->getAllBillingCycles();
				
				//Addons
				$addons_list = $addon->getAllAddons();				
				
				// List of invoices. :)
				$query = $db->query("SELECT * FROM `<PRE>invoices` WHERE `uid` = '{$_SESSION['cuser']}' ORDER BY `id` ASC");
				$userdata = mysql_fetch_row($db->query("SELECT `user`,`firstname`,`lastname` FROM `<PRE>users` WHERE `id` = {$_SESSION['cuser']}"));
				$domain = mysql_fetch_row($db->query("SELECT domain, pid, billing_cycle_id  FROM `<PRE>user_packs` WHERE `userid` = {$_SESSION['cuser']}"));
				$extra = array(					
					"domain"	=> $domain[0],
					"pid"	=> $domain[1],
					"billing_cycle_id"	=> $domain[2]			
				);
				$array2['list'] = "";
				while($array = $db->fetch_array($query)) {
					$total_amount = 0;				
					
					$array['due'] = strftime("%D", $array['due']);			
							/*
					$array['paid'] = ($array["is_paid"] == 1 ? "<span style='color:green;font-size:20px;'>Paid</span>" :
					"<span style='color:red;font-size:20px;'>Unpaid</span>");
					$array['pay'] = ($array["is_paid"] == 0 ? 
					'<input type="button" name="pay" id="pay" value="Pay Now" onclick="doswirl(\''.$array['id'].'\')" />' : 'Already paid');
					*/
					
					switch ($array['status']) {
						case INVOICE_STATUS_PAID:
							$array['paid']	= "<span style='color:green'>Already Paid</span>";
							$array['pay']	=  "<span style='color:green'>Already Paid</span>";
							$array['due']	=  '<span style="color:green">'.$array['due'].'</span>' ;
							  
						break;
						case INVOICE_STATUS_CANCELLED:
							$array['paid'] 	= "<span style='color:red'>Canceled</span>";
							$array['pay'] 	= '<input type="button" name="pay" id="pay" value="Pay Now" onclick="doswirl(\''.$array['id'].'\')" />';
							$array['due']	=  '<span style="color:red">'.$array['due'].'</span>';		
						break;
						case INVOICE_STATUS_WAITING_PAYMENT:
							$array['paid'] = "<span style='color:red'>Pending</span>";
							$array['pay'] 	= '<input type="button" name="pay" id="pay" value="Pay Now" onclick="doswirl(\''.$array['id'].'\')" />';
							$array['due']	=  '<span style="color:red">'.$array['due'].'</span>';		
						break;
						case INVOICE_STATUS_DELETED:
							///	$array['paid'] = "<span style='color:green'>Already Paid</span>";
						break;				
					}
					
					
					$package_id 	  = $extra['pid'];
					$billing_cycle_id = $extra['billing_cycle_id'];
					
					$addon_fee_string = '';
					if (!empty($array['addon_fee'])) {
						
						$array['addon_fee'] = unserialize($array['addon_fee']);
						if (is_array($array['addon_fee']) && count($array['addon_fee']) > 0 ) {
							foreach($array['addon_fee'] as $addon) {					
								$addon_fee_string.= $addons_list[$addon['addon_id']].' - '.$addon['amount'].'<br />';
								$total_amount = $total_amount + $addon['amount'];					
							}
						}					
					}
					
					$array['addon_fee'] = $addon_fee_string;
					$total_amount 		= $total_amount + $array['amount'];
					$array['amount'] 	= $total_amount." ".$db->config('currency');
					
					$array['edit']  	= '';			
					$array['delete']  	= '';
					$array2['list'] .= $style->replaceVar("tpl/invoices/invoice-list-item-client.tpl", array_merge($array, $extra));
				}
				$array2['num'] = mysql_num_rows($query);
				
				echo $style->replaceVar("tpl/invoices/client-page.tpl", $array2);
				break;		
		}
	}
}