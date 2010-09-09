<?php
/* For licensing terms, see /license.txt */

/**
	ISPConfig Plugin for BNPanel
	@author Julio Montoya <gugli100@gmail.com> BeezNest	2010
*/

class ispconfig extends Panel {
		
	public	$name = 'ISPConfig3';
	public	$hash = false; # Password or Access Hash?
	public  $status;	
	
	public $_soap_client;
	private	$session_id;	
	
	public function __construct($server_id = null) {
		global $main;
		//@todo fix me this is an ugly fix to avoid the useless calls in the ajax::editserverhash and ajax::serverhash
		if ($server_id != '-1') {
			parent::__construct($server_id);
			$this->status = false;
			if (!empty($server_id)) {
				if ($this->_testConnection()) {			
					$this->status = true;
					$main->addlog('ispconfig::construct Testing connection ok');			
				} else {
					$main->addlog('ispconfig::construct Testing connection failed');
				}
			} else {
				$main->addlog('ispconfig::server id not provided');
			}
		}
	}
	
	public function getSessionId() {
		return	$this->session_id;
	}	
	
	public function _testConnection() {
		global $main;			
		$soap_client = $this->load();
		if ($soap_client && $this->getSessionId()) {
			return true;
		} else {
			return false;
		}				
	}	
	/**
	 * This is a wrapper of _testConnection()
	 */
	public function testConnection() {
		$result = $this->_testConnection();
		if ($result) {
			return 'Logged into ISPConfig3 Remote Server successfully. The SessionID is '.$this->getSessionId().'<br />';
		} else {
			if ($this->status) {
				return 'The Test Connection failed. Please check the host name parameters. <br />You can also check the logs <a href="?page=logs">here</a><br /> You should also check the Server id in ISPConfig';
			} else {
				return 'Cannot reach the host.<br />';
			}
		}				
	}

	/**
		Stablished a SOAP connection
	*/
	public function load() {	
		global $main;		
		$my_session_id = $this->getSessionId();
		//Try to check if there is already a soap client available
		/*if (isset($this->_soap_client) && !empty($this->_soap_client) && !empty($my_session_id)) {
			$main->addLog("ispconfig::load Returning SOAP client already set");
			return $this->_soap_client;
		} else {*/
			$data = $this->serverDetails($this->getServerId());
			if (!empty($data) && is_array($data)) {			
				//	$host_parts = parse_url($data['host']);
				//$data['host']	= $host_parts['scheme'].$host_parts['host'].$host_parts['path'];
				
				//* The URI to the remoting interface. Please replace with the URI to your real server
				$soap_location	= $data['host'].'/remote/index.php';
				$soap_uri 		= $data['host'].'/remote/';
				
				// Create the SOAP Client
				$client = new SoapClient(null, array('location' => $soap_location,'uri'=> $soap_uri));				
				try {
					//* Login to the remote server
					if($session_id = $client->login($data['user'],$data['accesshash'])) {
						if ($this->debug) {echo 'Logged into remote server successfully. The SessionID is '.$session_id.'<br />';}
						$main->addLog("ispconfig::load Session id $session_id");				
						$this->session_id = $session_id;	
						$this->_soap_client = $client;
						return $client;
					}
				} catch (SoapFault $e) {
					$main->addLog("ispconfig::load Soap error. Trying to load URL: $soap_location URI: $soap_uri ".$e->getMessage());
					if ($this->debug) 			
						//die('SOAP Error: '.$e->getMessage());
					return false;
				}
			}
			$main->addLog("ispconfig::load error seems that the server id is wrong");
			return false;
		
	}
		
