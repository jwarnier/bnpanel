<?php
require '../compiler.php';

global $style, $db, $main, $invoice,  $addon, $billing, $order, $server, $package;
$main->addLog('paypal::ipn file called');

if(isset($main->getvar['do'])) {			
	require_once LINK."paypal/paypal.class.php";
	$paypal = new paypal_class();
	//This is a very important step, this thing checks if the payment was sucessfull or not
	$invoice_id = intval($main->getvar['do']);
		
	if ($paypal->validate_ipn()) {	
		
		$main->addLog('paypal::ipn validate ok');
		$main->addLog('paypal::ipn Invoice id #'.$invoice_id.' set to paid ');
		
		$invoice->set_paid($invoice_id);		
		$user_id = $main->getCurrentUserId();
		$order_id = $invoice->getOrderByInvoiceId($invoice_id);
		
		$order_info = $order->getOrderInfo($order_id);
		$package_info = $package->getPackage($order_info['pid']);						
								
		//we check if the site was not already sent
		$serverphp = $server->loadServer($package_info['server']);
		if ($serverphp->status) {
			$site_status = $serverphp->getSiteStatus($order_id);
			$result = true;		
			if ($site_status == false) {
				//Unsuspend order status + create web site 
				$order->updateOrderStatus($order_id, ORDER_STATUS_ACTIVE);
				$main->addLog('paypal::ipn updateOrderStatus to Active');
			} else {
				//The site already exist
				$order->updateOrderStatus($order_id, ORDER_STATUS_FAILED);
				$main->addLog('paypal::ipn updateOrderStatus to Fail');
			}
		} else {
			//Seems that the server is down!
			$order->updateOrderStatus($order_id, ORDER_STATUS_FAILED);
			$main->addLog('paypal::ipn updateOrderStatus to Fail seems that the Contro Panel is down');
		}
		
		//Adding the transaction id (comes from a post of paypal)
		$transaction_id = $main->postvar['txn_id'];
		$params['transaction_id'] = $transaction_id;						
		$invoice->edit($invoice_id, $params);			
		$main->addLog('paypal::ipn adding transaction id #'.$transaction_id);
		
	} else {
		$main->addLog('paypal::ipn validate error');						
	}
}