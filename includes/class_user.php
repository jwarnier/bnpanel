<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){
	die();
}

class user extends model {
	
	public $columns 	= array('id', 'user','email', 'password','salt', 'signup', 'ip', 'firstname', 'lastname', 'address', 'city', 'zip', 'state', 'country', 'phone', 'status');
	public $table_name 	= 'users';
	
	/** 
	 * Creates an user
	 * 
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($params, $clean_token = true) {
		global $db, $main;		 
		//Password is the same, email and username is not empty
		
		if ($params['password'] == $params['confirmp']) {
			if (!empty($params['user']) &&  !empty($params['email'])) {
				if ($this->userNameExists($params['user']) == false) {			
					$params['salt']			= md5(rand(0,9999999)); 
					$params['signup']		= time();
					$params['password'] 	= md5(md5($params['password']).md5($params['salt']));
					$params['ip'] 			= $_SERVER['REMOTE_ADDR'];
					$user_id = $this->save($params, $clean_token);	    
					$main->addLog("User created: $user_id");    	
		      		return $user_id;
				} else {
					//$array['Error'] = "That username already exist!";				
					$main->errors( "That username already exist!");
				}
			} else {
				$main->errors('Please field the username and email');
			}
		} else {
			$main->errors('Passwords do not match');
		}
		return false;
	}
	
	
	public function edit($id, $params) {
		global $order, $main;	
		$this->setPrimaryKey($id);	
		
		if (isset($params['password']) && !empty($params['password']) )  {			
			$params['salt']			= md5(rand(0,9999999));
			$params['password'] 	= md5(md5($params['password']).md5($params['salt']));
		}	
		
		if(isset($params['status'])) {
			
			$order_list = $order->getAllOrdersByUser($id);		
			
			switch($params['status']) {
				case USER_STATUS_ACTIVE:
					//If a user is active we do nothing  since we dont know what to update
					//$server->unsuspend($order_id);
				break;
				case USER_STATUS_SUSPENDED:					
				case USER_STATUS_WAITING_ADMIN_VALIDATION:
				case USER_STATUS_WAITING_USER_VALIDATION:
				case USER_STATUS_DELETED:
				global $server;
				
				//If we suspend a user all orders will be set to suspend				
				if(is_array($order_list) && count($order_list) > 0) {		
					foreach($order_list as $order_item) {
						$server->suspend($order_item['id']);
					}
				}
				break;
				default:
				break;
			}
		}
		$main->addLog("User updated: $id");
		$this->update($params);
	}
	
	/**
	 * Checks if the username is taken or not
	 * @param	string	username
	 * @return 	bool	true if success
	 */
	public function userNameExists($username) {
		global $db;
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE `user` = '{$username}'");
		if($db->num_rows($query) > 0) {
			return true;
		} else {	
			return false;	
		}
	}
	
	/**
	 * Deletes a user + order deleted + invoice deleted
	 */
	public function delete($id) {
		global $order, $invoice, $main; 
		//User deleted
		$this->updateUserStatus($id,USER_STATUS_DELETED);
		$main->addLog("User deleted: $id");
		
		//@todo check this funcionality
		$order_list = $order->getAllOrdersByUser($id);
		foreach($order_list as $order_item) {
			//Deleting orders
			$order->updateOrderStatus($order_item['id'], ORDER_STATUS_DELETED);
			$invoice_list = $invoice->getAllInvoicesByOrderId($order_item['id']);
			foreach($invoice_list as $invoice_item) {
				$invoice->updateInvoiceStatus($invoice_item['item'], INVOICE_STATUS_DELETED);				
			}			
		}		
		return true;
	}
		
	/**
	 * Gets user information by id
	 * @param	int		user id
	 * @param	array	user information
	 */
	public function getUserById($user_id) {
		global $db, $main;
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE `id` = '{$db->strip($user_id)}'");
		$data = array();
		if($db->num_rows($query) > 0) {
			$data = $db->fetch_array($query,'ASSOC');			
		}
		return $data;		
	}
	
	/**
	 * Gets user information by username
	 * @param	int		user id
	 * @param	array	user information
	 */
	public function getUserByUserName($username) {
		global $db, $main;
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE `user` = '{$db->strip($username)}'");
		$data = array();
		if($db->num_rows($query) > 0) {
			$data = $db->fetch_array($query,'ASSOC');			
		}
		return $data;		
	}
	
	
	/**
	 * Search a user from a keyword (username, email, firtname or lastname) 
	 */
	public function searchUser($query) {
		global $db;
		$user_list = array();
		if (!empty($query)) {
			$sql = "SELECT * FROM ".$this->getTableName()." 
						  WHERE user 		LIKE '%{$db->strip($query)}%' OR 
								email 		LIKE '%{$db->strip($query)}%'  OR 
								firstname 	LIKE '%{$db->strip($query)}%'  OR
								lastname 	LIKE '%{$db->strip($query)}%'";
			$result = $db->query($sql);
			
			if($db->num_rows($result) > 0) {
				while($data = $db->fetch_array($result,'ASSOC')) {
					$user_list[] = $data;
				};		
			}
		}
		return $user_list;		
	}
	
	
	public function updateUserStatus($user_id, $status) {
		global $main;		
		$this->setPrimaryKey($user_id);
		$user_status_list = array_keys($main->getUserStatusList());		
		if (in_array($status, $user_status_list)) {		
			$params['status'] = $status;
			$main->addLog("updateUserStatus function called: $user_id");
			$this->update($params);
		}		
	}
	
}