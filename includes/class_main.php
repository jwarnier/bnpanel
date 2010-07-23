<?php/* For licensing terms, see /license.txt */
//Check if called by script
if(THT != 1){die();}class main {
	public $postvar = array(), $getvar = array(); # All post/get strings
	public function cleaninteger($var){ # Transforms an Integer Value (1/0) to a Friendly version (Yes/No)
	     $patterns[0] = '/0/';
         $patterns[1] = '/1/';
         $replacements[0] = 'No';
         $replacements[1] = 'Yes';
         return preg_replace($patterns, $replacements, $var);
	}
	public function cleanwip($var){ # Cleans v* from the version Number so we can work
	     if(preg_match('/v/', $var)) {
	     	$wip[0] = '/v/';
	     	$wipr[0] = '';
	     	$cleaned = preg_replace($wip, $wipr, $var);
	     	return $cleaned;
	     } else {	     	return $var; #Untouched
	     }	}
	public function error($array) {
		echo "<strong>ERROR<br /></strong>";
		foreach($array as $key => $data) {
			echo "<strong>". $key . ":</strong> ". $data ."<br />";
		}
		echo "<br />";
	}	
	public function redirect($url, $headers = 0, $long = 0) { # Redirects user, default headers
		if(!$headers) {
			header("Location: ". $url);	# Redirect with headers
		} else {
			echo '<meta http-equiv="REFRESH" content="'.$long.';url='.$url.'">'; # HTML Headers
		}
	}
		/**	 *  Shows error default, sets error if $error set	 */
	public function errors($error = 0) {		
		if(!$error) {
			if($_SESSION['errors']) {
				return $_SESSION['errors'];
			}
		} else {
			$_SESSION['errors'] = $error;
		}			}	
	public function table($header, $content = 0, $width = 0, $height = 0) { # Returns the HTML for a THT table
		global $style;
		if($width) {
			$props = "width:".$width.";";		}		
		if($height) {
			$props .= "height:".height.";";
		}
		$array['PROPS'] = $props;
		$array['HEADER'] = $header;
		$array['CONTENT'] = $content;
		$array['ID'] =rand(0,999999);
		$link = LINK."../themes/". THEME ."/tpl/table.tpl";
		if(file_exists($link)) {
			$tbl = $style->replaceVar("../themes/". THEME ."/tpl/table.tpl", $array);
		} else {
			$tbl = $style->replaceVar("tpl/table.tpl", $array);
		}
		return $tbl;
	}
	public function sub($left, $right) { # Returns the HTML for a THT table
		global $style;
		$array['LEFT'] = $left;
		$array['RIGHT'] = $right;
		$link = LINK."../themes/". THEME ."/tpl/sub.tpl";
		if(file_exists($link)) {
			$tbl = $style->replaceVar("../themes/". THEME ."/tpl/sub.tpl", $array);
		} else {
			$tbl = $style->replaceVar("tpl/sub.tpl", $array);
		}
		return $tbl;
	}
	public function evalreturn($code) { # Evals code and then returns it without showing
		ob_start();
		eval("?> " . $code . "<?php ");
		$data = ob_get_contents();
		ob_clean();
		return $data;
	}	
	public function done() { # Redirects the user to the right part
		global $main;
		foreach($main->getvar as $key => $value) {
			if($key != "do") {
				if($i) {
					$i = "&";
				} else {
					$i = "?";
				}
				$url .= $i . $key . "=" . $value;
			}		}
		$main->redirect($url);
	}	
	public function check_email($email) {
		if($this->validEmail($email)) {
			return true;
		} else {
			return false;
		}
	}
	/**	 * Creates an input	 * @param string	label	 * @param string	name	 * @param bool		true if the checkbox will be checked	 * @return string html	 * 	 */	public function createInput($label, $name, $value) {		$html = $label.' <input name="'.$name.'" value="'.$value.'"> <br/>';		return $html;	}		/**	 * Creates a checkbox	 * @param string	label	 * @param string	name	 * @param bool		true if the checkbox will be checked	 * @return string html	 * 	 */	public function createCheckbox($label, $name, $checked = false) {		if ($checked == true) {			$checked = 'checked="'.$checked.'"';		} else {			$checked = '';		}		if(empty($label)) {			$label = '';		} else {			$label = $label.':';		}		$html = $label.'<input type="checkbox" name="'.$name.'"  '.$checked.' > <br/>';		return $html;	}		/**	 * @todo Function deprecated use createSelect instead	 */
	public function dropDown($name, $values, $default = 0, $top = 1, $class = "", $parameter_list = array()) { # Returns HTML for a drop down menu with all values and selected		if($top) {			$extra = '';			foreach($parameter_list as $key=>$parameter) {				$extra .= $key.'="'.$parameter.'"';			}
			$html .= '<select name="'.$name.'" id="'.$name.'" class="'.$class.'" '.$extra.'>';
		}
		if($values) {
			foreach($values as $key => $value) {
				$html .= '<option value="'.$value[1].'"';
				if($default == $value[1]) {
					$html .= 'selected="selected"';				}
				$html .= '>'.$value[0].'</option>';
			}
		}
		if($top) {
			$html .= '</select>';
		}
		return $html;
	}		/**	 * New simpler version of the dropDown function	 * @param 	string	name of the select tag	 * @param	array	values with this structure array(1=>'Item 1', 2=>'Item 2')	 * @param 	array	extra information to add in the select i.e onclick, onBlur, etc	 * @param	bool	show or not a blank item	 * @return	html	returns the select html  	 */	public function createSelect($name, $values, $default = 0, $parameter_list = array(), $show_blank_item = true) {				$extra = '';		foreach($parameter_list as $key=>$parameter) {			$extra .= $key.'="'.$parameter.'"';		}		$html .= '<select name="'.$name.'" id="'.$name.'" '.$extra.'>';			if ($show_blank_item) {			$html .= '<option value="">-- Select --</option>';		}		if($values) {			foreach($values as $key => $value) {				$html .= '<option value="'.$key.'"';				if($default == $key) {					$html .= 'selected="selected"';				}				$html .= '>'.$value.'</option>';			}		}				$html .= '</select>';				return $html;	}		
	public function folderFiles($link) { # Returns the filenames of a content in a folder
		$folder = $link;
		if ($handle = opendir($folder)) { # Open the folder
			while (false !== ($file = readdir($handle))) { # Read the files
				if($file != "." && $file != ".." && $file != ".svn" && $file != "index.html") { # Check aren't these names
					$values[] = $file;
				}
			}
		}
		closedir($handle); #Close the folder
		return $values;
	}
	public function checkIP($ip) { # Returns boolean for ip. Checks if exists
		global $db;
		global $main;
		$query = $db->query("SELECT * FROM `<PRE>users` WHERE `ip` = '{$db->strip($ip)}'");
		if($db->num_rows($query) > 0) {
			return false;
		}else {
			return true;	
		}
	}
		/**	 * Checks the staff permissions for a nav item	 * @param	int		permission id	 * @param 	int		user id	 */	 
	public function checkPerms($id, $user = 0) {
		global $main, $db;
		if(!$user) {
			$user =  $main->getCurrentStaffId();
		}			//Use now session to avoid useless query calls to the DB		if (isset($_SESSION['user_permissions'])) {			foreach($_SESSION['user_permissions'] as $value) {				if($value == $id) {					return false;					}			}			return true;		} else {
			$query = $db->query("SELECT * FROM `<PRE>staff` WHERE `id` = '{$user}'");
			if($db->num_rows($query) == 0) {
				$array['Error'] = "Staff member not found";
				$array['Staff ID'] = $id;
				$main->error($array);
			} else {
				$data = $db->fetch_array($query);			
				$perms = explode(",", $data['perms']);											$_SESSION['user_permissions'] = $perms;				
				foreach($perms as $value) {
					if($value == $id) {
						return false;	
					}
				}
				return true;
			}		}
	}		/**	 * Checks the credentails of the client and logs in, returns true or false	 * 	 */
	public function clientLogin($username, $pass) {
		global $db, $main, $user;		$ip   = $_SERVER['REMOTE_ADDR'];			
		if(isset($user) && isset($pass)) {			$user_info	= $user->getUserByUserName($username);						if (is_array($user_info) && !empty($user_info)) {				if ($user_info['status'] == USER_STATUS_ACTIVE) {										if(md5(md5($pass).md5($user_info['salt'])) == $user_info['password']) {												$_SESSION['clogged'] 	= 1;											$data['password'] 		= null;						$data['salt'] 			= null;						//Save all user in this session						$_SESSION['cuser'] 		= $user_info;						$this->addLog("Login successful $ip - $username");																							return true;					} else { 						$main->errors('Incorrect password!');					}				} else {					$main->errors('Your account is not active');				}			} else {				$main->errors('User does not exist');			}		}				$this->addLog("Login failed ($ip) - $username");			return false;	}
	public function staffLogin($user, $pass) { # Checks the credentials of a staff member and returns true or false
		global $db, $main;		$ip = $_SERVER['REMOTE_ADDR'];		$date = time();		
		if($user && $pass) {
			$query = $db->query("SELECT * FROM `<PRE>staff` WHERE `user` = '{$main->postvar['user']}'");
			if($db->num_rows($query) == 0) {
				return false;
			} else {
				$data = $db->fetch_array($query);
				if(md5(md5($main->postvar['pass']) . md5($data['salt'])) == $data['password']) {
					$_SESSION['logged'] = 1;					$data['password'] 	= null;					$data['salt'] 		= null;					$_SESSION['user'] 	= $data;										$main->addLog("STAFF LOGIN SUCCESSFUL ($user, $ip)");					
					return true;
				} else {										$main->addLog("STAFF LOGIN FAILED ($user, $ip)");
					return false;
				}
			}
		} else {
			return false;
		}
	}
	
	public function laterMonth($num) { # Makes the date with num of months after current
		$day = date('d');
		$month = date('m');
		$year = date('Y');
		$endMonth = $month + $num;
		switch($endMonth) {
		case 1:
		$year++;
		break;
		case 2:
		{
		if ($day > 28)
		{
		// check if the year is leap
		$day = 28; // or you can keep the day and increase the month
		}
		}
		break;
		default:
		// nothing to do 
		break;
		}
		return mktime(0,0,0,$endMonth,$day,$year);
	}
	
	/**
	* Validate an email address.
	* Provide email address (raw input)
	* Returns true if the email address has the email 
	* address format and the domain exists.
	* Thank you, Linux Journal!
	* http://www.linuxjournal.com/article/9585
	*/
	public function validEmail($email) {
	   $isValid = true;
	   $atIndex = strrpos($email, "@");
	   if (is_bool($atIndex) && !$atIndex)   {
		  $isValid = false;
	   }   else   {
		  $domain = substr($email, $atIndex+1);
		  $local = substr($email, 0, $atIndex);
		  $localLen = strlen($local);
		  $domainLen = strlen($domain);
		  if ($localLen < 1 || $localLen > 64)
		  {
			 // local part length exceeded
			 $isValid = false;		  }
		  else if ($domainLen < 1 || $domainLen > 255)
		  {
			 // domain part length exceeded
			 $isValid = false;
		  }
		  else if ($local[0] == '.' || $local[$localLen-1] == '.')
		  {
			 // local part starts or ends with '.'
			 $isValid = false;
		  }
		  else if (preg_match('/\\.\\./', $local))
		  {
			 // local part has two consecutive dots
			 $isValid = false;
		  }
		  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		  {
			 // character not valid in domain part
			 $isValid = false;
		  }
		  else if (preg_match('/\\.\\./', $domain))
		  {
			 // domain part has two consecutive dots
			 $isValid = false;
		  }
		  else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
					 str_replace("\\\\","",$local)))  {
			 // character not valid in local part unless			 // local part is quoted
			 if (!preg_match('/^"(\\\\"|[^"])+"$/',
				 str_replace("\\\\","",$local)))
			 {
				$isValid = false;
			 }
		  }
		  if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
			 // domain not found in DNS
			 $isValid = false;
		  }
	   }
	   return $isValid;
	}
	
	/**	 * Only changes the system password not the Control Panel password	 * 
	 * A more or less centralized function for changing a client's
	 * password. This updates both the cPanel/WHM and THT password.
	 * Will return true ONLY on success. Any other returned value should
	 * be treated as a failure. If the return value happens to be a
	 * string, it is an error message.	 * @todo this function should be moved to the class_user.php file	 * 
	 */
	public function changeClientPassword($clientid, $newpass) {
		global $db, $user;
		//Making sure the $clientid is a reference to a valid id.		$user_info	=	$user->getUserById($clientid);		
		if (is_array($user_info) && !empty($user_info)) {			$user->edit($clientid, array('password'=>$newpass));			/*			mt_srand((int)microtime(true));			$salt = md5(mt_rand());			$password = md5(md5($newpass) . md5($salt));			$db->query("UPDATE `<PRE>users` SET `password` = '{$password}' WHERE `id` = '{$db->strip($clientid)}'");			$db->query("UPDATE `<PRE>users` SET `salt` = '{$salt}' WHERE `id` = '{$db->strip($clientid)}'");*/					} else {			return "That client does not exist.";		}		
		/*
		 * We're going to set the password in cPanel/WHM first. That way
		 * if the password is rejected for some reason, THT will not 
		 * desync.
		 */		 
		/*$command = $server->changePwd($clientid, $newpass);
		if($command !== true) {
			return $command;
		}*/
		
		/*
		 * Let's change THT's copy of the password. Might as well make a
		 * new salt while we're at it.
		 */
	
		
		//Let's wrap it all up.
		return true;
	}		/**	 * Generates a contry select	 * @param	string	code of the country	 * @return	string	html content	 */	public function	countrySelect($selected_item = '') {		//@todo this will be move into other place something like includes/library/text.lib.php		require_once 'text.php';		$selected_item = strtoupper($selected_item);		return $this->createSelect('country', $country, $selected_item);	}		// Order status	public function getOrderStatusList() {		return array(			ORDER_STATUS_ACTIVE						=> 'Active', 			ORDER_STATUS_WAITING_USER_VALIDATION 	=> 'Waiting user validation',						ORDER_STATUS_WAITING_ADMIN_VALIDATION	=> 'Waiting admin validation',			ORDER_STATUS_CANCELLED 					=> 'Cancelled',  			//ORDER_STATUS_WAITING_PAYMENT			=> 'Waiting payment', 			ORDER_STATUS_DELETED					=> 'Deleted', 			);	}		public function getInvoiceStatusList() {		return array(			INVOICE_STATUS_PAID				=> 'Paid', 			INVOICE_STATUS_CANCELLED		=> 'Cancelled',						INVOICE_STATUS_WAITING_PAYMENT	=> 'Pending', 			INVOICE_STATUS_DELETED			=> 'Deleted'			);	}		public function getUserStatusList() {		return array(			USER_STATUS_ACTIVE						=> 'Active', 			USER_STATUS_SUSPENDED 					=> 'Suspend', 			USER_STATUS_WAITING_ADMIN_VALIDATION	=> 'Waiting admin validation',  			//USER_STATUS_WAITING_PAYMENT				=> 'Waiting payment',  //should be remove only added for backward comptability			USER_STATUS_DELETED						=> 'Deleted', 			);	}		/**	 * Gets current user info 	 */	public function getCurrentUserInfo() {		if (isset($_SESSION['cuser']) && is_array($_SESSION['cuser'])) {			return $_SESSION['cuser'];		} else {			return false;		}	}		/**	 * Gets the curren user id 	 */	public function getCurrentUserId() {		if (isset($_SESSION['cuser']) && is_array($_SESSION['cuser'])) {			return intval($_SESSION['cuser']['id']);		} else {			return false;		}	}			/**	 * Gets current staff info 	 */	public function getCurrentStaffInfo() {		if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {			return $_SESSION['user'];		} else {			return false;		}	}		/**	 * Gets the curren staff id 	 */	public function getCurrentStaffId() {		if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {			return intval($_SESSION['user']['id']);		} else {			return false;		}	}		/**	 * Gets the admin menu 	 */	public function getAdminMenu() {				if (isset($_SESSION['admin_menu']) && count($_SESSION['admin_menu']) > 0 ) {			return $_SESSION['admin_menu'];					} else {			global $db;						$result = $db->query("SELECT * FROM `<PRE>acpnav`");			$menu_list = $db->store_result($result);			$new_menu_list = array();			foreach($menu_list as $menu) {				$new_menu_list[$menu['link']] = $menu;			}						$_SESSION['admin_menu'] = $new_menu_list;			return $menu_list;		}	}		/**	 * Check if the link exist in the admin menu	 * @param	string	link 	 * @return  bool	true if sucess		 */	public function linkAdminMenuExists($link) {		$list = $this->getAdminMenu();		if (isset($list[$link]) && $list[$link]['link'] == $link) {			return true;		} else {			//somebody is trying to hack			return false;		}	}		/**	 * Gets the list of subdomains by server id	 * @param	int		server id		 * @return	array	list of subdomains	 */	public function getSubDomainByServer($server_id) {		global $db;		$result = $db->query("SELECT id, subdomain FROM `<PRE>subdomains` WHERE `server` = '{$server_id}' ");		$array = array();		while($data = $db->fetch_array($result, 'ASSOC')) {			$array[$data['id']] = $data['subdomain'];		}			return $array;			}			function toDate($d, $format= 'Y-m-d') {				if (strtotime($d) === false || strtotime($d) === -1)			return $d;		return date($format, strtotime($d));	}		function getDateArray($date) {		return getdate(strtotime($date));	}		/**	 * Generates a random password	 */	public function generatePassword() {		for ($digit = 0; $digit < 5; $digit++) {			$r = rand(0,1);			$c = ($r==0)? rand(65,90) : rand(97,122);			$passwd .= chr($c);		}		return $passwd;	}		/**	 * Generates a random username	 */	public function generateUsername() {		$t = rand(5,8);		for ($digit = 0; $digit < $t; $digit++) {			$r = rand(0,1);			$c = ($r==0)? rand(65,90) : rand(97,122);			$user .= chr($c);		}		return $user;	}		/**	 * Adds a log	 * @param	string	message to save in the log	 */			public function addLog($message) {		global $db;				$date = time();		//Tries to save the log as a staff user				if($this->getCurrentStaffId() != false) {			$user_id   	= $this->getCurrentStaffId();			$user_name 	= $this->getCurrentStaffInfo();			$user_name 	= $user_name['user'];		} elseif ($this->getCurrentUserId() != false) {			$user_id	= $this->getCurrentUserId();			$user_name 	= $this->getCurrentUserInfo();			$user_name 	= $user_name['user'];		} elseif (CRON == 1) {			$user_id = '0';			$user_name = 'Cron';		} else {			$user_id = '0';			$user_name = 'Anonymous';		}				$db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(			'{$user_id}',			'{$user_name}',			'{$date}',			'{$message}')");	}		public function getToken () {		$token = md5(uniqid(rand(),TRUE));		$_SESSION['sec_token'] = $token;		return $token;	}				public function clearToken() {		$_SESSION['sec_token'] = null;		unset($_SESSION['sec_token']);	}			public function checkToken($clean_token = true) {		if (isset($this->postvar['_post_token'])) {			if (isset($_SESSION['sec_token']) && isset($this->postvar['_post_token']) && $_SESSION['sec_token'] === $this->postvar['_post_token']) {				if($clean_token)					$this->clearToken();				return true;			}		} elseif(isset($this->getvar['_get_token'])) {			if (isset($_SESSION['sec_token']) && isset($this->getvar['_get_token']) && $_SESSION['sec_token'] === $this->getvar['_get_token']) {				if ($clean_token)					$this->clearToken();				return true;			}								}		if ($clean_token)			$this->clearToken();		return false;	}}