<?php
/**
	ISPConfig Plugin for TheHostingTool (THT)
	@author Julio Montoya <gugli100@gmail.com> Beeznest	2010
*/

class ispconfig extends Panel {
		
	public	$name = "ISPConfig"; # THT Values
	public	$hash = false; # Password or Access Hash?
	private $server;
	private	$session_id;
	public	$debug = true;

//	private	$soap_client;

	/**
		Stablished a SOAP connection
	*/
	public function load() {	
		$data = $this->serverDetails($this->server);
		$port = 8080;

		$data['host']	= $data['host'].':'.$port;
		//* The URI to the remoting interface. Please replace with the URI to your real server
		$soap_location	= $data['host'].'/remote/index.php';
		$soap_uri 		= $data['host'].'/remote/';

		// Create the SOAP Client
		$client = new SoapClient(null, array('location' => $soap_location,'uri'=> $soap_uri));
		try {
			//* Login to the remote server
			if($session_id = $client->login($data['user'],$data['accesshash'])) {
				//var_dump($client->__getFunctions()); does not work
				if ($this->debug) {echo 'Logged into remote server sucessfully. The SessionID is '.$session_id.'<br />';}
				$this->session_id = $session_id;
				return $client;
			}
		} catch (SoapFault $e) {
			return false;
			//die('SOAP Error: '.$e->getMessage());
		}
		return false;
	}
	
	


