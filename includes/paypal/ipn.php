<?php
require '../compiler.php';

global $style, $db, $main, $invoice,  $addon, $billing, $order, $server, $package;

if(isset($main->getvar['do'])) {			
	require_once LINK."includes/paypal/paypal.class.php";
	$paypal = new paypal_class();
	//This is a very important step, this thing checks if the payment was sucessfull or not
	$invoice_id = intval($main->getvar['do']);
	 error_log('validate_ipn called');
	if ($paypal->validate_ipn()) {		
		error_log('ok');					
		$invoice->set_paid($invoice_id);		
		$user_id = $main->getCurrentUserId();
		$order_id = $invoice->getOrderByInvoiceId($invoice_id);
		
		$order_info = $order->getOrderInfo($order_id);
		$package_info = $package->getPackage($order_info['pid']);						
								
		//we check if the site was not already sent
		$serverphp = $server->loadServer($package_info['server']);
		$site_status = $serverphp->getSiteStatus($order_id);
		$result = true;
		
		if ($site_status == false) {
			//We send to the server finally
			$result = $order->sendOrderToControlPanel($order_id);
		}
		
		if ($result) {
			//Unsuspend order status + unsuspend the webhosting just in case 
			$order->updateOrderStatus($order_id, ORDER_STATUS_ACTIVE);
		} else {
			$order->updateOrderStatus($order_id, ORDER_STATUS_FAILED);
		}
		
		//Adding the transaction id (comes from a post of paypal)
		$transaction_id = $main->postvar['txn_id'];
		$params['transaction_id'] = $transaction_id;						
		$invoice->edit($invoice_id, $params);										
		$message = "Your Invoice #$invoice_id is paid.<br />";
		
		if ($result) {							
			$message .= "You Order #$order_id has been also proceed.<br />";
			$message .= "Check your email for access information. You should be able to see your site working in a few minutes.<br />";
		} else {
			$message .= 'There was a problem while dealing with you Order please contact the administrator.';
		}						
		$main->addLog($message);										
	} else {
		$main->addLog("Your invoice #$invoice_id hasn't been paid");						
	}
}
