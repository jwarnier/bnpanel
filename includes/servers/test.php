<?php
/* For licensing terms, see /license.txt */
/**
	Test Plugin for BNPanel (THT)
	@author Julio Montoya <gugli100@gmail.com> BeezNest	2010
*/

class test extends Panel {
		
	public	$name = 'Test';
	public	$hash = false; # Password or Access Hash?	
			
	public function testConnection() {		
		return 'Logged into the Test Server successfully';						
	}	

	/**
		Changes the user password
		@param string	username
		@param string	new password
		@param int	server id		
		@return bool true if success
		@author Julio Montoya <gugli100@gmail.com> BeezNest	2010
	*/
	public function changePwd($username, $newpwd, $server_id) {
		echo 'Changing password on the Test Server: '.$username;
		return true;
	}
	
	/**
		Creates an user account + creating a site
		@param	int server id
		@param	int reseller id
		@param	string user name
		@param	string user email
		@param	string user password	
		
		@author Julio Montoya <gugli100@gmail.com> BeezNest	2010
	*/
	public function signup($order_id, $package_id, $domain_username, $domain_password, $user_id, $domain, $sub_domain_id) {		
		return true;	
	}
	
	/**
		Suspend a website/order
		@param string	order id 
		@param int		server id 
		@param string	reason 
		@author Julio Montoya <gugli100@gmail.com> BeezNest 2010
	*/
	public function suspend($order_id, $server_id, $reason = false) {
		return true;
	}
	
	/**
		Unsuspends a website/order
		@param string	order id
		@param int		server id 
		@author Julio Montoya <gugli100@gmail.com> BeezNest
	*/
	public function unsuspend($order_id, $server_id) {	
		return true;
	}	

	/**
		Deletes an user account
		@param string	user name
		@param int		server id
		@return bool true if success
	*/
	
	public function terminate($username, $server_id) {
		return true;
	}
	
	public function getAllPackageBackEnd() {
		$result = array(1=>'A', 2=>'B');		
		return $result;
	}
	
	public function parseBackendInfo($data) {
		if (!empty($data)) {
			$html .='<ul>';
			foreach ($data as $key=>$value) {
				$html .='<li>';
				$html .="<strong>$key</strong> :  $value";
				$html .='</li>';			
			}
			$html .='</ul>';
		}
		return $html;		
	}	
}
