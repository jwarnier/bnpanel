<?php
/* For licensing terms, see /license.txt */

abstract class Panel {
	
	/** Panel Name*/
	public	$name; 
	/** Hash or password*/ 
	public	$hash;
	/**Show debug or net*/
	public	$debug = false;
	/**The server id*/
	private $server_id;
	
	
	public function __construct($server_id = null) {
		if (empty($server_id)) {
			$server_id = 0;
		}
		$this->server_id = $server_id;		
	}
	
	public function GenUsername() {
		global $main;
		return $main->generateUsername();
	}
	
	public function GenPassword() {
		global $main;
		return $main->generatePassword();	
	}
	
	public function serverDetails($server) {
		global $db, $main;
		if (!empty($server)) {
			$server = $db->strip($server);
			$sql = "SELECT * FROM <PRE>servers WHERE id = $server";
			$query = $db->query($sql);
			if($db->num_rows($query) > 0) {
				return $db->fetch_array($query, 'ASSOC');
			}
		} else {
			$main->addlog('panel::serverDetails server id is not set');
			return false;
		}
	}
	public  function setServerId($server_id) {
		$this->server_id = $server_id;		
	}	
	public  function getServerId() {
		return $this->server_id;		
	}
		
	private	function remote($action, $params){}
	public  function testConnection() {}
	public	function changePwd($username, $newpwd, $server_id) {}
	public	function signup($server, $reseller, $user, $email, $pass ) {}
	public	function suspend($username, $server_id, $reason) {}
	public	function unsuspend($username, $server_id) {}
	public	function terminate($username, $server_id) {}
	public  function getServerStatus() {}
	public  function getSiteStatus() {}
	public  function getUserStatus() {}
		
}