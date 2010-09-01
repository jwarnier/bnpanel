<?php
/* For licensing terms, see /license.txt */

/**
 * This class is the interface between the Billing System and the Control Panel (ISPConfig, Cpanel, Direct Admin)
 * It cancel, suspend the orders. * 
 */
 
class server extends Model {
		
	public 	$columns 	= array('id', 'name','host', 'user','accesshash','type');	
	public	$table_name 	= 'servers';	
	private $servers = array(); # All the servers in a array
	public 	$availableServerList = array();
	
	public function __construct() {
		$this->availableServerList = $this->getAvailablePanelsFromDir();
		/*			
		if (isset($_SESSION['available_server_list']) && is_array($_SESSION['available_server_list'])) {
			$this->availableServerList  = $_SESSION['available_server_list'];
		} else {
			$this->availableServerList = $this->getAvailablePanelsFromDir();	
			$_SESSION['available_server_list'] = $this->availableServerList;
		}*/
	}
	
	/**
	 * Reads the includes/servers folder
	 * @todo right know this code is hardcoded
	 */
	private function getAvailablePanelsFromDir() {
		/*$server_path = LINK.'servers';
		$my_list = array();
		if (is_dir($server_path) && $handle_type = opendir($server_path)) {
			while (FALSE !== ($file = readdir($handle_type))) {  		
	    		if (!in_array($file, array('.','..')) && !in_array($file,array('panel.php', 'index.html'))) {
	    			$my_list[] = basename($file,'.php');	    			
	    		}
			}
		}*/
		$my_list = array('da','ispconfig','test','whm');
		return 	$my_list;
	}
	
	public function getAvailablePanels() {
		return $this->availableServerList;
	}	
	
	/**
	 * Adds a server in the Database
	 * @param	array	parameters 
	 */
	public function create($params) {
		global $main;		
		$server_id = $this->save($params);
		if (!empty($server_id) && is_numeric($server_id )) {
			$main->addLog("server:create #$server_id");	
		}
		return $server_id;
	}
	
	/** 
	 * Edits a server
	 * 
	 */
	public function edit($server_id, $params) {
		global $main;
		$this->setId($server_id);		
		$this->update($params);
		$main->addLog("server:edit #$server_id updated");
	}
	
	/**
	 * Deletes a server
	 */
	public function delete($server_id) { # Deletes invoice upon invoice id
		global $main;
		$this->setId($server_id);	
		parent::delete();
		$main->addLog("server:delete id #$id deleted ");
		return true;
	}
	
	
	/**
	 * Return the server class
	 * @param	int		server id	
	 */
	public function loadServer($server_id) {
		global $main;
		$main->addlog("server::loadServer server_id #$server_id");
		$server_info = $this->getServerById($server_id); # Determine server				
		$server_type = $server_info['type'];
				
		//Abstract class Panel added
		require_once LINK."servers/panel.php";		
		if (in_array($server_type, $this->getAvailablePanels())) {
			$link = LINK."servers/".$server_type.".php"; 
			if(!file_exists($link)) {
				$main->addlog("server::loadServer function error. The server  $server_type doesn't exist!");				
				return false;	
			} else {
				require_once $link; # Get the server							
				$serverphp = new $server_type($server_id);
				$main->addlog("server::loadServer function. Loading $server_type ");
				return $serverphp;
			}
		}
		return false;
	}
	
	/**
	 * Loads the Server class (isoconfig, direct admin,  cpanel)
	 * @param	int		package id
	 * @return 	mixed	false or the instance of the server class 
	 */
	private function createServer($package_id) { # Returns the server class for the desired package
		global $type, $main;		
		$server_id 	 = $type->determineServer($package_id);
		$server_type = $type->determineServerType($server_id); # Determine server		
		//Abstract class Panel added
		require_once LINK."servers/panel.php";		
		if (in_array($server_type, $this->getAvailablePanels())) {
			$link = LINK."servers/".$server_type.".php";
			if(!file_exists($link)) {
				$main->addlog("server::loadServer function error. The server  $server_type doesn't exist!");				
				return false;	
			} else {
				require_once $link; # Get the server				
				$serverphp = new $server_type($server_id);
				return $serverphp;
			}
		}
	}
	
