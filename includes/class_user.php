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
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE user = '{$username}'");
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
		$main->addLog("User deleted: $id");
		
		$this->updateUserStatus($id,USER_STATUS_DELETED);		
		
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
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE id = '{$db->strip($user_id)}'");
		$data = array();
		if($db->num_rows($query) > 0) {
			$data = $db->fetch_array($query,'ASSOC');			
		}
		return $data;		
	}
	
	public function validateUserName($username) {
		//Min 8 - Max 15
		if (preg_match('/^[a-z\d_]{8,20}$/i', $username)) {
			return true;
		}		
		return false;
	}
	
	/**
	 * Gets user information by username
	 * @param	int		user id
	 * @param	array	user information
	 */
	public function getUserByUserName($username) {
		global $db, $main;
		if (!empty($username)) {
			$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE user = '{$db->strip($username)}'");
			$data = array();
			if($db->num_rows($query) > 0) {
				$data = $db->fetch_array($query,'ASSOC');
				return $data;				
			}				
		}
		return false;
	}
	
	
	public function getUserByEmail($email) {
		global $db, $main;
		if (!empty($username)) {
			$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE email = '{$db->strip($email)}'");
			$data = array();
			if($db->num_rows($query) > 0) {
				$data = $db->fetch_array($query,'ASSOC');
				return $data;				
			}				
		}
		return false;
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
			$this->update($params, false);
		}		
	}
	
	public function getClientNavigation() {
		global $db;
		$sql = 'SELECT * FROM <PRE>clientnav'; 
		$result = $db->query($sql);
		$client_nav = array();
		while ($row = $db->fetch_array($result, 'ASSOC')) {
			$client_nav[$row['link']] = $row;
		}		
		return $client_nav;
	}
	
	public function getAdminNavigation() {
		global $db;
		$sql = 'SELECT * FROM <PRE>acpnav'; 
		$result = $db->query($sql);
		$client_nav = array();
		while ($row = $db->fetch_array($result, 'ASSOC')) {
			$client_nav[$row['link']] = $row;
		}		
		return $client_nav;
	}
	
	/**
	 * Only changes the system password not the Control Panel password
	 * 
	 * A more or less centralized function for changing a client's
	 * password. This updates both the cPanel/WHM and THT password.
	 * Will return true ONLY on success. Any other returned value should
	 * be treated as a failure. If the return value happens to be a
	 * string, it is an error message.
	 * @todo this function should be moved to the class_user.php file
	 * 
	 */
	public function changeClientPassword($clientid, $newpass) {
		global $db, $user;
		//Making sure the $clientid is a reference to a valid id.
		$user_info	=	$user->getUserById($clientid);
		
		if (is_array($user_info) && !empty($user_info)) {
			$user->edit($clientid, array('password'=>$newpass));
			/*
			mt_srand((int)microtime(true));
			$salt = md5(mt_rand());
			$password = md5(md5($newpass) . md5($salt));
			$db->query("UPDATE `<PRE>users` SET `password` = '{$password}' WHERE `id` = '{$db->strip($clientid)}'");
			$db->query("UPDATE `<PRE>users` SET `salt` = '{$salt}' WHERE `id` = '{$db->strip($clientid)}'");*/
			
		} else {
			return "That client does not exist.";
		}
		
		/*
		 * We're going to set the password in cPanel/WHM first. That way
		 * if the password is rejected for some reason, THT will not 
		 * desync.
		 */
		 
		/*$command = $server->changePwd($clientid, $newpass);
		if($command !== true) {
			return $command;
		}*/
		
		/*
		 * Let's change THT's copy of the password. Might as well make a
		 * new salt while we're at it.
		 */
		//Let's wrap it all up.
		return true;
	}
	
}