	/**
		This functions gets the data of a package (hosting package)
		@param 	int the package id 
		@return array info with the package data
		@author Julio Montoya <gugli100@gmail.com> Beeznest	2010	
	*/
	private function getPackage($package_id) {
		global $db, $main;
		$package_id = intval($package_id);
		$sql = "SELECT * FROM `<PRE>packages` WHERE `id` = '$package_id'";
		$query = $db->query($sql);
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That server doesn't exist!";
			$main->error($array);
			return;	
		} else {
			return $db->fetch_array($query, MYSQL_ASSOC);
		}
	}

	/*
		Manage the ISPConfig SOAP functions
		@param  string the action will be the same name as the specify in the ISPConfig API
		@param	array  parameters that the SOAP will used 
		@return mixed  result of the SOAP call
	*/
	private function remote($action, $params) {
		$soap_client = $this->load();
		
		$result = array();
		if ($soap_client) {
			try {
				if ($this->debug) { echo '<br /><<-'.$action.'<br />'; echo 'Params : '; var_dump($params).'<br /><br />'; }
	
				switch($action) {
					case 'client_add':
					 	$reseller_id = 0;		
						$soap_result	= $soap_client->client_add($this->session_id, $reseller_id, $params);					
					break;
					case 'client_get':
						$soap_result 	= $soap_client->client_get($this->session_id, $params['client_id']);
					break;
					case 'client_get_by_username':
						$soap_result 	= $soap_client->client_get_by_username($this->session_id, $params['username']);
					break;
					case 'client_get_sites_by_user':
						$soap_result 	= $soap_client->client_get_sites_by_user($this->session_id, $params['sys_userid'], $params['groups']);
					break;
					case 'client_delete':
						$soap_result 	= $soap_client->client_delete($this->session_id, $params['client_id']);
					break;
					case 'client_update':
						$soap_result 	= $soap_client->client_update($this->session_id, $params['client_id'], $params['reseller_id'], $params);
					break;
					case 'client_change_password':
						$soap_result 	= $soap_client->client_change_password($this->session_id, $params['client_id'], $params['password']);
					break;
					case 'sites_cron_add':
						//$soap_result = $soap_client->sites_cron_add($this->session_id, $reseller_id, $site);	
					break;
					case 'sites_web_domain_update':
						$client_id 		= $params['client_id']; // client id
						$primary_id		= $params['primary_id']; //site id
						$params['client_id'] = $params['primary_id'] = null;
						$soap_result 	= $soap_client->sites_web_domain_update($this->session_id, $client_id, $primary_id, $params);
					break;
					case 'sites_web_domain_active':
						$primary_id		= $params['primary_id']; //site id
						$soap_result 	= $soap_client->sites_web_domain_active($this->session_id, $primary_id);
					break;
					case 'sites_web_domain_inactive':	
						$primary_id		= $params['primary_id']; //site id
						$soap_result 	= $soap_client->sites_web_domain_inactive($this->session_id, $primary_id);
					break;
					case 'sites_web_domain_add':
						$client_id = $params['client_id'];
						$params['client_id'] = null;
						$soap_result 	= $soap_client->sites_web_domain_add($this->session_id, $client_id  , $params);
					break;	
					case 'sites_web_domain_get':
						$soap_result 	= $soap_client->sites_web_domain_get($this->session_id, $params['primary_id']);
					break;
					
					case 'server_get':
						$soap_result 	= $soap_client->server_get($this->session_id, $params['server_id'], $params['section']);
					break;
	
					default:
					break;
				}
				if ($this->debug) { echo 'Result: '; var_dump($soap_result); echo '------------------>><br />';}
				return $soap_result;
	
			} catch (SoapFault $e) {
				$result['error']=1;
				$result['text'] = $e->getMessage();
				return $result;
			}
		} else {
			return false;
		}
	}

	/*
		Changes the user password
		@param string	username
		@param string	new password
		@param int	server id		
		@return bool true if success
		@author Julio Montoya <gugli100@gmail.com> Beeznest	2010
	*/
	public function changePwd($username, $newpwd, $server_id) {
		$this->server = $server_id;
		$params['username'] = $username;
		$user_info = $this->remote('client_get_by_username',$params);	
		if (!empty($user_info['client_id'])) {	
			$client_update_params['client_id'] = $user_info['client_id'];	
			$client_update_params['password'] = $newpwd;
			$result = $this->remote('client_change_password',$client_update_params);
			if ($result) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
		Creates an user account + creating a site
		@param	int server id
		@param	int reseller id
		@param	string user name
		@param	string user email
		@param	string user password	
		
		@author Julio Montoya <gugli100@gmail.com> Beeznest	2010
	*/
	public function signup($server, $reseller, $user = '', $email = '', $pass = '') {
		global $main;
		global $db;		

		if ($user  == '')  { $user =	$main->getvar['username']; }
		if ($email == '')  { $email= $main->getvar['email']; }
		if ($pass  == '')  { $pass = $main->getvar['password']; }

		$this->server = $server;
		$data = $this->serverDetails($this->server);	
		if ($this->debug) {echo '<pre>';}
		$ip = gethostbyname($data['host']);

			/*ISPConfig client variables
					client_id 	sys_userid 	sys_groupid 	sys_perm_user 	sys_perm_group 	sys_perm_other 	
company_name 	contact_name 	street 	zip 	city 	state 	country 	telephone 	mobile 	fax 	email 	internet 	icq 	notes 	
default_mailserver 	limit_maildomain 	limit_mailbox 	limit_mailalias 	limit_mailaliasdomain 	limit_mailforward 	limit_mailcatchall 	limit_mailrouting 	limit_mailfilter 	limit_fetchmail 	limit_mailquota 	limit_spamfilter_wblist 	limit_spamfilter_user 	limit_spamfilter_policy 	default_webserver 	limit_web_ip 	limit_web_domain 	limit_web_quota 	web_php_options 	limit_web_subdomain 	limit_web_aliasdomain 	limit_ftp_user 	limit_shell_user 	ssh_chroot 	default_dnsserver 	limit_dns_zone 	limit_dns_record 	default_dbserver 	limit_database 	limit_cron 	limit_cron_type 	limit_cron_frequency 	limit_traffic_quota 	limit_client 	parent_client_id 	
username 	password 	language 	usertheme 	template_master 	template_additional 	created_at
					*/	
		//User info
		$params['username'] 		= $user ;
		$params['email'] 			= $email;
		$params['contact_name'] 	= $main->getvar['firstname'].' '.$main->getvar['lastname'];		
		$params['password'] 		= $pass;	

		$params['street'] 			= $main->getvar['address'];
		$params['city'] 			= $main->getvar['city'];
		$params['state'] 			= $main->getvar['state'];
		$params['zip'] 				= $main->getvar['zip'];
		$params['country'] 			= $main->getvar['country'];
		$params['telephone'] 		= $main->getvar['phone'];

		$package_info = $this->getPackage($main->getvar['package']);
		$package_back_end_id = $package_info['backend'];

		//Plan info
		$params['template_master'] 	= $package_back_end_id;
		//$params['template_additional'] 	= '';

		//Getting the server id it depends in the package that the user is selecting
		$server_id = 1; //default server = 1
		if (!empty($package_info['server_id'])) {
			$server_id = $package_info['server_id'];
		}

		//harcoding values
		$params['usertheme'] 		= 'default';	
		$params['created_at']		= time();
		$params['language'] 		= 'en';

		 //The main domain
		$is_domain = true;
		if ($main->getvar['domain'] == 'dom' ) {
			$site_params['domain'] 	= $main->getvar['cdom'];
		} elseif ($main->getvar['domain'] == 'sub') {
			//Subdomain
			$site_params['domain'] 	= $main->getvar['csub'].'.'.$main->getvar['csub2'];
			$is_domain = false;
		}

		/*
		hd_quota_error_empty ok
		traffic_quota_error_empty ok 
		documentroot_error_empty ??
		sysuser_error_empty ok
		sysgroup_error_empty ok 
		allow_override_error_empty ok 
		php_open_basedir_error_empty ??
		*/

		//Adding the client
		$new_client_id = $this->remote('client_add',$params);
		$site_params['client_id'] = $new_client_id;

		//If no error 
		if($new_client_id['error']) {
//			echo "<strong>".$command['text']."</strong><br />". $command['details'];
			return false;
		} else {

			//If client is added we have the new client id	

			//Preparing variables to send to server_get
			$server_params['server_id'] 	= $server_id;
			$server_params['section'] 		= 'web';
			
			//Getting server info
			$server_info = $this->remote('server_get',$server_params);

			//Getting extra info of user
			$client_info = $this->remote('client_get',array('client_id'=>$new_client_id));

			$website_id = 1;

			//Setting parameters for the sites_web_domain_add function
			$site_params['type'] 			= 'vhost';// harcoded in ISPConfig vhost
			$site_params['vhost_type'] 		= 'name';// harcoded in ISPConfig vhost 
	
			$site_params['sys_userid'] 		= 1;//1; force to the admin
			$site_params['sys_groupid'] 	= 1; //ass added by the admin
			$site_params['system_user'] 	= 'web'.$website_id;
			$site_params['system_group'] 	= 'client'.$client_info['client_id'];
 	
			$site_params['server_id'] 		= $server_id;
//			$site_params['subdomain'] 		= 'none';

			if (empty($client_info['limit_web_quota'])) {
			 //Not 0 values otherwise the script will not work
				$site_params['hd_quota'] = 1; //ISPCOnfig field
			}

			if (empty($client_info['$site_infolimit_traffic_quot'])) {
			 //Not 0 values otherwise the script will not work
				$site_params['traffic_quota'] = 1; // ISPCOnfig field
			}

			//Hardcoded values
			$site_params['allow_override'] 	= 'All';	
		
			if(!$client_info['error']) {				
				//website_path=/var/www/clients/client[client_id]/web[website_id]
				$site_params['document_root'] 	 = str_replace(array('[client_id]','[website_id]'),array($new_client_id, $website_id), $server_info['website_path']);

				//"[website_path]/web:[website_path]/tmp:/var/www/[website_domain]/web:/srv/www/[website_domain]/web:/usr/share/php5:/tmp:/usr/share/phpmyadmin"
				$site_params['php_open_basedir'] = str_replace(array('[website_path]','[website_domain]'),array($site_params['document_root'], $site_params['domain']), $server_info['php_open_basedir']);

				//Creating a site
				$result = $this->remote('sites_web_domain_add',$site_params);

				//Creating an ftp user
			}		
			return true;	
		}
		return true;
	
	}
	
	/**
		Suspend a website of an user
		@param string	username
		@param int		server id 
		@param string	reason 
		@author Julio Montoya <gugli100@gmail.com> Beeznest 2010
	*/

	public function suspend($username, $server_id, $reason = false) {
		global $main;
		global $db;		
		$this->server = $server_id;
		$params['username'] = $username;

		//Getting user info
		$user_info = $this->remote('client_get_by_username',$params);
//		$sys_userid = $user_info['sys_userid'];

		$site_params['sys_userid']	= $user_info['userid'];		
		$site_params['groups'] 		= $user_info['groups'];	

		//Getting domains by user THT problem 1 account = 1 domain
		$site_info = $this->remote('client_get_sites_by_user', $site_params);
		foreach($site_info as $domain) {
			//tacking the first domain of the user
			$domain_id = $domain['domain_id'];
			break;
		}

		$params_get_site['primary_id'] = $domain_id;
		$result = $this->remote('sites_web_domain_inactive',$params_get_site);


/*
		//Getting the site info
		$site_info = $this->remote('sites_web_domain_get',$params_get_site);

		//Suspending account active = 'n'
		$params_get_site['domain'] 			= $site_info['domain'];
		$params_get_site['hd_quota'] 		= $site_info['hd_quota'];
		$params_get_site['traffic_quota'] 	= $site_info['traffic_quota'];
		$params_get_site['allow_override'] 	= $site_info['allow_override'];

		$params_get_site['sys_userid'] 		= $site_info['sys_userid'];
		$params_get_site['sys_groupid'] 	= $site_info['sys_groupid'];

		$params_get_site['php_open_basedir']= $site_info['php_open_basedir'];
		$params_get_site['document_root'] 	= $site_info['document_root'];
		$params_get_site['system_user'] 	= $site_info['system_user'];
		$params_get_site['system_group'] 	= $site_info['system_group'];
		
		//Setting 
		$params_get_site['active'] = 'n';

		$result = $this->remote('sites_web_domain_update',$params_get_site);
		if ($result == 1) {
			return true;
		} else {
			return false;
		}*/
	}
	
	/**
		Unsuspends a website of an user
		Restrictions: Only one user can have one website
		@param string	username
		@param int		server id 
		@author Julio Montoya <gugli100@gmail.com> Beeznest
	*/

	public function unsuspend($username, $server_id) {
		global $main;
		global $db;		
		$this->server = $server_id;
		$params['username'] = $username;

		//Getting user info
		$user_info = $this->remote('client_get_by_username',$params);

		$site_params['sys_userid']	= $user_info['userid'];		
		$site_params['groups'] 		= $user_info['groups'];	

		//Getting domains by user THT problem 1 account = 1 domain
		$site_info = $this->remote('client_get_sites_by_user',$site_params);
		
		if ($site_info !==false) {
			foreach($site_info as $domain) {
				//tacking the first domain of the user
				$domain_id = $domain['domain_id'];
				break;
			}
				//		$params_get_site['client_id']  = $client_id;
			$params_get_site['primary_id'] = $domain_id;
			
			$result = $this->remote('sites_web_domain_active',$params_get_site);
	/*
			//Getting the site info
			$site_info = $this->remote('sites_web_domain_get',$params_get_site);
	
			//Suspending account active = 'n'
			$params_get_site['domain'] 			= $site_info['domain'];
			$params_get_site['hd_quota'] 		= $site_info['hd_quota'];
			$params_get_site['traffic_quota'] 	= $site_info['traffic_quota'];
			$params_get_site['allow_override'] 	= $site_info['allow_override'];
	
			$params_get_site['sys_userid'] 		= $site_info['sys_userid'];
			$params_get_site['sys_groupid'] 	= $site_info['sys_groupid'];
	
			$params_get_site['php_open_basedir']= $site_info['php_open_basedir'];
			$params_get_site['document_root'] 	= $site_info['document_root'];
			$params_get_site['system_user'] 	= $site_info['system_user'];
			$params_get_site['system_group'] 	= $site_info['system_group'];
			
			//Setting 
			$params_get_site['active'] = 'y';
	
			$result = $this->remote('sites_web_domain_update',$params_get_site);
			if ($result == 1) {
				return true;
			} else {
				return false;
			}*/
		} else {
			return false;
		}
	

	}



	/**
		Deletes an user account
		@param string	user name
		@param int		server id
		@return bool true if sucess
	*/
	public function terminate($username, $server_id) {
		$this->server = $server_id;

		//Getting user info
		$params['username'] = $username;
		$user_info = $this->remote('client_get_by_username',$params);
		if(!empty( $user_info['client_id'])) {
			$client_delete_params['client_id'] = $user_info['client_id'];
			$command = $this->remote('client_delete',$client_delete_params);
			return true;
		} else {
			return false;
		}
	}
}

?>