	/**
	 * Creates a user account, order and invoice
	 */
	public function signup() { # Echos the result of signup for ajax
		global $main, $db, $type, $addon, $order, $package, $email, $user;
		
		$main->addLog("Executing server::signup function");
			
		//Check package details
		$package_id 	= intval($main->getvar['package']);
		$package_info 	= $package->getPackage($package_id);		
		
		if (empty($package_info) || $package_info['is_disable'] == 1) {
			echo 'Package doesn\'t exist please contact the administrator';
			return;
		} 
		
		if (!$main->checkToken(false)) {
			echo 'Token Error';
			return;
		}
		$user_id = '';		
		$final_domain = '';
		$subdomain_id = 0;
		
		if($main->getvar['domain'] == 'dom') { # If Domain
			if(!$main->getvar['cdom']) {
				echo "Please fill in the domain field!";
				return;
			} else {
				$data = explode(".",$main->getvar['cdom']);
				if(!$data[1]) {
					echo "Your domain is the wrong format!";	
					return;
				}
				if ($db->config("tldonly")) { # Are we alowing TLD's Only?
					$ttlparts = count($data);
					if ($ttlparts > 2) {
						$dmndata = array('com', 'net', 'co', 'uk', 'org');
						if (!in_array($data[$ttlparts - 2], $dmndata)) {
							echo "We only allow Top Level Domains (.com/.net/.org, etc)";
							return;
						}
					} # If we get past this, its a top level domain :D yay
				}
			}
			//In this case subdomain & domain is the same thing
			$final_domain = $sub_domain = $main->getvar['fdom'] = $main->getvar['cdom'];
		}		
		
		if($main->getvar['domain'] == 'sub') { # If Subdomain
			if(!$main->getvar['csub']) {
				echo "Please fill in the subdomain field!";
				return;
			}						
			$sub_domain 	= $main->getvar['csub'];
			$subdomain_id 	= $main->getvar['csub2'];			
			$subdomain_list = $main->getSubDomainByServer($package_info['server']);
			if ($subdomain_id != 0 ) {		
				if (isset($subdomain_list[$subdomain_id])) {
					$domain_is_correct = true;
					$final_domain = $sub_domain;
				}			
			}
		}
						
		if ($order->domainExistInOrder($sub_domain, $subdomain_id)) {
			echo "Domain already exists";
			return;
		}
				
		$user_already_registered = false;
		
		//Check if the client is already logged in to ask for user information
		if ($main->getCurrentUserId() === false) {
			
			if((!$main->getvar['username'])) {
				echo "Please enter a username!";
				return;
			} else {				
				$user_result = $user->getUserByUserName($main->getvar['username']);
				if(!empty($user_result)) {
					echo "That username already exists!";
					return;
				}
			}
			if((!$main->getvar['password'])) {
			   echo "Please enter a password!";
			   return;
			} else {
				if($main->getvar['password'] != $main->getvar['confirmp']) {
					echo "Your passwords don't match!";
					return;
				}
			}
			if((!$main->getvar['email'])) {
			   echo "Please enter a email!";
			   return;
			}
			if((!$main->check_email($main->getvar['email']))) {
					echo "Your email is the wrong format!";	
					return;
			} else {
				$email_result = $user->getUserByEmail($main->getvar['email']);				
				if(!empty($email_result)) {
					echo "That e-mail address is already in use!";
					return;
				}
			}
			if(($main->getvar['human'] != $_SESSION["pass"])) {
			   echo "Human test failed!";
			   //return;
			}
			if((!$main->getvar['firstname'])) {
			   echo "Please enter a valid first name!";
			   return;
			}
			if((!$main->getvar['lastname'])) {
			   echo "Please enter a valid last name!";
			   return;
			}
			if((!$main->getvar['address'])) {
			   echo "Please enter a valid address!";
			   return;
			}
			if((!$main->getvar['city'])) {
			   echo "Please enter a valid city!";
			   return;
			}
			if((!$main->getvar['zip'])) {
			   echo "Please enter a valid zip code!";
			   return;
			}
			if((!$main->getvar['state'])) {
			   echo "Please enter a valid state!";
			   return;
			}
			if((!$main->getvar['state'])) {
			   echo "Please enter a valid state!";
			   return;
			}
			if((!$main->getvar['country'])) {
			   echo "Please select a country!";
			   return;
			}
			if ((!preg_match("/^([a-zA-Z\.\'\ \-])+$/",$main->getvar['firstname']))) {
				echo "Please enter a valid first name!";
				return;			
			}
			if ((!preg_match("/^([a-zA-Z\.\'\ \-])+$/",$main->getvar['lastname']))) {
				echo "Please enter a valid last name!";
				return;			
			}
			if ((!preg_match("/^([0-9a-zA-Z\.\ \-])+$/",$main->getvar['address']))) {
				echo "Please enter a valid address!";
				return;
			}
			if ((!preg_match("/^([a-zA-Z ])+$/",$main->getvar['city']))) {
				echo "Please enter a valid city!";
				return;			
			}
			if ((!preg_match("/^([a-zA-Z\.\ -])+$/",$main->getvar['state']))) {
				echo "Please enter a valid state!";
				return;
			}
			if((strlen($main->getvar['zip']) > 7)) {
				echo "Please enter a valid zip/postal code!";
				return;
			}
			if ((!preg_match("/^([0-9a-zA-Z\ \-])+$/",$main->getvar['zip']))) {
				echo "Please enter a valid zip/postal code!";
				return;
			}
			if((strlen($main->getvar['phone']) > 15)) {
				echo "Please enter a valid phone number!";
				return;
			}
			if ((!preg_match("/^([0-9\-])+$/",$main->getvar['phone']))) {
				echo "Please enter a valid phone number!";
				return;
			}
		} else {
			//The user is already in. We load the user information from the DB			
			
			$user_id = $main->getCurrentUserId();
			if (!empty($user_id) && is_numeric($user_id) ) {
				$user_info = $user->getUserById($user_id);	
				if (!empty($user_info)) {						
					$system_username 	= $user_info['user'];
					$system_password 	= $user_info['password'];			
					$system_email		= $user_info['email'];			
							
					$main->getvar['firstname'] = $user_info['firstname']; 
					$main->getvar['lastname']  = $user_info['lastname']; 
										
					$user_already_registered = true;
				} else {
					echo 'Please try again';
					$main->logout();		
					return;	
				} 
			} else {
				echo 'Please try again';
				$main->logout();		
				return;		
			}
		}		
		
		// Creates the "paid" or "free" class 
		$package_type_class = $type->createType($package_info['type']);	
		
		//@todo not sure if this will be used
		if($package_type_class->signup) {
			$pass = $package_type_class->signup();			
			if($pass) {
				echo $pass;	
				return ;
			}
		}
		
		$date 				= time();
		$billing_cycle_id 	= $main->getvar['billing_id'];
					
		if ($user_already_registered == false) {
			
			/* Creating a new user */												
			$system_username			= $main->getvar['user'] = $main->getvar['username'];	
			$system_password			= $main->getvar['password'];
			$system_email				= $main->getvar['email'];
			
			$main->getvar['signup'] 	= $date;			
			$main->getvar['status'] 	= USER_STATUS_ACTIVE; 
			
			//Creates a new user
			$user_id 					= $user->create($main->getvar);
			
			//If user is created
			if (!empty($user_id) && is_numeric($user_id)){
				$user_already_registered = true;
				$main->addLog('server::signup User id #'.$user_id.' registered');										
				$main->clientLogin($system_username, $system_password);
			} else {
				$main->addLog("server::signup Error while trying to create an user $system_username $system_email ");		
				return "Can't create an user";
			}																		  
		} 
			
		if ($user_already_registered == true) {
			
			/* Creating a new order */ 
			
			$params['userid'] 			= $user_id;
			$params['domain'] 			= $final_domain;
			$params['pid'] 				= $package_id;
			$params['signup'] 			= $date;						
			$params['status'] 			= ORDER_STATUS_WAITING_USER_VALIDATION;
									
			$params['additional']		= '';//@todo this field is not used
			$params['billing_cycle_id'] = $billing_cycle_id;
			
			$user_info = $user->getUserById($user_id);
			//Username + password for the ISPConfig
			$params['username']			= substr($user_info['firstname'], 0 ,1).substr($user_info['lastname'], 0 ,1).$main->generateUsername();
			$params['password']			= $main->generatePassword();
			$params['subdomain_id']		= $subdomain_id;			

			//Getting mandatory addons and adding if somebody 
			$mandatory_addons = $addon->getMandatoryAddonsByPackage($package_id);
			if(is_array($mandatory_addons) && !empty($mandatory_addons)) {
				foreach($mandatory_addons as $key=>$addon_item) {
					if (in_array($key, $main->getvar['addon_ids'])) {
						continue;
					} else {
						array_push($main->getvar['addon_ids'], $key);
					}				
				}
			}	
			
			//Create an order
			if (!empty($params['userid']) && !empty($params['pid'])) {
				$order_id = $order->create($params);
				
				//Add addons to the new order						
				$order->addAddons($order_id, $main->getvar['addon_ids']);				
			}
			
			$array['USER']		= $system_username;				
			$array['PASS'] 		= $system_password; 
			$array['EMAIL'] 	= $system_email;
			$array['DOMAIN'] 	= $final_domain;	
			
			//@todo Email User confirmation
			if ($user_already_registered == true && $user_info['status'] == USER_STATUS_ACTIVE) {			
				$array['CONFIRM'] 	= '';
			} else {
			//	$array['CONFIRM'] 	= '<span style="font-weight: bold;">Confirmation Link: </span>'.$db->config('url') . "client/confirm.php?u=" . $user_name . "&c=" . $date;	
				$array['CONFIRM'] 	= '';
			}			
			$array['PACKAGE'] 	= $package_info['name'];
								
			//We do not sent tout suite the ISPConfig calls to create a new site, we wait that a user paid the invoice! 
			//$done = $serverphp->signup($order_id, $package_id, $params['username'], $params['password'], $user_id, $sub_domain, $subdomain_id);
			
			//Package does not needs validation
			if ($package_info['admin'] == 0) {
				//New hosting account created	
				echo "<strong>Your account has been completed!</strong><br />You may now use the client login bar to see your client area or proceed to your control panel.";							
			} elseif($package_info['admin'] == 1) {
				$email_to_admin = $db->emailTemplate('orders_needs_validation');				
				$email->staff($email_to_admin['subject'], $email_to_admin['content']);				
				echo "<strong>Your order is awaiting admin validation!</strong><br />An email has been dispatched to the address on file. You will recieve another email when the admin has overlooked your account.";				
			} else {				
				echo "Something with admin validation went wrong. Your account should be running but contact your host administrator.";	
			}			
		} else {
			echo "There was a problem when creating a user. Please contact the system administrator.";	
		}
		
		//If the package is paid	
		if($package_info['type'] == 'paid') {
			global $invoice, $package, $billing;
			//The order was saved with an status of admin validation now we should create an invoice an set the status to wait payment
			$due 		= time();
			$notes 		= '';			
			
			//1. Calculating the amount for the package depending on the billing cycle
			$package_amount = 0;
			$package_billing_info = $package->getPackageByBillingCycle($package_id, $billing_cycle_id);	
			$billing_info = $billing->getBilling($billing_cycle_id);
			
			if (is_array($package_billing_info) && isset($package_billing_info['amount'])) {					
				$package_amount = $package_billing_info['amount'];
			}				
			//2. Generating the addon serialized array
			$addon_fee = $addon->generateAddonFee($main->getvar['addon_ids'], $billing_cycle_id, true);
							
			//3. Creating the invoice
			
			$invoice_params['uid'] 		= $user_id;
			$invoice_params['amount'] 	= $package_amount;			
			$invoice_params['due'] 		= $due + $billing_info['number_months']*30*24*60*60;
			$invoice_params['notes'] 	= $notes;
			$invoice_params['addon_fee']= $addon_fee;
			$invoice_params['status'] 	= INVOICE_STATUS_WAITING_PAYMENT;
			$invoice_params['order_id'] = $order_id;
			
			$invoice_id = $invoice->create($invoice_params);
			if (is_numeric($invoice_id)) {							
				//This variable will be read in the Ajax::ispaid function
				$_SESSION['last_invoice_id'] = $invoice_id;
			}												
			echo '<div class="errors"><b>You are being redirected to payment! It will load in a couple of seconds..</b></div>';
		}	
	}	
	
