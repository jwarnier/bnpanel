<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){
	die();
}

class user {
	
	/** 
	 * Creates an user
	 * 
	 * @param 	int		User id
	 * @param	float	amount
	 * @param	date	expiration date
	 */
	public function create($uid, $amount, $due, $notes, $addon_fee) {
		/*global $db;
		global $email;
		$client 		= $db->client($uid);
		$emailtemp 		= $db->emailTemplate('newinvoice');
		$array['USER'] 	= $client['user'];
		$array['DUE'] 	= strftime("%D", $due);
		$email->send($client['email'], $emailtemp['subject'], $emailtemp['content'], $array);
		return $db->query("INSERT INTO `<PRE>invoices` (uid, amount, due, notes, addon_fee ) VALUES('{$uid}', '{$amount}', '{$due}', '{$notes}','{$addon_fee}' )");*/
	}
	
	/**
	 * Deletes a user
	 */
	public function delete($id) { # Deletes invoice upon invoice id
	/*	global $db;
		$query = $db->query("DELETE FROM `<PRE>user_packs` WHERE `id` = '{$id}'"); //Delete the invoice
		$query = $db->query("DELETE FROM `<PRE>user_pack_addons` WHERE `order_id` = '{$id}'"); //Delete the invoice*/
		return true;
	}
	
	public function changeUserStatus($user_id, $status) {
		global $main;
		$db->query("UPDATE `<PRE>user_packs` SET `status` = '$status' WHERE `id` = '{$db->strip($status)}'");
		$db->query("UPDATE `<PRE>users` SET `status` = '$status' WHERE `id` = '{$db->strip($status)}'");
	}
	
	public function getUserById($user_id) {
		global $db, $main;
		$query = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($id)}'");
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
	
	public function searchUser($query) {
		global $db;
		$user_list = array();
		if (!empty($query)) {
			echo $sql = "SELECT * FROM `<PRE>users` 
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