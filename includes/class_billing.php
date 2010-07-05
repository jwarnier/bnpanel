<?php


//Check if called by script
if(THT != 1){
	die();
}

class billing {
	
	/**
	 * Generates a select menu with the available addons
	 * @param	array	selected addons
	 * @return 	string	html of the select
	 * @author	Julio Montoya <gugli100@gmail.com> BeezNest 2010
	 */
	public function generateBillingSelect($selected_values = array()) {
		global $db,$main;
		$sql = "SELECT * FROM `<PRE>billing_cycles` WHERE status = ".BILLING_CYCLE_STATUS_ACTIVE;
		$query = $db->query($sql);		
		$billing_cycle_result = '';
		while($data = $db->fetch_array($query)) {
			$amount = '';
			if (isset($selected_values[$data['id']])) {
				$amount = $selected_values[$data['id']];
			}		
			$billing_cycle_result.= $main->createInput($data['name'].' ('.$db->config('currency').')', 'billing_cycle_'.$data['id'], $amount);													
		}
		return $billing_cycle_result;
	}
	
	public function getBillingCycles($status = BILLING_CYCLE_STATUS_ACTIVE) {
		global $db;
		if (!in_array($status, array(BILLING_CYCLE_STATUS_ACTIVE, BILLING_CYCLE_STATUS_INACTIVE))) {
			$status = BILLING_CYCLE_STATUS_ACTIVE;
		}		
		$query = $db->query("SELECT * FROM `<PRE>billing_cycles` WHERE status = ".$status);
		$billing_list = array();				
		if($db->num_rows($query) > 0) {											
			$billing_cycle_result = '';
			while($data = $db->fetch_array($query)) {		
				$billing_list[$data['id']] = $data;
			}								
		}
		return $billing_list; 		
	}
	
	public function getBilling($id) {
		global $db;
		$id = intval($id);
		$sql = "SELECT * FROM `<PRE>billing_cycles` WHERE id = ".$id;
		$result = $db->query($sql);
		$data = array();		
		if ($db->num_rows($result) > 0) {
			$data = $db->fetch_array($result);	
		}		
		return $data;
	}
	
	
}

?>