	/**
	 * 	Deletes a user account from the Control Panel System (ISPConfig, Cpanel)
	 *  Not recomended to use
	 */	 
	public function terminate($order_id, $reason = false) { # Deletes a user account from the package ID
		return false; // not implemented yet
		global $db, $main, $type, $email, $order, $user;
		$order_info = $order->getOrderInfo($order_id);
		if (is_array($order_info) && !empty($order_info)) {
			$package_id = $order_info['pid'];	
			$server_id = $type->determineServer($package_id);
			if(!is_object($this->servers[$server_id])) {
				$this->servers[$server_id] = $this->createServer($package_id); # Create server class
			}
			$user_info = $user->getUserById($order_info['userid']);
			if($this->servers[$server_id]->terminate($user_info['user'], $server_id) == true) {
				$date = time();
				$emaildata = $db->emailTemplate('termacc');
				$array['REASON'] = "Admin termination.";
				$email->send($user_info['email'], $emaildata['subject'], $emaildata['content'], $array);
				/*
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Terminated ($reason)')");*/
				//$db->query("DELETE FROM `<PRE>user_packs` WHERE `id` = '{$data['id']}'");
				//$db->query("DELETE FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
				$user->updateUserStatus($user_info['id'], USER_STATUS_DELETED);
				return true;
			}
			else {
				return false;	
			}			
		} else {
			$array['Error'] = "That order doesn't exist or cannot be terminated!";
			$array['User PID'] = $order_id;
			$main->error($array);
			return;	
		}	
	}
	
