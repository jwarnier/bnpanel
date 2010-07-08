<?php
/* For licensing terms, see /license.txt */
class server {
	
	private $servers = array(); # All the servers in a array
	
	# Start the Functions #
	private function createServer($package) { # Returns the server class for the desired package
		global $type, $main;
		$server = $type->determineServerType($type->determineServer($package)); # Determine server
		
		if($this->servers[$server]) {
			return true;	
		}
		//Abstract class Panel added
		require_once LINK."servers/panel.php";
		$link = LINK."servers/".$server.".php";
		if(!file_exists($link)) {
			$array['Error'] = "The server .php doesn't exist!";
			$array['Server ID'] = $server;
			$array['Path'] = $link;
			$main->error($array);
			return false;	
		} else {
			require_once $link; # Get the server
			$serverphp = new $server;
			return $serverphp;
		}
	}
	
	public function signup() { # Echos the result of signup for ajax
		global $main, $db, $type, $addon, $order, $package, $email,$user;
			
		//Check package details
		$package_id = intval($main->getvar['package']);
		$package_info = $package->getPackage($package_id);
		
		
		if (empty($package_info) || $package_info['is_disable'] == 1) {
			echo 'Package doesn\'t exist please contact the administrator';
			return;
		} 
				
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
					if ($ttlparts > 2)
					{
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
		if($main->getvar['domain'] == "sub") { # If Subdomain
			if(!$main->getvar['csub']) {
				echo "Please fill in the subdomain field!";
				return;
			}
			$main->getvar['fdom'] = $main->getvar['csub'].".".$main->getvar['csub2'];
		}
		
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
		
		// Creates the "paid" or "free" class 
		$package_type_class = $type->createType($package_info['type']);	
			
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
		//$done = $serverphp->signup($type->determineServer($package_id), $package_info['reseller']);
		
		$done = true;
		if($done == true) {
			// Did the signup pass?
			//Creating a new user
			$date 				= time();								
			$user_name 			= $main->getvar['username'];				
			$billing_cycle_id 	= $main->getvar['billing_id'];		

			$main->getvar['signup'] 	= $_SERVER['REMOTE_ADDR'];
			$main->getvar['ip'] 		= time();
			$main->getvar['salt'] 		= md5(rand(0,9999999));
			$main->getvar['password'] 	= md5(md5($main->getvar['password']).md5($main->getvar['salt']));
			$main->getvar['user'] 		= $main->getvar['username'];			
			$main->getvar['status'] 	= USER_STATUS_ACTIVE;
							  
			$user_id = $user->create($main->getvar);
													  
			$newSQL = "SELECT * FROM `<PRE>users` WHERE `user` = '{$user_name}' LIMIT 1;";
			$query = $db->query($newSQL);
						
			//If user added
			if($db->num_rows($query) == 1) {
				$data = $db->fetch_array($query);
				
				//Insert into logs
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
												  '{$data['userid']}',
												  '{$data['user']}',
												  '{$date}',
												  'User registered.')");												  
				
				//Creating a new order
				$order_id = $order->create($data['id'], $main->getvar['username'], $main->getvar['fdom'], $package_id, $date, ORDER_STATUS_WAITING_ADMIN_VALIDATION , $additional, $billing_cycle_id);
								
				//Add addons to a new order		
				$order->addAddons($order_id, $main->getvar['addon_ids']);
				
				$url = $db->config('url');
				$array['USER']	= $user_name;
				$array['PASS'] 	= $main->getvar['password']; 
				$array['EMAIL'] = $main->getvar['email'];
				$array['DOMAIN'] = $main->getvar['fdom'];
				$array['CONFIRM'] = $url . "client/confirm.php?u=" . $user_name . "&c=" . $date;
				
				//Get plan email friendly name				
				$array['PACKAGE'] = $package_info['name'];
				
				//Getting the order info
				$order_info = $order->getOrderByUser($data['id']);
				
				//Depends if the package needs an admin validation
				if($package_info['admin'] == 0) {
					//No admin validation no suspend of the webhosting
					$emaildata = $db->emailTemplate('newacc');
					echo "<strong>Your account has been completed!</strong><br />You may now use the client login bar to see your client area or proceed to your control panel. An email has been dispatched to the address on file.";
					if($type->determineType($package_id) == 'paid') {
						echo " This will apply only when you've made payment.";	
						$_SESSION['clogged'] = 1;
						$_SESSION['cuser'] = $data['id'];
					}
					$donecorrectly = true;
				} elseif($package_info['admin'] == 1) {
					//Needs admin validation so we suspend the webhosting -
					if($serverphp->suspend($main->getvar['username'], $type->determineServer($package_id)) == true) {						
						$emaildata = $db->emailTemplate('newaccadmin');
						$emaildata2 = $db->emailTemplate('adminval');
						$email->staff($emaildata2['subject'], $emaildata2['content']);
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
				echo "Your username doesn't exist in the system meaning the query failed or it exists more than once!";	
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
				$invoice->create($data['id'], $package_amount, $due, $notes, $addon_fee, INVOICE_STATUS_WAITING_PAYMENT);
				
				//4. Suspend the hosting if is not already suspended
				if ($package_info['admin'] == 0) { 				
					$serverphp->suspend($main->getvar['username'], $type->determineServer($package_id));
				}												
				echo '<div class="errors"><b>You are being redirected to payment! It will load in a couple of seconds..</b></div>';
			}
		}
	}
	
	public function terminate($id, $reason = false) { # Deletes a user account from the package ID
		global $db, $main, $type, $email;
		$query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist or cannot be terminated!";
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
				$emaildata = $db->emailTemplate("termacc");
				$array['REASON'] = "Admin termination.";
				$email->send($data2['email'], $emaildata['subject'], $emaildata['content'], $array);
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Terminated ($reason)')");
				//$db->query("DELETE FROM `<PRE>user_packs` WHERE `id` = '{$data['id']}'");
				//$db->query("DELETE FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
				$user->updateUserStatus($data['id'], USER_STATUS_DELETED);
				return true;
			}
			else {
				return false;	
			}
		}
	}
	
