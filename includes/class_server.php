<?php
/* For licensing terms, see /license.txt */

/**
 * This class is the interface between the Billing System and the Control Panel (ISPConfig, Cpanel, Direct Admin)
 * It cancel, suspend the orders.
 * 
 */
class server {
	
	private $servers = array(); # All the servers in a array
	
	/**
	 * Loads the server class (isoconfig, direct admin,  cpanel)
	 */
	private function createServer($package_id) { # Returns the server class for the desired package
		global $type, $main;		
		$server_type = $type->determineServerType($type->determineServer($package_id)); # Determine server		
		if($this->servers[$server_type]) {
			return true;	
		}
		
		//Abstract class Panel added
		require_once LINK."servers/panel.php";
		$link = LINK."servers/".$server_type.".php";
		if(!file_exists($link)) {
			$array['Error'] = "The server .php doesn't exist!";
			$array['Server ID'] = $server_type;
			$array['Path'] = $link;
			$main->error($array);
			return false;	
		} else {
			require_once $link; # Get the server
			$serverphp = new $server_type();
			return $serverphp;
		}
	}
	
	/**
	 * Creates a user account, order and invoice
	 */
	public function signup() { # Echos the result of signup for ajax
		global $main, $db, $type, $addon, $order, $package, $email,$user;
			
		//Check package details
		$package_id 	= intval($main->getvar['package']);
		$package_info 	= $package->getPackage($package_id);		
		
		if (empty($package_info) || $package_info['is_disable'] == 1) {
			echo 'Package doesn\'t exist please contact the administrator';
			return;
		} 
		$user_id = '';
	
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
			$main->getvar['fdom'] = $main->getvar['cdom'];
		}
		
		if($main->getvar['domain'] == 'sub') { # If Subdomain
			if(!$main->getvar['csub']) {
				echo "Please fill in the subdomain field!";
				return;
			}			
			$subdomain_list = $main->getSubDomainByServer($package_info['server']);			
			$subdomain = $subdomain_list[$main->getvar['csub2']];			
			$main->getvar['fdom'] = $main->getvar['csub'].".".$subdomain;
		}
				
		$user_already_registered = false;
		