	/**
	 * Cancel an order
	 * 1. Suspend the website
	 * 2. Sets the Order with status ORDER_STATUS_CANCELLED
	 * @param	int		order id
	 * @param	string	reason?
	 * 
	 */	 
	public function cancel($order_id, $reason = false) { # Deletes a user account from the package ID
		global $db, $main, $package, $email, $user, $order;
		$order_info = $order->getOrderInfo($order_id);
		if (is_array($order_info) && !empty($order_info)) {
			$user_info = $user->getUserById($order_info['userid']);
			
			$package_info = $package->getPackage($order_info['pid']);
			if (!empty($package_info)) {
				$server_id = $package_info['server'];
				$serverphp= $this->createServer($order_info['pid']); # Create server class
			
				//Suspending the website of that user
				if($serverphp->suspend($order_id, $server_id) == true) {
					$order->updateOrderStatus($order_id, ORDER_STATUS_CANCELLED);		
					return true;
				}				
			}
			return false;
		} else {
			$array['Error'] = "That order doesn't exist or cannot be cancelled! Are you trying to cancel an already cancelled account?";
			$array['User PID'] = $order_id;
			$main->error($array);		
		}
	}
	
	/**
	 * @todo Packages should be suspend not the users 
	 */
	public function decline($id) { # Deletes a user account from the package ID
		return false; // not implemented yet
		global $db, $main, $type, $email;
		$query = $db->query("SELECT * FROM `<PRE>orders` WHERE `id` = '{$db->strip($id)}' AND `status` != '9'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist or cannot be cancelled! Are you trying to cancel an already cancelled account?";
			$array['User PID'] = $id;
			$main->error($array);
			return;	
		} else {
			$data = $db->fetch_array($query);
			$query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
			$data2 = $db->fetch_array($query2);
			$server = $type->determineServer($data['pid']);
			if(!is_object($this->servers[$server])) {
				$this->servers[$server] = $this->createServer($data['pid']); # Create server class
			}
			if($this->servers[$server]->terminate($data2['user'], $server) == true) {
				/*$date = time();
				$emaildata = $db->emailTemplate('orders_cancelled');
				$array['REASON'] = "Account Declined.";
				$email->send($data2['email'], $emaildata['subject'], $emaildata['content'], $array);
				
				$db->query("UPDATE `<PRE>user_packs` SET `status` = '9' WHERE `id` = '{$data['id']}'");
				$db->query("UPDATE `<PRE>users` SET `status` = '9' WHERE `id` = '{$db->strip($data['userid'])}'");
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Declined  (Package ID $id)')");*/
				return true;
			}
			else {
				return false;	
			}
		}
	}
	
