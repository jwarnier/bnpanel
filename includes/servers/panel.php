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
		$sql = "SELECT * FROM `<PRE>servers` WHERE `id` = '{$db->strip($server)}'";
		$query = $db->query($sql);
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That server doesn't exist!";
			$array['Server ID'] = $server;
			$main->error($array);
			return;	
		} else {
			return $db->fetch_array($query, 'ASSOC');
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
}
/*

function restartBind($action = 'restart') {
	global $conf;
    $daemon = '';
    if(is_file($conf['init_scripts'] . '/' . 'bind9')) {
        $daemon = 'bind9';
    } else {
        $daemon = 'named';
    }
    service_ctl($daemon, $action);
}

function service_ctl($daemon, $action) {
	global $conf;
	$dist_information = get_distname_standalone();
	$ostype = $dist_information['id'];	
	$method = '';
	switch ($ostype) {
		case 'solaris':
			if ($action == 'reload') {
				$action = 'refresh';
			}
			$method = "svcadm $daemon $action"; 
		break;
		case 'debian40':
		 	$method = "invoke-rc.d $daemon $action";
		break;
		//case 'ubuntu':
		// 	$method = "$action $daemon";
		//break;
		case 'opensuse110':
		 	$method = "$action $daemon";
		break;		
		case 'fedora9':
		 	$method = "$action $daemon";
		break;		
		case 'centos52':
		 	$method = "$action $daemon";
		break;	
		case 'centos53':
		 	$method = "$action $daemon";
		break;	
		case 'gentoo':
		 	$method = "$action $daemon";
		break;		
		default:
			$method = $conf['init_scripts'] . '/' . $daemon;
		break;
	}
	
	if (!empty($method)) {
		exec($method);
	}    
}


function get_distname_standalone() {

		$distname = '';
		$distver = '';
		$distid = '';
		$distbaseid = '';

		//** Debian or Ubuntu
		if(file_exists('/etc/debian_version')) {

			if(trim(file_get_contents('/etc/debian_version')) == '4.0') {
				$distname = 'Debian';
				$distver = '4.0';
				$distid = 'debian40';
				$distbaseid = 'debian';
			} elseif(strstr(trim(file_get_contents('/etc/debian_version')),'5.0')) {
				$distname = 'Debian';
				$distver = 'Lenny';
				$distid = 'debian40';
				$distbaseid = 'debian';
			} elseif(strstr(trim(file_get_contents('/etc/debian_version')),'6.0') || trim(file_get_contents('/etc/debian_version')) == 'squeeze/sid') {
				$distname = 'Debian';
				$distver = 'Squeeze/Sid';
				$distid = 'debian40';
				$distbaseid = 'debian';
			}  else {
				$distname = 'Debian';
				$distver = 'Unknown';
				$distid = 'debian40';
				$distbaseid = 'debian';
			}
		}

		//** OpenSuSE
		elseif(file_exists("/etc/SuSE-release")) {
			if(stristr(file_get_contents('/etc/SuSE-release'),'11.0')) {
				$distname = 'openSUSE';
				$distver = '11.0';
				$distid = 'opensuse110';
				$distbaseid = 'opensuse';
			} elseif(stristr(file_get_contents('/etc/SuSE-release'),'11.1')) {
				$distname = 'openSUSE';
				$distver = '11.1';
				$distid = 'opensuse110';
				$distbaseid = 'opensuse';
			} elseif(stristr(file_get_contents('/etc/SuSE-release'),'11.2')) {
				$distname = 'openSUSE';
				$distver = '11.1';
				$distid = 'opensuse110';
				$distbaseid = 'opensuse';
			}  else {
				$distname = 'openSUSE';
				$distver = 'Unknown';
				$distid = 'opensuse110';
				$distbaseid = 'opensuse';
			}
		}


		//** Redhat
		elseif(file_exists("/etc/redhat-release")) {

			$content = file_get_contents('/etc/redhat-release');

			if(stristr($content,'Fedora release 9 (Sulphur)')) {
				$distname = 'Fedora';
				$distver = '9';
				$distid = 'fedora9';
				$distbaseid = 'fedora';
			} elseif(stristr($content,'Fedora release 10 (Cambridge)')) {
				$distname = 'Fedora';
				$distver = '10';
				$distid = 'fedora9';
				$distbaseid = 'fedora';
			} elseif(stristr($content,'Fedora release 10')) {
				$distname = 'Fedora';
				$distver = '11';
				$distid = 'fedora9';
				$distbaseid = 'fedora';
			} elseif(stristr($content,'CentOS release 5.2 (Final)')) {
				$distname = 'CentOS';
				$distver = '5.2';
				$distid = 'centos52';
				$distbaseid = 'fedora';
			} elseif(stristr($content,'CentOS release 5.3 (Final)')) {
				$distname = 'CentOS';
				$distver = '5.3';
				$distid = 'centos53';
				$distbaseid = 'fedora';
			} else {
				$distname = 'Redhat';
				$distver = 'Unknown';
				$distid = 'fedora9';
				$distbaseid = 'fedora';
			}
		}

		//** Gentoo
		elseif(file_exists("/etc/gentoo-release")) {

			$content = file_get_contents('/etc/gentoo-release');

			preg_match_all('/([0-9]{1,2})/', $content, $version);
			$distname = 'Gentoo';
			$distver = $version[0][0].$version[0][1];
			$distid = 'gentoo';
			$distbaseid = 'gentoo';

		} else {
			die('unrecognized Linux distribution');
		}

		return array('name' => $distname, 'version' => $distver, 'id' => $distid, 'baseid' => $distbaseid);
	}
	*/