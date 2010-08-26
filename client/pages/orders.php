<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){die();}

class page {
	
	public function __construct() {
		$this->navtitle = "Order menu";		
		$this->navlist[] = array("View all orders", "page_white_go.png", "all");
	}
	
	public function description() {
		return "<strong>Order Area</strong><br />
		This is the area where you can view all your orders.";	
	}	
	
	public function content() {
		global $style, $db, $main, $order;

		switch($main->getvar['sub']) {							
			case 'view':				
				if(isset($main->getvar['do'])) {					
					$return_array = $order->getOrder($main->getvar['do'], true);	
					$return_array['INVOICE_LIST'] = $order->showAllInvoicesByOrderId($main->getvar['do']);						
					echo $style->replaceVar("tpl/orders/view.tpl", $return_array);				
				}
			break;					
			case 'all':
			default :
				$return_array = $order->getAllOrdersToArray($main->getCurrentUserId());
				if(!empty($return_array) && isset($return_array['list']) && !empty($return_array['list'])) {
					echo '<ERRORS>';		
					echo $style->replaceVar("tpl/orders/client-page.tpl", $return_array);
				} else {
					$style->showMessage('No Orders available');
				}		
		}
	}
}