	/**
	 * Suspends an order
	 * @param	int		order id	
	 * @param	bool	reason
	 */
	 
	public function suspend($order_id, $reason = false) { # Suspends a user account from the package ID
		global $db, $main, $type, $email, $order,$user, $package;
		$order_info = $order->getOrderInfo($order_id);		
		if (is_array($order_info) && !empty($order_info)) {
			$user_info = $user->getUserById($order_info['userid']);
			$package_info = $package->getPackage($order_info['pid']);
			
			$donestuff = false;
			if (!empty($package_info)) {
				$server_id = $package_info['server'];		
				
				$serverphp = $this->createServer($order_info['pid']);
				if ($serverphp != false) {					
					$done = $serverphp->suspend($order_id, $server_id, $reason);					
					if ($done) {
						$main->addlog("server::suspend Order #$order_id");
						return true;
					}
				}	
			}	
			$main->addlog("server::suspend Error with Order #$order_id");
			return false;	
		} else {
			$main->addlog("server::suspend Order not found #$order_id");	
		}
	}
	
	/**
	 * Unsuspend an Order
	 * @param	int		order id 
	 */
	public function unsuspend($order_id) { # Unsuspends a user account from the package ID
		global $db, $main, $package, $email, $order, $user;
		
		$order_info = $order->getOrderInfo($order_id);
		if (is_array($order_info) && !empty($order_info)) {
			$user_info = $user->getUserById($order_info['userid']);			
			$package_info = $package->getPackage($order_info['pid']);
			if (!empty($package_info)) {
				$server_id = $package_info['server'];	
				$serverphp = $this->createServer($order_info['pid']); # Create server class
				if ($serverphp != false) {
					if($serverphp->unsuspend($order_id, $server_id) == true) {
						$main->addlog("server::unsuspend Order #$order_id");
						return true;
					}
				}		
			}
			return false;
		} else {
			$main->addlog("server::suspend Order not found #$order_id");			
			return;
		}
	}
	
