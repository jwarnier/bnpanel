<?php
/* For licensing terms, see /license.txt */

class page extends Controller {
	
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
		
		$action = isset($main->getvar['sub']) ? $main->getvar['sub'] : 'all';
		
		switch($action) {							
			case 'view':				
				if(isset($main->getvar['do'])) {					
					$return_array = $order->getOrder($main->getvar['do'], true);	
					$return_array['INVOICE_LIST'] = $order->showAllInvoicesByOrderId($main->getvar['do']);						
					$this->replaceVar("tpl/orders/view-client.tpl", $return_array);				
				}
			break;					
			case 'all':
			default :
				$return_array = $order->getAllOrdersToArray($main->getCurrentUserId());
				if(!empty($return_array) && isset($return_array['list']) && !empty($return_array['list'])) {							
					$this->replaceVar("tpl/orders/client-page.tpl", $return_array);
				} else {
					$style->showMessage('No Orders available');
				}		
		}
	}
}