	public function cancel($id, $reason = false) { # Deletes a user account from the package ID
		global $db, $main, $type, $email, $user;
		$query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND `status` != '9'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist or cannot be cancelled! Are you trying to cancel an already cancelled account?";
			$array['User PID'] = $id;
			$main->error($array);
			return;	
		} else {
			$data 	= $db->fetch_array($query);
			$query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
			$data2 	= $db->fetch_array($query2);
			$server = $type->determineServer($data['pid']);
			if(!is_object($this->servers[$server])) {
				$this->servers[$server] = $this->createServer($data['pid']); # Create server class
			}
			if($this->servers[$server]->terminate($data2['user'], $server) == true) {
				$date = time();
				$emaildata = $db->emailTemplate("cancelacc");
				$array['REASON'] = "Account Cancelled.";
				$email->send($data2['email'], $emaildata['subject'], $emaildata['content'], $array);
				$user->updateUserStatus($data['id'], USER_STATUS_SUSPENDED);
				//$db->query("UPDATE `<PRE>user_packs` SET `status` = '9' WHERE `id` = '{$data['id']}'");
				//$db->query("UPDATE `<PRE>users` SET `status` = '9' WHERE `id` = '{$db->strip($data['userid'])}'");
				
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Cancelled  ($reason)')");
				return true;
			}
			else {
				return false;	
			}
		}
	}
	
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
	public function suspend($id, $reason = false) { # Suspends a user account from the package ID
		global $db, $main, $type, $email;
		//$query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND `status` = '1'");
		$query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist or cannot be suspended!";
			$array['User PID'] = $id;
			$main->error($array);
			return;	
		}
		else {
			$data = $db->fetch_array($query);
			$query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
			$data2 = $db->fetch_array($query2);
			$server = $type->determineServer($data['pid']);
			global $serverphp;
			if(!is_object($this->servers[$server]) && !$serverphp) {
				$this->servers[$server] = $this->createServer($data['pid']); # Create server class
				$donestuff = $this->servers[$server]->suspend($data2['user'], $server, $reason);
			}
			else {
				$donestuff = $serverphp->suspend($data2['user'], $server, $reason);
			}
			if($donestuff == true) {
				$date = time();
				$db->query("UPDATE `<PRE>user_packs` SET `status` = '2' WHERE `id` = '{$data['id']}'");
				$db->query("UPDATE `<PRE>users` SET `status` = '2' WHERE `id` = '{$db->strip($data['userid'])}'");
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Suspended ($reason)')");
				$emaildata = $db->emailTemplate("suspendacc");
				$email->send($data2['email'], $emaildata['subject'], $emaildata['content']);
				return true;
			}
			else {
				return false;	
			}
		}
	}
	
	public function unsuspend($id) { # Unsuspends a user account from the package ID
		global $db, $main, $type, $email;
		//$sql = "SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND (`status` = '2' OR `status` = '3' OR `status` = '4')";
		$sql = "SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' ";
		$query = $db->query($sql);
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist or cannot be unsuspended!";
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
				$db->query("UPDATE `<PRE>users` SET `status` = '1' WHERE `id` = '{$db->strip($data['userid'])}'");
				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
													  '{$db->strip($data['userid'])}',
													  '{$data2['user']}',
													  '{$date}',
													  'Unsuspended.')");
				$emaildata = $db->emailTemplate("unsusacc");
				$email->send($data2['email'], $emaildata['subject'], $emaildata['content']); 
				return true;
			}
			else {
				return false;	
			}
		}
	}
	
	
	public function changePwd($id, $newpwd) { # Changes user's password.
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
	
	public function confirm($username, $confirm) { # Set's user's account to Active when the unique link is visited.
		global $db, $main, $type, $email;
		$query = $db->query("SELECT * FROM `<PRE>users` WHERE `user` = '{$username}' AND `signup` = {$confirm} AND `status` = '3'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist or cannot be confirmed!";
			$main->error($array);
			return false;	
		}
		else {
			$data = $db->fetch_array($query);
			$date = time();
			$db->query("UPDATE `<PRE>users` SET `status` = '1' WHERE `user` = '{$username}'");
			$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
												  '{$db->strip($data['userid'])}',
												  '{$data['user']}',
												  '{$date}',
												  'Account/E-mail Confirmed.')");
			return true;
		}
	}
}
?>