	/**
	 * Changes the order (Cpanel, ISPConfig) password 
	 * @param	int		order id
	 * @param	string	new password
	 */
	public function changePwd($order_id, $new_password) { # Changes user's password.
		global $main, $order, $package;
		$order_info = $order->getOrderInfo($order_id);
		$package_info = $package->getPackage($order_info['pid']);
		$server_id = $package_info['server'];	
		$serverphp = $this->createServer($order_info['pid']); # Create server class		
		if($serverphp->changePwd($order_info['username'], $new_password, $server_id) == true) {
			$main->addlog("server::changePwd Control Panel password updated $order_id");
			$order->edit($order_id, array('password'=>$new_password));
			return true;
		}
		return false;	
	}	
	
	/**
	 * Approves a user account
	 * @todo Not to be use right now
	 ***/
	
	public function approve($id) { # Approves a user's account (Admin Validation).
		return false; // not implemented yet
		global $db, $main, $type, $email;
		$query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND (`status` = '2' OR `status` = '3' OR `status` = '4')");
		$uquery = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$query['userid']}' AND (`status` = '1')");
		if($db->num_rows($query) == 0 AND $db->num_rows($uquery) == 0) {
			$array['Error'] = "That package doesn't exist or cannot be approved! (Did they confirm their e-mail?)";
			$array['User PID'] = $id;
			$main->error($array);
			return;	
		}
		else {
			$data = $db->fetch_array($query);
			$query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
			$data2 = $db->fetch_array($query2);
			$server = $type->determineServer($data['pid']);
			if(!is_object($this->servers[$server])) {
				$this->servers[$server] = $this->createServer($data['pid']); # Create server class
			}
			if($this->servers[$server]->unsuspend($data2['user'], $server) == true) {
				$date = time();
				//$db->query("UPDATE `<PRE>user_packs` SET `status` = '1' WHERE `id` = '{$data['id']}'");
				/*$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Approved (Package ID $id)')");*/
				return true;
			}
			else {
				return false;	
			}
		}
	}
	
	/**
	 * Sets user's account to Active when the unique link is visited.
	 * @todo Not to be use right now
	 */
	public function confirm($username, $confirm) {
		return false; // not implemented yet
		global $db, $main, $type, $email;
		
		$query = $db->query("SELECT * FROM `<PRE>users` WHERE `user` = '{$username}'");
		// AND `status` = '".USER_STATUS_WAITING_ADMIN_VALIDATION."'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "The user doesn't exist";
			$main->error($array);
			return false;	
		} else {
			$data = $db->fetch_array($query);
			
			switch($data['status']) {
				case USER_STATUS_WAITING_ADMIN_VALIDATION:
					//$date = time();
					//$db->query("UPDATE `<PRE>users` SET `status` = '".USER_STATUS_ACTIVE."' WHERE `user` = '{$username}'");
					$user->updateUserStatus($data['id'], USER_STATUS_ACTIVE);					
					/*$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
														  '{$db->strip($data['userid'])}',
														  '{$data['user']}',
														  '{$date}',
														  'Account/E-mail Confirmed.')");*/
					return true;
				break;
				case USER_STATUS_ACTIVE:				
				return true;
				
				default:				
				return false;
			}
		}
	}
	
	public function	getAllServers() {
		global $db, $main;	
		$sql = "SELECT *  FROM `<PRE>servers`";
		$query = $db->query($sql);
		$server_list = array();
		if($db->num_rows($query) > 0) {
			$server_list = array();
			while ($row =  $db->fetch_array($query,'ASSOC')) {
				$server_list[$row['id']]=$row;
			}			
		}
		return $server_list;
	}
	
	public function getServerById($server_id) {
		global $db, $main;	
		$server_id = intval($server_id);
		$sql = "SELECT *  FROM `<PRE>servers` WHERE id = '$server_id'";
		$query = $db->query($sql);
		$data = array();
		if($db->num_rows($query) > 0) {			
			$data = $db->fetch_array($query,'ASSOC');			
		}
		return $data;
	}
}