	/**
		Manage the ISPConfig SOAP functions
		@param  string the action will be the same name as the specify in the ISPConfig API
		@param	array  parameters that the SOAP will used 
		@return mixed  result of the SOAP call
	*/
	private function remote($action, $params = array()) {
		global $main;
		
		$main->addLog('ispconfig::remote action called: ' . $action);
		$main->addLog('ispconfig::remote action params: ' . print_r($params,1));
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
						$soap_result 	= $soap_client->sites_web_domain_set_status($this->session_id, $primary_id, 'active');
					break;
					case 'sites_web_domain_inactive':	
						$primary_id		= $params['primary_id']; //site id
						$soap_result 	= $soap_client->sites_web_domain_set_status($this->session_id, $primary_id,'inactive');
					break;
					case 'sites_web_domain_add':
						$client_id = $params['client_id'];
						$params['client_id'] = null;
						$soap_result 	= $soap_client->sites_web_domain_add($this->session_id, $client_id  , $params);
					break;					
					case 'sites_web_domain_update':
						$client_id = $params['client_id'];
						$params['client_id'] = null;
						$soap_result 	= $soap_client->sites_web_domain_update($this->session_id, $client_id  , $params);
					break;					
					case 'sites_web_subdomain_add':
						$client_id = $params['client_id'];
						$params['client_id'] = null;
						$soap_result 	= $soap_client->sites_web_subdomain_add($this->session_id, $client_id  , $params);
					break;					
					//Get domain info
					case 'sites_web_domain_get':
						$soap_result 	= $soap_client->sites_web_domain_get($this->session_id, $params['primary_id']);
					break;
					//Get server info
					case 'server_get':
						$soap_result 	= $soap_client->server_get($this->session_id, $params['server_id'], $params['section']);//Section Could be 'web', 'dns', 'mail', 'dns', 'cron', etc
					break;					
					//Adds a DNS zone
					case 'dns_zone_add':
						$client_id 		= $params['client_id']; // client id
						$params['client_id'] = null;
						$soap_result 	= $soap_client->dns_zone_add($this->session_id, $client_id, $params);
					break;
					case 'dns_zone_get':
						$soap_result 	= $soap_client->dns_zone_get($this->session_id, $client_id, $params);
					break;
					case 'dns_zone_get_by_user':
						$client_id 		= $params['client_id']; // client id	
						$soap_result 	= $soap_client->dns_zone_get_by_user($this->session_id, $client_id, $params);
					break;
					case 'dns_zone_update':
						/*$client_id 		= $params['client_id']; // client id
						$primary_id		= $params['primary_id']; // client id
						$params['client_id'] = null;
						$params['primary_id'] = null;						
						$soap_result 	= $soap_client->dns_zone_update($this->session_id, $client_id, $primary_id, $params);*/						
					break;
					case 'dns_zone_inactive':						
						$primary_id		= $params['primary_id']; // client id
						$soap_result 	= $soap_client->dns_zone_set_status($this->session_id, $primary_id, 'inactive');				
					break;					
					case 'dns_zone_active':						
						$primary_id		= $params['primary_id']; // client id
						$soap_result 	= $soap_client->dns_zone_set_status($this->session_id, $primary_id, 'active');				
					break;				
		
					case 'dns_a_add':
						$client_id		= $params['client_id']; // client id
						$soap_result 	= $soap_client->dns_a_add($this->session_id, $client_id, $params);
					break;
					
					case 'mail_domain_add':
						$client_id 		= $params['client_id']; // client id
						$params['client_id'] = null;
						$soap_result 	= $soap_client->mail_domain_add($this->session_id, $client_id, $params);
					break;					
					//Add an email domain
					case 'mail_domain_update':
						$client_id 		= $params['client_id']; // client id
						$params['client_id'] = null;
						$soap_result 	= $soap_client->mail_domain_update($this->session_id, $client_id, $params);
					break;		
					//Change domain status
					case 'mail_domain_active':
						$primary_id 		= $params['primary_id']; 
						$soap_result 	= $soap_client->mail_domain_set_status($this->session_id, $primary_id, 'active');
					break;					
					//Change domain status
					case 'mail_domain_inactive':
						$primary_id 		= $params['primary_id'];						
						$soap_result 	= $soap_client->mail_domain_set_status($this->session_id, $primary_id, 'inactive');
					break;					
					case 'mail_domain_get_by_domain':
						$domain		= $params['domain'];
						$soap_result 	= $soap_client->mail_domain_get_by_domain($this->session_id, $domain);					
					break;					
					//Creates a mySQL database
					case 'sites_database_add':
						$client_id 		= $params['client_id']; // client id
						$params['client_id'] = null;
						$soap_result 	= $soap_client->sites_database_add($this->session_id, $client_id, $params);
					break;
					case 'sites_database_get':
						$client_id 		= $params['client_id']; // client id
						$params['client_id'] = null;
						$soap_result 	= $soap_client->sites_database_get($this->session_id, $client_id, $params);
					break;					
					case 'sites_database_get_all_by_user':
						$client_id 		= $params['client_id']; // client id
						$params['client_id'] = null;
						$soap_result 	= $soap_client->sites_database_get_all_by_user($this->session_id, $client_id, $params);
					break;
					case 'install_chamilo':
						$client_id 		= $params['client_id']; // client id
						$params['client_id'] = null;
						$soap_result 	= $soap_client->install_chamilo($this->session_id, $client_id, $params);
					break;	
					case 'client_templates_get_all': 
						$soap_result 	= $soap_client->client_templates_get_all($this->session_id);
					break;	
					case 'logout' :
						$soap_result 	= $soap_client->logout($this->session_id);
					break;
					default:
					break;
				}
				if ($this->debug) { echo 'Result: '; var_dump($soap_result); echo '------------------>><br />';}
				$main->addLog("Result of ispconfig:: $action : ".print_r($soap_result,1));
				return $soap_result;	
			} catch (SoapFault $e) {				
				$main->addLog("ispconfig::remote <strong>Soap error:</strong> ".$e->getMessage());
				$result['error']=1;
				$result['text'] = $e->getMessage();
				return $result;
			}
		}
		return false;
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
		$this->server_id = $server_id;
		$params['username'] = $username;
		if ($this->status) {
			$user_info = $this->remote('client_get_by_username',$params);	
			if (!empty($user_info['client_id'])) {	
				$client_update_params['client_id'] = $user_info['client_id'];	
				$client_update_params['password'] = $newpwd;
				$result = $this->remote('client_change_password',$client_update_params);
				if ($result) {
					return true;
				}
			}
		}
		return false;
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
	public function signup($order_id) {		
		global $main, $db, $package, $order, $user;
		
		$order_info			= $order->getOrderInfo($order_id);
		$package_id 		= $order_info['pid'];
		$domain_username 	= $order_info['username'];
		$domain_password 	= $order_info['password'];
		$user_id 			= $order_info['userid'];
		$domain 			= $order_info['real_domain'];
		$sub_domain_id 		= $order_info['subdomain_id'];
				
		$main->addLog('ispconfig::signup Order id:'.$order_id.' Domain: '.$domain);		
		
		$package_info 	= $package->getPackage($package_id);
		
		// Sets the current server
		$this->server_id = $package_info['server'];
		$data 			 = $this->serverDetails($package_info['server']);	
		
		if ($this->debug) {echo '<pre>';}		
	
		/*ISPConfig client variables
					client_id 	sys_userid 	sys_groupid 	sys_perm_user 	sys_perm_group 	sys_perm_other 	
company_name 	contact_name 	street 	zip 	city 	state 	country 	telephone 	mobile 	fax 	email 	internet 	icq 	notes 	
default_mailserver 	limit_maildomain 	limit_mailbox 	limit_mailalias 	limit_mailaliasdomain 	limit_mailforward 	limit_mailcatchall 	limit_mailrouting 	limit_mailfilter 	limit_fetchmail 	limit_mailquota 	limit_spamfilter_wblist 	limit_spamfilter_user 	limit_spamfilter_policy 	default_webserver 	limit_web_ip 	limit_web_domain 	limit_web_quota 	web_php_options 	limit_web_subdomain 	limit_web_aliasdomain 	limit_ftp_user 	limit_shell_user 	ssh_chroot 	default_dnsserver 	limit_dns_zone 	limit_dns_record 	default_dbserver 	limit_database 	limit_cron 	limit_cron_type 	limit_cron_frequency 	limit_traffic_quota 	limit_client 	parent_client_id 	
username 	password 	language 	usertheme 	template_master 	template_additional 	created_at
					*/	
		
		//User and password from the Control Panel / ISPConfig, etc
		$params['username'] 		= $domain_username;
		$params['password'] 		= $domain_password;
		
		// User information
		$user_info					= $user->getUserById($user_id);			
		$params['email'] 			= $user_info['email'];
		$params['contact_name'] 	= $user->formatUsername($user_info['firstname'], $user_info['lastname']);
		
		$params['company_name']		= $user_info['company'];
		$params['street'] 			= $user_info['address'];
		$params['city'] 			= $user_info['city'];
		$params['state'] 			= $user_info['state'];
		$params['zip'] 				= $user_info['zip'];
		$params['country'] 			= $user_info['country'];
		$params['telephone'] 		= $user_info['phone'];
		
		//Package information
		$package_info 				= $package->getPackage($package_id);
		$package_back_end_id 		= $package_info['backend'];

		//This is very important we match the package backend with the client_template id in ISPConfig
		$params['template_master'] 	= $package_back_end_id;

		//harcoding values
		$params['usertheme'] 		= 'default';	
		$params['created_at']		= time();
		$params['language'] 		= 'en'; //dafult

	/*	 //The main domain
		$is_domain = true;
		
		$subdomain_list = $main->getSubDomainByServer($package_info['server']);
		
		if ($sub_domain_id != 0 ) {		
			$subdomain = $subdomain_list[$sub_domain_id];
			$domain = $domain.'.'.$subdomain;
			$is_domain = false;
		}*/
		
		//Domain Info
		$site_params['domain'] = $domain;

		/*
		hd_quota_error_empty ok
		traffic_quota_error_empty ok 
		documentroot_error_empty ??
		sysuser_error_empty ok
		sysgroup_error_empty ok 
		allow_override_error_empty ok 
		php_open_basedir_error_empty ??
		*/
		
		$user_panel_info		= $this->getUserStatus($order_id);
		$site_info				= $this->getSiteStatus($order_id);
			

		//Adding the client
		if ($user_panel_info == false) {
			$new_client_id = $this->remote('client_add',$params);
			if ($new_client_id['error']) {
				$main->addlog('ispconfig::signup client_add error'.$new_client_id['text']);
				return false;
			}
		} else {
			$new_client_id = $user_panel_info['client_id'];			
		}
		
		//If no error 
		if(is_numeric($new_client_id)) {
			//If client is added we have the new client id	

			//Preparing variables to send to server_get
			$server_params['server_id'] 	= $this->getServerId();
			$server_params['section'] 		= 'web';
			
			//Getting server info
			$server_info = $this->remote('server_get',$server_params);

			//Getting extra info of user
			$client_info = $this->remote('client_get', array('client_id'=>$new_client_id));
		
			if (!$client_info['error']) {
				
				$website_id = 1;
				$group_id = $new_client_id + 1;
				
				//Setting parameters for the sites_web_domain_add function
				$site_params['type'] 			= 'vhost';	// harcoded in ISPConfig vhost
				$site_params['vhost_type'] 		= 'name';	// harcoded in ISPConfig vhost 
		
				$site_params['sys_userid'] 		= 1;//1; force to the admin
				$site_params['sys_groupid'] 	= 1; //ass added by the admin
				
				$site_params['system_user'] 	= 'web'.$website_id;					//This field will be overwritten by ISPconfig
				$site_params['system_group'] 	= 'client'.$client_info['client_id'];	//This field will be overwritten by ISPconfig
						
				$site_params['client_group_id'] = $new_client_id + 1;	 //always will be this 	groupd id + 1			
				$site_params['server_id'] 		= $this->getServerId();				
		
				
				$list_templates = $this->getAllPackageBackEnd();
				
				$main->addlog('ispconfig::signup using Package backend id '.$package_back_end_id);
				
				//Default values in case we don't find the template id 
				$limit_package_web_quota 		= 10;
				$limit_package_traffic_quota 	= 10;
				
				//Set the defaults 
				$site_params['hd_quota'] 		= $limit_package_web_quota;
				$site_params['traffic_quota'] 	= $limit_package_traffic_quota;
				
				if (is_array($list_templates)) {
					foreach($list_templates as $template) {
						if ($template['template_id'] == $package_back_end_id) {
							$limit_package_web_quota 	 = $template['limit_web_quota'];
							$limit_package_traffic_quota = $template['limit_traffic_quota'];
							break;						
						}
					}
				}
				
				if (empty($client_info['limit_web_quota'])) {
				 	//Not 0 values otherwise the script will not work
				 	if (!empty($limit_package_web_quota)) {
						$site_params['hd_quota'] = $limit_package_web_quota; //ISPCOnfig field
				 	}
				} else {
					$site_params['hd_quota'] = $client_info['limit_web_quota'];
				}
	
				
				if (empty($client_info['limit_traffic_quota'])) {
					 //Not 0 values otherwise the script will not work
					 if (!empty($limit_package_traffic_quota)) {
						$site_params['traffic_quota'] = $limit_package_traffic_quota; // ISPCOnfig field
					 }
				} else {
					$site_params['traffic_quota'] = $client_info['limit_traffic_quota'];
				}
				
				$main->addlog('ispconfig::signup using traffic quota '.$site_params['traffic_quota']);
				$main->addlog('ispconfig::signup using hd quota '.$site_params['hd_quota']);
	
				//Hardcoded values
				$site_params['allow_override'] 	= 'All';
				$site_params['errordocs'] 		= 1;
						
				//website_path=/var/www/clients/client[client_id]/web[website_id]
				//This job will be done by the web_edit plugin in ISPConfig 
				//$site_params['document_root'] 	 = str_replace(array('[client_id]','[website_id]'),array($new_client_id, $website_id), $server_info['website_path']);
				$site_params['document_root'] 	 = $server_info['website_path'];

				//"[website_path]/web:[website_path]/tmp:/var/www/[website_domain]/web:/srv/www/[website_domain]/web:/usr/share/php5:/tmp:/usr/share/phpmyadmin"
				//$site_params['php_open_basedir'] = str_replace(array('[website_path]','[website_domain]'),array($site_params['document_root'], $site_params['domain']), $server_info['php_open_basedir']);
				$site_params['php_open_basedir'] = $server_info['php_open_basedir'];
				
				//PHP Configuration
				$site_params['php'] 			= 'suphp'; //php available posible values				
				$site_params['ip_address'] 		= '*'; //important
				
				//Active or not
				if ($order_info['status'] == ORDER_STATUS_ACTIVE) {					
					$site_params['active'] 	 	= 'y';
				} else {
					$site_params['active'] 	 	= 'n';
				}
				 
				//Creating a site
				$result = $this->remote('sites_web_domain_add',$site_params);
				
				if ($result) {			
					
					//Adding the DNS record A
						
					$dns_a_params['server_id'] = $this->getServerId();
					$dns_a_params['client_id'] = $new_client_id;
					$dns_a_params['zone'] = '90';
					$dns_a_params['name'] = $domain.'.'; //adding a final dot
					$dns_a_params['type'] = 'A';
					$dns_a_params['data'] = '217.112.190.149';
					$dns_a_params['ttl'] = '86400';
					$dns_a_params['active'] = 'Y';
					
					$this->remote('dns_a_add', $dns_a_params);
			
					//@todo Adding MAIL and DNS Zone record. There are some hardcoded values
					
										
					// ---- Setting up the mail domain
					/*
					$mail_domain_params['client_id'] 	= $new_client_id;					
					$mail_domain_params['server_id']  	= $this->getServerId();
					$mail_domain_params['domain']	 	= $domain;
					
					if ($order_info['status'] == ORDER_STATUS_ACTIVE) {
						$mail_domain_params['active'] 	 	= 'y';
					} else {
						$mail_domain_params['active'] 	 	= 'n';
					}
					
					$domain_id = $this->remote('mail_domain_add', $mail_domain_params);
					
					// ---- Setting up the DNS ZONE					

					$dns_domain_params['client_id'] = $new_client_id;
					$dns_domain_params['server_id'] = $this->getServerId();
					$dns_domain_params['origin']	= $domain;
					
					$dns_domain_params['ns']		= '8.8.8.8';
					$dns_domain_params['mbox'] 		= 'mbox.beeznest.com.';//@todo 
					$dns_domain_params['refresh'] 	= 28800;
					$dns_domain_params['retry'] 	= 7200;
					$dns_domain_params['expire']	= 604800;
					$dns_domain_params['minimum']	= 604800;
					$dns_domain_params['ttl']		= 604800;	
					
					if ($order_info['status'] == ORDER_STATUS_ACTIVE) {
						$dns_domain_params['active'] 	 	= 'y';
					} else {
						$dns_domain_params['active'] 	 	= 'n';
					}				
					$result = $this->remote('dns_zone_add', $dns_domain_params);
					*/
					//----- Logout of the remoting
											
					$result = $this->remote('logout');								
					/* Extra params
					serial				
					xfer
					also_notify
					update_acl
					*/
					return true;					
				}		
			} else {
				$main->addlog('ispconfig::signup client_get error'.$client_info['text']);				
			}					
		}
		return false;
	}
	
	/**
		Suspend a website/order
		@param string	order id 
		@param int		server id 
		@param string	reason 
		@author Julio Montoya <gugli100@gmail.com> BeezNest 2010
	*/
	public function suspend($order_id, $server_id, $reason = false) {
		global $main, $db, $order, $user;
		
		$order_info = $order->getOrderInfo($order_id);	
		
		$this->server_id = $server_id;
		$params['username'] = $order_info['username'];

		//Getting user info
		$user_info = $this->remote('client_get_by_username',$params);		
		
		if (is_array($user_info) && !empty($user_info)) {
			
			$site_params['sys_userid']	= $user_info['userid'];		
			$site_params['groups'] 		= $user_info['groups'];	
	
			//Getting all domains from this user
			$site_info = $this->remote('client_get_sites_by_user', $site_params);			
					
			$domain_id = 0;
			if ($site_info !== false) {				
				foreach($site_info as $domain) {				
					if ($order_info['real_domain'] == $domain['domain']) {
						$domain_id = $domain['domain_id'];
						break;
					}
				}				
				if ($domain_id != 0) {
					$params_get_site['primary_id'] = $domain_id;
					
					$result = $this->remote('sites_web_domain_inactive',$params_get_site);				
					/*	
					$params['client_id'] = $user_info['client_id'];	
					$params['server_id'] = $this->getServerId();
					
					//Searching fot DNS zones
					$dns_zone_list = $this->remote('dns_zone_get_by_user',$params);				
					$dns_id = 0;
					if (is_array($dns_zone_list) && count($dns_zone_list) > 0) {
						foreach($dns_zone_list as $dns_soa) {
							if ($dns_soa['origin'] == $order_info['domain']) {
								$dns_id = $dns_soa['id'];
								break;
							}
						}
					}	
								
					//Inactive DNS ZONE					
					if (!empty($dns_id)) {
						$dns_domain_params['primary_id'] = $dns_id;
						$result = $this->remote('dns_zone_inactive',$dns_domain_params);				
					}
					
					//Searching for mail domains
					$params['domain'] = $order_info['domain'];
					$domain_list = $this->remote('mail_domain_get_by_domain',$params);
					$mail_domain_id = 0;
					if(!empty($domain_list)) {
						foreach($domain_list as $domain) {
							if ($domain['domain'] == $order_info['domain']) {
								$mail_domain_id = $domain['domain_id'];
								break;
							}
						}				
						//Inactive mail domain	
						if (!empty($mail_domain_id)) {						
							$mail_update_status_params['primary_id'] = $mail_domain_id;
							$this->remote('mail_domain_inactive', $mail_update_status_params);
						}
					}
					*/
					return true;
				}					
			}
			return false;			
		}
		//This means that the order was not sent to the Control Panel so we cant enable locally
		return true;
	}
	
	/**
		Unsuspends a website/order
		@param string	order id
		@param int		server id 
		@author Julio Montoya <gugli100@gmail.com> BeezNest
	*/
	public function unsuspend($order_id, $server_id) {
		global $main,$db, $order, $user;
		$order_info = $order->getOrderInfo($order_id);
						
		$this->server_id = $server_id;
		$params['username'] = $order_info['username'];

		//Getting user info
		$user_info = $this->remote('client_get_by_username',$params);	

		$site_params['sys_userid']	= $user_info['userid'];		
		$site_params['groups'] 		= $user_info['groups'];	

		$site_info = $this->remote('client_get_sites_by_user',$site_params);

		$domain_id = 0;
		if ($site_info !== false) {
			
			foreach($site_info as $domain) {
				if ($order_info['real_domain'] == $domain['domain']) {
					$domain_id = $domain['domain_id'];
					break;
				}
			}
				
			if (!empty($domain_id)) {
				
				$params_get_site['primary_id'] = $domain_id;
						
				//Inactive domain	
				$result = $this->remote('sites_web_domain_active',$params_get_site);
				
				/*
				// DNS SOA				
				$params['client_id'] = $user_info['client_id'];	
				$params['server_id'] = $this->getServerId();
				
				$dns_zone_list = $this->remote('dns_zone_get_by_user',$params);				
				
				$dns_id = 0;				
				if (is_array($dns_zone_list) && count($dns_zone_list) > 0) {
					foreach($dns_zone_list as $dns_soa) {
						if ($dns_soa['origin'] == $order_info['domain']) {
							$dns_id = $dns_soa['id'];
							break;
						}
					}
				}
					
				//Inactive zone
				if (!empty($dns_id)) {
					$dns_domain_params['primary_id']		= $dns_id;
					$result = $this->remote('dns_zone_active',$dns_domain_params);				
				}
				
				//Searching for mail domains
				$params['domain'] = $order_info['domain'];
				$domain_list = $this->remote('mail_domain_get_by_domain',$params);
				$mail_domain_id = 0;
				
				foreach($domain_list as $domain) {
					if ($domain['domain'] == $order_info['domain']) {
						$mail_domain_id = $domain['domain_id'];
						break;
					}
				}
						
				//Inactive mail domain	
				if (!empty($mail_domain_id)) {						
					$mail_update_status_params['primary_id'] = $mail_domain_id;
					$this->remote('mail_domain_active', $mail_update_status_params);
				}	
				*/								
				return true;				
			}
			return false;
		}
		return true;
	}
	
	/**
	 * Install Chamilo
	 */
	public function installChamilo($order_id, $params = array()) {
		global	$main, $order;
		$main->addLog("ispconfig::install_chamilo Order #$order_id");
		
		if ($this->status) {
					
			$order_info = $order->getOrderInfo($order_id);
			//Getting user info 
			$params['username'] = $order_info['username'];
			$user_info = $this->remote('client_get_by_username',$params);
			
			if (!empty($user_info)) {
				
				$site_params['sys_userid']	= $user_info['userid'];		
				$site_params['groups'] 		= $user_info['groups'];	
				
				$site_info = $this->remote('client_get_sites_by_user',$site_params);
						
				$domain_id = 0;
				if ($site_info !==false) {
					foreach($site_info as $key=>$domain) {
						if ($order_info['real_domain'] == $domain['domain']) {
							$domain_id = $domain['domain_id'];
							break;
						}
					}
				}
				
				if (!empty($domain_id)) {
					//Create a new database for Chamilo		
					
					//$db_part_name = substr($order_info['domain'],0,6);
					if (isset($order_info['subdomain_id']) && $order_info['subdomain_id'] != 0) {
						$url_parts['domain'] = $order_info['domain'];
					} else {					
						$url_parts = $main->parseUrl($order_info['domain']);					
						/*if ($url_parts['subdomain'] != '') {
							$url_parts['domain'] = $url_parts['subdomain'];					
						} else {*/					
						$url_parts['domain'] = substr($url_parts['domain'], 0 , strlen($url_parts['domain']) - ( strlen($url_parts['extension']) + 1) );
						//}					
					}				
					
					//We take only 20 chars
					$url_parts['domain'] 				= substr($url_parts['domain'], 0, 20);
					
					$mysql_params['client_id'] 			= $user_info['client_id'];				
					$mysql_params['server_id']			= $this->getServerId();				
					$mysql_params['type'] 				= 'mysql';
					
					//$generate_username					= $main->generateUsername();
					$mysql_params['database_name'] 		= 'c'.$user_info['client_id'].'_'.$url_parts['domain'].'_chamilo_main';
					$mysql_params['database_user'] 		= 'c'.$user_info['client_id'].'_'.$url_parts['domain'];
					$mysql_params['database_password'] 	= $main->generatePassword();
					$mysql_params['database_charset']	= 'utf8';
					$mysql_params['remote_access'] 		= 'n';
					$mysql_params['active'] 			= 'y';				
					
					$database_id = $this->remote('sites_database_add', $mysql_params);
					
					if (is_numeric(($database_id))) {		
						$install_params['package_id'] 	= 1; // this value can be found in the ISPConfig install_package table
						$install_params['domain_id'] 	= $domain_id ; // Chamilo
						$install_params['status'] 		= 2;// 0 not install / 1 installed 2 pending 3 error
						$install_params['database_id'] 	= $database_id;  
						$result = $this->remote('install_chamilo', $install_params);
						$main->addLog("ispconfig::install_chamilo domain_id #$domain_id db id #$database_id db name {$mysql_params['database_name']} ");
						return true;
					} else {
						if ($database_id['error']) {
							$main->addLog("ispconfig::install_chamilo error: {$database_id['text']}");
							return false;	
						}			
					}
				}
			}
		}
		return false;		
	}

	/**
		Deletes an user account
		@param string	user name
		@param int		server id
		@return bool true if sucess
	*/
	
	public function terminate($username, $server_id) {
		$this->server_id = $server_id;
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
	
	public function getAllPackageBackEnd() {
		$result = $this->remote('client_templates_get_all', null);		
		return $result;
	}
	
	public function parseBackendInfo($data) {
		if (!empty($data)) {
			$html .='<ul>';
			foreach ($data as $key=>$value) {
				$html .='<li>';
				$html.="<strong>$key</strong> :  $value";
				$html .='</li>';			
			}
			$html .='</ul>';
		} else {
			$html = 'No data available';
		}
		return $html;		
	}
	
	public function getMethods() {
		$soap_client = $this->load();		
		var_dump($soap_client ->get_class_methods());		
	}
	
	
	public function getUserStatus($order_id) {
		global $main, $order;
		$order_info = $order->getOrderInfo($order_id);		
		$params['username'] = $order_info['username'];
		$main->addlog("ispconfig::getUserStatus Order id: $order_id");
		
		//Getting user info
		$user_info = $this->remote('client_get_by_username',$params);
				
		if (is_array($user_info) && !empty($user_info)) {			
			return $user_info;				
		}		
		return false;	
	}
	
	public function getSiteStatus($order_id) {
		global $main, $order;
		$order_info = $order->getOrderInfo($order_id);		
		$params['username'] = $order_info['username'];		
		$main->addlog("ispconfig::getSiteStatus Order id: $order_id");
		
		//Getting user info
		$user_info = $this->remote('client_get_by_username',$params);
				
		if (is_array($user_info) && !empty($user_info)) {			
			$site_params['sys_userid']	= $user_info['userid'];		
			$site_params['groups'] 		= $user_info['groups'];	
	
			//Getting all domains from this user
			$site_info = $this->remote('client_get_sites_by_user', $site_params);
						
			if (isset($site_info) && is_array($site_info) ) {
				foreach($site_info as $key=>$domain) {
					if ($order_info['real_domain'] == $domain['domain']) {
						$my_domain = $domain;
						break;
					}
				} 				
				if 	(!empty($my_domain) && is_array($my_domain)) {
					$main->addlog("ispconfig::getSiteStatus Domain exists: {$my_domain['domain']} {$my_domain['domain_id']}");
					return $my_domain;
				}			
			}							
		}		
		return false;	
	}
	
	public function getServerStatus() {
		global $main;
		$main->addlog("ispconfig::getServerStatus");
		if ($this->status) {
			
			$main->addlog("ispconfig::getServerStatus status = true");
			//we should use only the load function
			$server_params['server_id'] 	= $this->getServerId();
			$server_params['section'] 		= 'web';
			
			//Getting server info
			$server_info = $this->remote('server_get',$server_params);
			if (!empty($server_info)) {
				$result = true;
			} else {
				$result = false;
			}
			return $result;
		} else {
			$main->addlog("ispconfig::getServerStatus status = false");
		}
		return false;		
	}
}

/**
 * 
To insert in the ISPConfig 
CREATE table install_package( package_id int NOT NULL AUTO_INCREMENT,name varchar(255),	package_path varchar(255),version varchar(255) ,PRIMARY KEY (package_id));
INSERT into install_package (package_id, name, package_path, version) values ('1', 'chamilo', '/var/www/chamilo-1.8.7.1-stable', '1.8.7.1');		
CREATE table install_package_web_domain ( id int NOT NULL AUTO_INCREMENT, domain_id int,package_id int, database_id int, status int,  PRIMARY KEY (id));
 */
