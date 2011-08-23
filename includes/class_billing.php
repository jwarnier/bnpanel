<?php
/* For licensing terms, see /license.txt */

class billing extends model {
	
	public $columns 	= array('id', 'number_months','name', 'status');
	public $table_name = 'billing_cycles';
	public $_modelName = 'billing';
	
	// products = addons or packages
	public $has_many	= array('products'=> array('table_name'	=> 'billing_products', 
												   'columns'	=> array('billing_id', 'product_id', 'amount','type'))
						   		);
	
	
	public function create($params) { 
		$billing_id = $this->save($params);
		return $billing_id;
	}
	
	public function edit($id, $params) {		
		$this->setId($id);		
		$this->update($params);
	}
	
	public function delete() {
		parent::delete();
	}	
	
	/**
	 * Generates a select menu with the available addons
	 * @param	array	selected addons
	 * @return 	string	html of the select
	 * @author	Julio Montoya <gugli100@gmail.com> BeezNest 2010
	 */
	public function generateBillingInputs($selected_values = array()) {
		global $db,$main;
		$sql = "SELECT * FROM ".$this->getTableName()." WHERE status = ".BILLING_CYCLE_STATUS_ACTIVE;
		$query = $db->query($sql);		
		$billing_cycle_result = '';
		while($data = $db->fetch_array($query)) {
			$amount = '';
			if (isset($selected_values[$data['id']])) {
				$amount = $selected_values[$data['id']];
			}		
			$billing_cycle_result.= $main->createInput($data['name'].' ('.$db->config('currency').') <br />', 'billing_cycle_'.$data['id'], $amount);													
		}
		return $billing_cycle_result;
	}
	
	public function getAllBillingCycles($status = BILLING_CYCLE_STATUS_ACTIVE, $add_none_value = false) {
		global $db;
		if (!in_array($status, array(BILLING_CYCLE_STATUS_ACTIVE, BILLING_CYCLE_STATUS_INACTIVE))) {
			$status = BILLING_CYCLE_STATUS_ACTIVE;
		}		
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE status = ".$status);
		$billing_list = array();				
		if ($db->num_rows($query) > 0) {											
			$billing_cycle_result = '';
			while($data = $db->fetch_array($query)) {		
				$billing_list[$data['id']] = $data;
			}								
		}
		if ($add_none_value) {
			$billing_list['-1'] = array('id' => '-1', 'name'=>'None');
		}
		return $billing_list; 		
	}
	
	public function getAllBillings($status = BILLING_CYCLE_STATUS_ACTIVE) {
		global $db;
		if (empty($status)) {
			$status = BILLING_CYCLE_STATUS_ACTIVE;
		} else {
			$status = intval($status);
		}
		$sql 	= "SELECT * FROM ".$this->getTableName()." WHERE status = ".$status;
		$query 	= $db->query($sql);	
		$result = $db->store_result($query, 'ASSOC');
		return $result;		
	}
	
	public function getBilling($id) {
		global $db;
		$id = intval($id);
		$sql = "SELECT * FROM ".$this->getTableName()." WHERE id = ".$id;
		$result = $db->query($sql);
		$data = array();		
		if ($db->num_rows($result) > 0) {
			$data = $db->fetch_array($result, 'ASSOC');	
		}		
		return $data;
	}	
}