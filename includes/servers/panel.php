<?php

abstract class Panel {
	
	public function GenUsername() {
		$t = rand(5,8);
		for ($digit = 0; $digit < $t; $digit++) {
			$r = rand(0,1);
			$c = ($r==0)? rand(65,90) : rand(97,122);
			$user .= chr($c);
		}
		return $user;
	}
	
	public function GenPassword() {
		for ($digit = 0; $digit < 5; $digit++) {
			$r = rand(0,1);
			$c = ($r==0)? rand(65,90) : rand(97,122);
			$passwd .= chr($c);
		}
		return $passwd;
	}
	
	private function serverDetails($server) {
		global $db;
		global $main;
		$sql = "SELECT * FROM `<PRE>servers` WHERE `id` = '{$db->strip($server)}'";
		$query = $db->query($sql);
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That server doesn't exist!";
			$array['Server ID'] = $server;
			$main->error($array);
			return;	
		} else {
			return $db->fetch_array($query);
		}
	}
	private function remote($action, $params){}
	public	function changePwd($username, $newpwd, $server_id) {}
	public	function signup($server, $reseller, $user, $email, $pass ) {}
	public	function suspend($username, $server_id, $reason) {}
	public	function unsuspend($username, $server_id) {}
	public	function terminate($username, $server_id) {}	
}