		//Check if the client is already logged in to ask for user information
		if ($main->getCurrentUserId() === false) {
			
			if((!$main->getvar['username'])) {
				echo "Please enter a username!";
				return;
			} else {
				$query = $db->query("SELECT * FROM `<PRE>users` WHERE `user` = '{$main->getvar['username']}'");
				if($db->num_rows($query) != 0) {
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
				$query = $db->query("SELECT * FROM `<PRE>users` WHERE `email` = '{$main->getvar['email']}'");
				if($db->num_rows($query) != 0) {
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
			//The user is already log in we load user information from the DB
			$user_already_registered = true;
			$user_id 	= $main->getCurrentUserId();
			$user_info 	= $main->getCurrentUserInfo();			
			$user_name 	= $user_info['user'];			
			$user_email = $user_info['email'];			
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
		
		foreach($main->getvar as $key => $value) {
			$data = explode("_", $key);
			if($data[0] == "type") {
				if($n) {
					$additional .= ",";	
				}
				$additional .= $data[1]."=".$value;
				$n++;
			}
		}
		
		//useless right now
		//$main->getvar['fplan'] = $package_info['backend'];
		$serverphp = $this->createServer($package_id); # Create server class
		
		//Registering to the server
		
		
		$done = true;
		if($done == true) {
			// Did the signup pass?
			$date 				= time();
			$billing_cycle_id 	= $main->getvar['billing_id'];
						
			if($user_already_registered == false) {
				//Creating a new user												
				$user_name 					= $main->getvar['username'];	
				$main->getvar['signup'] 	= $_SERVER['REMOTE_ADDR'];
				$main->getvar['ip'] 		= time();				
				$main->getvar['user'] 		= $main->getvar['username'];			
				$main->getvar['status'] 	= USER_STATUS_ACTIVE; 
				//Create a new user
				$user_id 					= $user->create($main->getvar);
				
				//If user is created
				if (!empty($user_id) && is_numeric($user_id)){
					$user_already_registered = true;										
					$login = $main->clientLogin($user_name, $main->getvar['password']);
				}
								
				$password					= $main->getvar['password']; 
				$user_email					= $main->getvar['email'];
								
				//@todo create a new class or function or whatever to avoid calling this insert log 
				/* Replace this thing with some cool class_log.php or something like that
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
												  '{$data['userid']}',
												  '{$data['user']}',
												  '{$date}',
												  'User registered.')");*/												  
			} 
				
			if ($user_already_registered == true) {
				
				//Creating a new order because the user is already registered
				$params['userid'] 		= $user_id;				
								
				$params['domain'] 		= $main->getvar['fdom'];
				$params['pid'] 			= $package_id;
				$params['signup'] 		= $date;
				
				//Change the order status it depends on the package
				if ($package_info['admin'] == 1) {
					$params['status'] 		= ORDER_STATUS_WAITING_ADMIN_VALIDATION;
				} else {
					$params['status'] 		= ORDER_STATUS_ACTIVE;
				}
				
				$params['additional']		= $additional;
				$params['billing_cycle_id'] = $billing_cycle_id;
				
				if (!empty($params['userid']) && !empty($params['pid'])) {
					//Creating a order		
						
					$order_id = $order->create($params);
					//Add addons to a new order		
					$order->addAddons($order_id, $main->getvar['addon_ids']);
					
					//$done = $serverphp->signup($type->determineServer($package_id), $package_info['reseller']);
					$done = $serverphp->signup($order_id);
				}
				
				$array['USER']		= $user_name;				
				$array['PASS'] 		= $password; 
				$array['EMAIL'] 	= $user_email;
				$array['DOMAIN'] 	= $main->getvar['fdom'];	
				
				//We avoid the user confirmation for the moment
				if ($user_already_registered == true && $user_info['status'] == USER_STATUS_ACTIVE) {			
					$array['CONFIRM'] 	= '';
				} else {
				//	$array['CONFIRM'] 	= '<span style="font-weight: bold;">Confirmation Link: </span>'.$db->config('url') . "client/confirm.php?u=" . $user_name . "&c=" . $date;	
					$array['CONFIRM'] 	= '';
				}
				
				$array['PACKAGE'] 	= $package_info['name'];
								
				//Depends if the package needs an admin validation
				if($package_info['admin'] == 0) {
					//New hosting account just waiting for *user* validation
					$emaildata = $db->emailTemplate('newacc');
					echo "<strong>Your account has been completed!</strong><br />You may now use the client login bar to see your client area or proceed to your control panel. An email has been dispatched to confirm you email address";
					if($type->determineType($package_id) == 'paid') {
						echo " This will apply only when you've made payment.";	
								
						//$_SESSION['clogged'] = 1;
						//$_SESSION['cuser'] = $user_id;
					}
					$donecorrectly = true;
				} elseif($package_info['admin'] == 1) {
					//Needs admin validation so we suspend the webhosting -
					if($serverphp->suspend($order_id, $type->determineServer($package_id)) == true) {
						
						//User is waiting for admin validation						
						$emaildata 	= $db->emailTemplate('newaccadmin');
						
						//Email sent to all admins 
						$email_to_admin = $db->emailTemplate('adminval');
						$email->staff($email_to_admin['subject'], $email_to_admin['content']);
						
						echo "<strong>Your account is awaiting admin validation!</strong><br />An email has been dispatched to the address on file. You will recieve another email when the admin has overlooked your account.";
						$donecorrectly = true;
					} else {
						echo "Something with admin validation went wrong (suspend). Your account should be running but contact your host administrator!";	
					}
				} else {				
					echo "Something with admin validation went wrong. Your account should be running but contact your host administrator.";	
				}
				$email->send($array['EMAIL'], $emaildata['subject'], $emaildata['content'], $array);
			} else {
				echo "There was a problem when creating a user. Please contact the system administrator.";	
			}
			
			//If the package is paid			
			if($donecorrectly && $type->determineType($package_id) == 'paid') {							
				global $invoice,$package;
				//The order was saved with an status of admin validation now we should create an invoice an set the status to wait payment 
			
				//$due 		= time()+intval($db->config('suspensiondays')*24*60*60);
				$due 		= time();
				$notes = '';
				
				//1. Calculating the amount for the package depending on the billing cycle
				$package_amount = 0;
				$package_billing_info = $package->getPackageByBillingCycle($package_id, $billing_cycle_id);	
				
				if (is_array($package_billing_info) && isset($package_billing_info['amount'])) {					
					$package_amount = $package_billing_info['amount'];
				}				
				//2. Generating the addon serialized array
				$addon_fee = $addon->generateAddonFee($main->getvar['addon_ids'], $billing_cycle_id, true);
								
				//3. Creating the invoice
				$invoice_id = $invoice->create($user_id, $package_amount, $due, $notes, $addon_fee, INVOICE_STATUS_WAITING_PAYMENT, $order_id);
				
				//This variable will be read by the Ajax::ispaid function
				$_SESSION['last_invoice_id'] = $invoice_id;
				
				//4. Suspend the hosting if is not already suspended
				if ($package_info['admin'] == 0) { 				
					$serverphp->suspend($order_id, $type->determineServer($package_id));
				}												
				echo '<div class="errors"><b>You are being redirected to payment! It will load in a couple of seconds..</b></div>';
			}
		}
	}
	
	
	/**
	 * 	Deletes a user account from the Control Panel System (ISPConfig, Cpanel)
	 *  Not recomended to use
	 */	 
	public function terminate($order_id, $reason = false) { # Deletes a user account from the package ID
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
				$emaildata = $db->emailTemplate("termacc");
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
		global $db, $main, $type, $email, $user, $order;
		$order_info = $order->getOrderInfo($order_id);
		if (is_array($order_info) && !empty($order_info)) {
			$user_info = $user->getUserById($order_info['userid']);
			$server = $type->determineServer($order_info['pid']);
			if(!is_object($this->servers[$server])) {
				$this->servers[$server] = $this->createServer($order_info['pid']); # Create server class
			}
			//Suspending the website of that user
			if($this->servers[$server]->suspend($order_id, $server) == true) {
				
				$emaildata = $db->emailTemplate("cancelacc");
				$array['REASON'] = "Account Cancelled.";
				$email->send($user_info['email'], $emaildata['subject'], $emaildata['content'], $array);
				$order->updateOrderStatus($order_id, ORDER_STATUS_CANCELLED);
				
				//$user->updateUserStatus($user_info['id'], USER_STATUS_SUSPENDED);
				//$db->query("UPDATE `<PRE>user_packs` SET `status` = '9' WHERE `id` = '{$data['id']}'");
				//$db->query("UPDATE `<PRE>users` SET `status` = '9' WHERE `id` = '{$db->strip($data['userid'])}'");
				/*
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Cancelled  ($reason)')");*/
				return true;
			} else {
				return false;	
			}
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
		global $db, $main, $type, $email;
		$query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND `status` != '9'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist or cannot be cancelled! Are you trying to cancel an already cancelled account?";
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
			if($this->servers[$server]->terminate($data2['user'], $server) == true) {
				$date = time();
				$emaildata = $db->emailTemplate("cancelacc");
				$array['REASON'] = "Account Declined.";
				$email->send($data2['email'], $emaildata['subject'], $emaildata['content'], $array);
				$db->query("UPDATE `<PRE>user_packs` SET `status` = '9' WHERE `id` = '{$data['id']}'");
				$db->query("UPDATE `<PRE>users` SET `status` = '9' WHERE `id` = '{$db->strip($data['userid'])}'");
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Declined  (Package ID $id)')");
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
		global $db, $main, $type, $email, $serverphp, $order,$user;
		$order_info = $order->getOrderInfo($order_id);
		
		if (is_array($order_info) && !empty($order_info)) {
			$user_info = $user->getUserById($order_info['userid']);
			$server_id = $type->determineServer($order_info['pid']);
			
			
			if(!is_object($this->servers[$server_id]) && !$serverphp) {
				$this->servers[$server_id] = $this->createServer($order_info['pid']); # Create server class
				$donestuff = $this->servers[$server_id]->suspend($order_id, $server_id, $reason);
			} else {
				$donestuff = $serverphp->suspend($order_id, $server_id, $reason);
			}
			
			if($donestuff == true) {
				$order->updateOrderStatus($order_id, ORDER_STATUS_CANCELLED);

				//$db->query("UPDATE `<PRE>users` SET `status` = '2' WHERE `id` = '{$db->strip($data['userid'])}'");
				/*
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Suspended ($reason)')");*/
				$emaildata = $db->emailTemplate('suspendacc');
				$email->send($user_info['email'], $emaildata['subject'], $emaildata['content']);
				return true;
			} else {
				return false;	
			}
			
		} else {
			$array['Error'] = "That order doesn't exist or cannot be suspended!";
			$array['User PID'] = $order_id;
			$main->error($array);
			return;			
		}
	}
	
	/**
	 * Unsuspend an Order
	 * @param	int		order id 
	 */
	public function unsuspend($order_id) { # Unsuspends a user account from the package ID
		global $db, $main, $type, $email, $order, $user;
		
		$order_info = $order->getOrderInfo($order_id);
		if (is_array($order_info) && !empty($order_info)) {
			$user_info = $user->getUserById($order_info['userid']);			
			$server_id = $type->determineServer($order_info['pid']);
			
			if(!is_object($this->servers[$server_id])) {
				$this->servers[$server_id] = $this->createServer($order_info['pid']); # Create server class
			}
			if($this->servers[$server_id]->unsuspend($order_id, $server_id) == true) {
			//	$date = time();
				$order->updateOrderStatus($order_id, ORDER_STATUS_ACTIVE);
				
				//$db->query("UPDATE `<PRE>users` SET `status` = '1' WHERE `id` = '{$db->strip($data['userid'])}'");
				/*$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Unsuspended.')");*/
				$emaildata = $db->emailTemplate('unsusacc');
				$email->send($user_info['email'], $emaildata['subject'], $emaildata['content']); 
				return true;
			} else {
				return false;	
			}		
		} else {
			$array['Error'] = "That package doesn't exist or cannot be unsuspended!";
			$array['User PID'] = $order_id;
			$main->error($array);
			return;
		}
	}
	
	/**
	 * Changes user/client password
	 * @param	int		user id
	 * @param	string	new password
	 * @todo	this function should be deprecated
	 */
	public function changePwd($user_id, $newpwd) { # Changes user's password.
		global $db, $main, $type, $email;
		$query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist!";
			$array['User PID'] = $id;
			$main->error($array);
			return;
		} else {
			$data = $db->fetch_array($query);
			$query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
			$data2 = $db->fetch_array($query2);
			$server = $type->determineServer($data['pid']);
			global $serverphp;
			if(!is_object($this->servers[$server]) && !$serverphp) {
				$this->servers[$server] = $this->createServer($data['pid']); # Create server class
				$donestuff = $this->servers[$server]->changePwd($data2['user'], $newpwd, $server);
			}
			else {
				$donestuff = $serverphp->changePwd($data2['user'], $newpwd, $server);
			}
			if($donestuff == true) {
				$date = time();
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Control Panel password updated.')");
				return true;
			}
			else {
				return false;	
			}
		}
	}
	
	/**
	 * Changes the order password
	 * @param	int		order id
	 * @param	string	new password
	 */
	public function changeOrderPassword($order_id, $new_password) {
		global $order;
		$order_info = $order->getOrderInfo($order_id);		
		$server_id  = $type->determineServer($order_info['pid']);
		
		if(!is_object($this->servers[$server_id])) {
			$this->servers[$server_id] = $this->createServer($order_info['pid']); # Create server class
		}
		if($this->servers[$server_id]->changePwd($order_info['username'], $new_password, $server_id) == true) {
			$order->edit($order_id,array('password'=>$new_password));
		}
	}
	
	/**
	 * Approves a user account
	 * @todo Not to be use right now
	 ***/
	
	public function approve($id) { # Approves a user's account (Admin Validation).
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
				$db->query("UPDATE `<PRE>user_packs` SET `status` = '1' WHERE `id` = '{$data['id']}'");
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Approved (Package ID $id)')");
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
					
					$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
														  '{$db->strip($data['userid'])}',
														  '{$data['user']}',
														  '{$date}',
														  'Account/E-mail Confirmed.')");
					return true;
				break;
				case USER_STATUS_ACTIVE:				
				return true;
				
				default:				
				return false;
			}
		}
	}
	
}
