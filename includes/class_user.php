<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){
	die();
}

class user extends model {
		
	public $columns 	= array('id', 'user','email', 'password','salt', 'signup', 'ip', 'firstname', 'lastname', 'company', 'vatid', 'fiscalid', 'address', 'city', 'zip', 'state', 'country', 'phone', 'status');
	public $table_name 	= 'users';
	
	/** 
	 * Creates an user
	 * 
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($params, $clean_token = true) {
		global $db, $main, $email;		 
		//Password is the same, email and username is not empty		
		if ($params['password'] == $params['confirmp']) {
			if (!empty($params['user']) &&  !empty($params['email'])) {
				if ($this->userNameExists($params['user']) == false) {			
					$params['salt']			= md5(rand(0,9999999)); 
					$params['signup']		= time();
					$params['password'] 	= md5(md5($params['password']).md5($params['salt']));
					$params['ip'] 			= $main->removeXSS($_SERVER['REMOTE_ADDR']);
					$user_id = $this->save($params);	    
					
					$main->addLog("user:create #$user_id");
					
					if (!empty($user_id) && is_numeric($user_id)) {
						
						$user_info 		= $this->getUserById($user_id);		
						$emaildata 		= $db->emailTemplate('user_new');	
												
						$replace_array['USERNAME'] 				=  $this->formatUsername($user_info['firstname'], $user_info['lastname']);
						
						$replace_array['COMPANY_NAME'] 			=  $db->config('name');
						$replace_array['URL'] 					=  $db->config('url');
						$replace_array['USER_LOGIN'] 			=  $user_info['user'];
											
						$email->send($user_info['email'], $emaildata['subject'], $emaildata['content'], $replace_array);
					  	
		      			return $user_id;
					}
					return false;
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
		$this->setId($id);	
		
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
		$username = $db->strip($username);
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
		if (is_array($order_list)) {
			foreach($order_list as $order_item) {
				//Deleting orders
				$order->updateOrderStatus($order_item['id'], ORDER_STATUS_DELETED);
				$invoice_list = $invoice->getAllInvoicesByOrderId($order_item['id']);
				foreach($invoice_list as $invoice_item) {
					$invoice->updateInvoiceStatus($invoice_item['item'], INVOICE_STATUS_DELETED);				
				}			
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
		$user_id = intval($user_id);
		$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE id = $user_id");
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
		if (!empty($username)) {
			$query = $db->query("SELECT * FROM ".$this->getTableName()." WHERE user = '{$db->strip($username)}'");
			$data = array();
			if($db->num_rows($query) > 0) {				
				$data = $db->fetch_array($query,'ASSOC');				
				return $data;				
			}
			return false;				
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
			return false;		
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
			$query = $db->strip($query);
			$sql = "  SELECT * FROM ".$this->getTableName()." 
					  WHERE user 		LIKE '%$query%'  OR 
							email 		LIKE '%$query%'  OR 
							firstname 	LIKE '%$query%'  OR
							lastname	LIKE '%$query%'  OR
							company		LIKE '%$query%'";
			$result = $db->query($sql);
			
			if($db->num_rows($result) > 0) {
				while($data = $db->fetch_array($result,'ASSOC')) {
					$user_list[] = $data;
				}
			}
		}
		return $user_list;		
	}
	
	
	public function updateUserStatus($user_id, $status) {
		global $main;		
		$this->setId($user_id);
		$user_status_list = array_keys($main->getUserStatusList());		
		if (in_array($status, $user_status_list)) {		
			$params['status'] = $status;
			$main->addLog("updateUserStatus function called: $user_id");
			$this->update($params);
		}		
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
	
	//@todo this function needs to be improved
	public function formatUsername($firstname, $lastname, $username = '') {
		if (!empty($username)) {
			return $firstname." ".$lastname." ($username)";
		} else {
			return $firstname.' '.$lastname;
		}
	}
	
	public function validateUserName($username) {
		//Min 6 - Max 12 AlphaNumeric
		if (preg_match('/^[a-z0-9]{8,20}$/i', $username)) {
			return true;
		}		
		return false;
	}
	
	public function validatePassword($password) {
		//Min 6 - Max 12 AlphaNumeric
		if (preg_match('/^[a-z0-9]{6,12}$/i',$password)) {		
			return true;
		}		
		return false;		
	}		
}