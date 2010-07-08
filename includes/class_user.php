<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){
	die();
}

class user extends model {
	
	public $columns = array('id', 'user','email', 'password','salt', 'signup', 'ip', 'firstname', 'lastname', 'address', 'city', 'zip', 'state', 'country', 'phone', 'status');
	public $table_name = "`<PRE>users`";
	
	/** 
	 * Creates an user
	 * 
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($params) {
		global $db, $main;		
		//Password is the same, email and username is not empty
		if ($params['password'] == $params['confirmp'] && !empty($params['user']) &&  !empty($params['email'])) {
			if ($this->userNameExists($params['user']) == false) {				
				$params['salt']			= md5(rand(0,9999999)); 
				$params['signup']		= time();
				$params['password'] 	= md5(md5($params['password']).md5($params['salt']));
				$params['ip'] 			= $_SERVER['REMOTE_ADDR'];
				$user_id = $this->save($params);	        	
	      		return $user_id;
			} else {
				//$array['Error'] = "That username already exist!";				
				$main->errors( "That username already exist!");
			}
		}
		return false;
	}
	
	public function edit($id, $params) {		
		$this->setPrimaryKey($id);
		$this->update($params);
	}
	

	
	/**
	 * Checks if the username is taken or not
	 * @param	string	username
	 * @return 	bool	true if success
	 */
	public function userNameExists($username) {
		global $db;
		$query = $db->query("SELECT * FROM ".$this->table_name." WHERE `user` = '{$username}'");
		if($db->num_rows($query) > 0) {
			return true;
		} else {	
			return false;	
		}
	}
	
	/**
	 * Deletes a user
	 */
	public function delete($id) { # Deletes a user
		/*global $db;
		$this->changeUserStatus($id, )
		$db->query("UPDATE `<PRE>user_packs` SET `status` = '$status' WHERE `id` = '{$db->strip($status)}'");*/
	/*	global $db;
		$query = $db->query("DELETE FROM `<PRE>user_packs` WHERE `id` = '{$id}'"); //Delete the invoice
		$query = $db->query("DELETE FROM `<PRE>user_pack_addons` WHERE `order_id` = '{$id}'"); //Delete the invoice*/
		return true;
	}
	
	public function changeUserStatus($user_id, $status) {
		global $db;		
		$db->query("UPDATE `<PRE>users` SET `status` = '$status' WHERE `id` = '{$db->strip($status)}'");
	}
	
	/**
	 * Gets user information by id
	 * @param	int		user id
	 * @param	array	user information
	 */
	public function getUserById($user_id) {
		global $db, $main;
		$query = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($user_id)}'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That user doesn't exist!";
			$array['User ID'] = $user_id;
			$main->error($array);
			return;
		} else {
			$data = $db->fetch_array($query);
			return $data;
		}
	}
	
	/**
	 * Search a user from a keyword (username, email, firtname or lastname) 
	 */
	public function searchUser($query) {
		global $db;
		$user_list = array();
		if (!empty($query)) {
			$sql = "SELECT * FROM `<PRE>users` 
						  WHERE user 		LIKE '%{$db->strip($query)}%' OR 
								email 		LIKE '%{$db->strip($query)}%'  OR 
								firstname 	LIKE '%{$db->strip($query)}%'  OR
								lastname 	LIKE '%{$db->strip($query)}%'";
			$result = $db->query($sql);
			
			if($db->num_rows($result) > 0) {
				while($data = $db->fetch_array($result)) {
					$user_list[] = $data;
				};		
			}
		}
		return $user_list;		
	}
}