<?php
/* For licensing terms, see /license.txt */
/**
 * This class respond the AJAX calls
 */
 
$is_ajax_load = true;
require 'compiler.php';

class AJAX {
	
	/**
	 * 
	 * AJAX Calls used during installation  
	 * 
	 */
	
	public function sqlcheck() {
		global $main;
		//This call only will work if the conf.inc.php is writable, this means that the installation is not protected yet
		$link = LINK."conf.inc.php";		
		if (file_exists($link) && is_writable($link)) {
			if (INSTALL != 1) {
				$host 	= $_GET['host'];
				$user 	= $_GET['user'];
				$pass 	= $_GET['pass'];
				$db 	= $_GET['db'];
				$pre 	= $_GET['pre'];
				//die($_SERVER['REQUEST_URI']);
				$con = @mysql_connect($host, $user, $pass);
				if(!$con) {
					echo 0;	
				} else {
					$seldb = mysql_select_db($db, $con);
					if(!$seldb) {
						echo 1;	
					} else {
						if ($this->writeconfig($host, $user, $pass, $db, $pre, "false")) {
							echo 2;	
						} else {
							echo 3;	
						}
					}
				}
			} else {
				echo 4;	
			}
		}
	}
	
	private function writeconfig($host, $user, $pass, $db, $pre, $true, $upgrade = 'false') {
		global $style;		
		$link = LINK."conf.inc.php";		
		if (file_exists($link) && is_writable($link)) {			
			$array['HOST'] 		=  $host;
			$array['USER'] 		=  $user;
			$array['PASS'] 		=  $pass;
			$array['DB'] 		=  $db;
			$array['PRE'] 		=  $pre;
			$array['TRUE'] 		=  $true;		
			$array['UPGRADE'] 	=  $upgrade;		
			
			$tpl = $style->replaceVar("tpl/install/conftemp.tpl", $array);					
			file_put_contents($link, $tpl);
			return true;
		} else {
			return false;
		}
	}
	
	public function install() {
		global $db, $main;
		$conf_file = LINK."conf.inc.php";
		
		if (file_exists($conf_file) && is_writable($conf_file)) {
			include $conf_file;
			$dbCon = mysql_connect($sql['host'], $sql['user'], $sql['pass']);
			$dbSel = mysql_select_db($sql['db'], $dbCon);
			
			if ($_GET['type'] == "install") {
				$errors = $this->installsql("sql/install.sql", $sql['pre'], $dbCon);
				echo "Complete!<br /><strong>There were ".$errors['n']." errors while executing the SQL!</strong><br />";
				echo '<div align="center"><input type="button" name="button4" id="button4" value="Next Step" onclick="change()" /></div>';					
			} elseif($_GET['type'] == "upgrade") {									
				if ($sql['upgrade'] == true) {
					$errors = $this->installsql("sql/upgrade.sql", $sql['pre'], $dbCon);
					echo "Complete!<br /><strong>There were ".$errors['n']." errors while executing the SQL!</strong><br />";
					echo '<div class="errors">Your upgrade is now complete.</div>';
				} else {
					echo 'Change the upgrade variable to true in conf.inc.php. Then try again.';					
				}				
			} else {
				echo "Fatal Error Debug";
			}			
			if (!$this->writeconfig($sql['host'], $sql['user'], $sql['pass'], $sql['db'], $sql['pre'], "true")) {
				echo '<div class="errors">There was a problem re-writing to the config!</div>';	
			}					
			if ($errors['n']) {
				echo "<strong>SQL Queries (Broke):</strong><br />";
				foreach($errors['errors'] as $value) {
					echo $value."<br />";	
				}
			}
		}
	}
	
	/**
	 * @todo this function should be moved to a includes/install.lib.php file 
	 */
	private function installsql($data, $pre, $con = 0) {
		global $main, $style, $db;
		
		$conf_file = LINK."conf.inc.php";		
		if (file_exists($conf_file) && is_writable($conf_file)) {
			
			$array['PRE'] = $pre;
			$array['API-KEY'] = hash('sha512', $main->randomString());
			$sContents = $style->replaceVar($data, $array);
			
			// replace slash quotes so they don't get in the way during parse
			// tried a replacement array for this but it didn't work
			// what's a couple extra lines of code, anyway?
			$sDoubleSlash   = '~~DOUBLE_SLASH~~';
			$sSlashQuote    = '~~SLASH_QUOTE~~';
			$sSlashSQuote   = '~~SLASH_SQUOTE~~';
			
			$sContents = str_replace('\\\\', $sDoubleSlash,  $sContents);
			$sContents = str_replace('\"', $sSlashQuote,  $sContents);
			$sContents = str_replace("\'", $sSlashSQuote, $sContents);
			
			$iContents = strlen($sContents);
			$sDefaultDelimiter = ';';
			
			$aSql = array();
			$sSql = '';
			$bInQuote   = false;
			$sDelimiter = $sDefaultDelimiter;
			$iDelimiter = strlen($sDelimiter);
			$aQuote = array("'", '"');
			
			for ($i = 0;  $i < $iContents;  $i++) {
				if ($sContents[$i] == "\n"
				||  $sContents[$i] == "\r") {
					// Check for Delimiter Statement
					if (preg_match('/delimiter\s+(.+)/i', $sSql, $aMatches)) {
							$sDelimiter = $aMatches[1];
							$iDelimiter = strlen($sDelimiter);
							$sSql = '';
							continue;
					}
				}
			
				if (in_array($sContents[$i], $aQuote)) {
					$bInQuote = !$bInQuote;
					if ($bInQuote) {
							$aQuote = array($sContents[$i]);
					} else {
							$aQuote = array("'", '"');
					}
				}
			
				if ($bInQuote) {
					$sSql .= $sContents[$i];
				} else {
					// fill a var with the potential delimiter - aka read-ahead
					if(substr($sContents, $i, $iDelimiter) == $sDelimiter) {
							// Clear Comments
							$sSql = preg_replace("/^(-{2,}.+)/", '', $sSql);
							$sSql = preg_replace("/(?:\r|\n)(-{2,}.+)/", '', $sSql);
			
							// Put quotes back where you found them
							$sSql = str_replace($sDoubleSlash, '\\\\',  $sSql);
							$sSql = str_replace($sSlashQuote,  '\\"',   $sSql);
							$sSql = str_replace($sSlashSQuote, "\\'",   $sSql);
			
							// FIXME: odd replacement issue, just fix it for now and move on
							$sSql = str_replace('IFEXISTS`', 'IF EXISTS `', $sSql);
			
							$aSql[] = $sSql;
							$sSql = '';
			
							// pass delimiter
							$i += $iDelimiter;
					} else {
							$sSql .= $sContents[$i];
					}
				}
			}
			
			$aSql = array_map('trim', $aSql);
			$aSql = array_filter($aSql);
			
			$n = 0;
			foreach($aSql as $sSql) {
				if($con) {
					$query = mysql_query($sSql, $con);
				}
				else {
					$query = $db->query($sSql);	
				}
				if(!$query) {
					$n++;
					$errors[] = $sSql;
				}
			}
			if(!$n) {
				$n = 0;	
			}
			$stuff['n'] = $n;
			$stuff['errors'] = $errors;
			return $stuff;
		}
	}
	
	public function installfinal() {
		global $db, $main;
		$conf_file = LINK."conf.inc.php";		
		if (file_exists($conf_file) && is_writable($conf_file)) {			
			$query = $db->query("SELECT * FROM <PRE>staff");			
			if($db->num_rows($query) == 0) {
				foreach($main->getvar as $key => $value) {
					if(!$value) {
						$n++;	
					}
				}
				if ($main->checkToken(false)) {
					if(!$n) {				
						$db->updateConfig('url', 		$main->getvar['url']);
						$db->updateConfig('name', 		$main->getvar['site_name']);
						$db->updateConfig('emailfrom', 	$main->getvar['site_email']);
						
						$salt = md5(rand(0,99999));
						$password = md5(md5($main->getvar['pass']).md5($salt));
						$main->getvar['user']	=	$db->strip($main->getvar['user']);
						$main->getvar['email'] 	=	$db->strip($main->getvar['email']);
						$main->getvar['name'] 	=	$db->strip($main->getvar['name']);
						 
						$db->query("INSERT INTO <PRE>staff (user, email, password, salt, name) VALUES(
								  '{$main->getvar['user']}',
								  '{$main->getvar['email']}',
								  '{$password}',
								  '{$salt}',
								  '{$main->getvar['name']}')");
						echo 1;
					} else {
						echo 0;	
					}
				}
			}
		}
	}
	
	
	/**
	 * 
	 * AJAX Calls
	 * 
	 */	
	
	public function userIsLogged() {
		global $main;		
		if(!$main->getCurrentUserId()) {
			echo "0";
		} else {
			echo "1";
		}
	}
	
	public function acpPadd() {
		global $type;
		global $main;
		echo $type->acpPadd($main->getvar['type']);
	}
		
	/**
	 * Checks the user
	 */
	public function usercheck() {
		global $main, $db, $user;
		if (!$user->validateUserName($main->getvar['user'])) {
			echo 0;
			return;
		}
		if(!$main->getvar['user']) {
			$_SESSION['check']['user'] = false;
		   echo 0;
		} else {
			$user_info = $user->getUserByUserName($main->getvar['user']);
			if ($user_info == false) {
				$_SESSION['check']['user'] = true;
				echo 1;	
			} else {
				$_SESSION['check']['user'] = false;
				echo 0;	
			}
		}
	}
	
	public function passcheck() {
		global $main, $user;
		if ($main->getvar['pass'] == ":") {
			$_SESSION['check']['pass'] = false;
		   echo 0;
		   return;
		} else {
			$pass = explode(":", $main->getvar['pass']);
			if($pass[0] == $pass[1]) {
				if ($user->validatePassword($pass[0])) {
					$_SESSION['check']['pass'] = true;
					echo 1;
				} else {
					echo 0;
				}	
			}
			else {
				$_SESSION['check']['pass'] = false;
				echo 0;	
			}
		}
	}
	
	public function emailcheck() {
		global $main, $db, $user;
		if(!$main->getvar['email']) {
		   $_SESSION['check']['email'] = false;		   
		   echo 0;
		   return;
		}
		$user_info = $user->getUserByEmail($main->getvar['email']);
		
		if(!empty($user_info)) {			
		   $_SESSION['check']['email'] = false;
		   echo 0;
		   return;
		} else {
			if($main->check_email($main->getvar['email'])) {
				$_SESSION['check']['email'] = true;
				echo 1;
			} else {
				$_SESSION['check']['email'] = false;
				echo 0;
			}
		}
	}

	public function firstnamecheck() {
		global $main;
		if(!preg_match("/^([a-zA-Z\.\'\ \-])+$/",$main->getvar['firstname'])) {
			$_SESSION['check']['firstname'] = false;
			echo 0;
		}
		else {
			$_SESSION['check']['firstname'] = true;
			echo 1;
		}
	}
	
	public function lastnamecheck() {
		global $main;
		if(!preg_match("/^([a-zA-Z\.\'\ \-])+$/",$main->getvar['lastname'])) {
			$_SESSION['check']['lastname'] = false;
			echo 0;
		} else {
			$_SESSION['check']['lastname'] = true;
			echo 1;
		}
	}
	
	public function addresscheck() {
		global $main;
		if(!preg_match("/^([0-9a-zA-Z\.\ \-])+$/",$main->getvar['address'])) {
			$_SESSION['check']['address'] = false;
			echo 0;
		} else {
			$_SESSION['check']['address'] = true;
			echo 1;
		}
	}
	
	public function citycheck() {
		global $main;
		if (!preg_match("/^([a-zA-Z ])+$/",$main->getvar['city'])) {
			$_SESSION['check']['city'] = false;
			echo 0;			
		}
		else {
			$_SESSION['check']['city'] = true;
			echo 1;
		}
	}		
	
	public function statecheck() {
		global $main;
		if (!preg_match("/^([a-zA-Z\.\ -])+$/",$main->getvar['state'])) {
			$_SESSION['check']['state'] = false;
			echo 0;
		} else {
			$_SESSION['check']['state'] = true;
			echo 1;
		}
	}				
	
	public function zipcheck() {
		global $main;
		if(strlen($main->getvar['zip']) > 7) {
			echo 0;
			return;
		} else {
			if (!preg_match("/^([0-9a-zA-Z\ \-])+$/",$main->getvar['zip'])) {
				$_SESSION['check']['zip'] = false;
				echo 0;
			}
			else {
				$_SESSION['check']['zip'] = true;
				echo 1;
				}
			}
	}
	
	public function phonecheck() {
		global $main;
		if(strlen($main->getvar['phone']) > 15) {
			echo 0;
			return;
		} else {
			if (!preg_match("/^([0-9\-])+$/",$main->getvar['phone'])) {
				$_SESSION['check']['phone'] = false;
				echo 0;
			} else {
				$_SESSION['check']['phone'] = true;
				echo 1;
				}
			}
	}	
	//Basic captcha check... thanks http://frikk.tk!
	public function humancheck() {
		global $main;
		if($main->getvar['human'] != $_SESSION['pass']) {
			$_SESSION['check']['human'] = false;
			echo 0;			
		} else {
			$_SESSION['check']['human'] = true;
			echo 1;			
		}
	}
	
	public function clientcheck() {
		if($_SESSION['check']['email'] == true && $_SESSION['check']['user'] == true && $_SESSION['check']['pass'] == true && $_SESSION['check']['human'] == true && $_SESSION['check']['address'] == true && $_SESSION['check']['state'] == true && $_SESSION['check']['zip'] == true && $_SESSION['check']['phone'] == true) {
			echo 1;	
		} else {
			echo 0;	
		}
	}
	
	public function domaincheck() {
		global $main;
		if(!$main->getvar['domain']) {
		   echo 0;
		} else {
			$data = explode(".", $main->getvar['domain']);
			if(!$data[0] || !$data[1]) {
				echo 0;	
			} else {
				echo 1;	
			}
		}
	}
	
	/**
	 * Creates an Order to the system
	 * This function is called in the Order form when all steps are finished.
	 */
	 
	public function create() { 
		global $server;
		$server->signup();
	}
	
	public function orderForm() {
		global $type, $main;
		$ptype = $type->determineType($main->getvar['package']);		
		echo $type->orderForm($ptype);
	}
	
	public function cancelacc() { 
		/*
		global $db, $main, $type, $server, $email;
		$user = $main->getvar['user'];
		$pass = $main->getvar['pass'];
		$query = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($user)}'");
		if($db->num_rows($query) == 0) {
			echo "That account doesn't exist!";	
		}
		else {
			$data = $db->fetch_array($query);
			if(md5(md5($pass) . md5($data['salt'])) == $data['password']) {
				$query2 = $db->query("SELECT * FROM `<PRE>orders` WHERE `userid` = '{$db->strip($user)}'");
				$data2 = $db->fetch_array($query2);
				if($server->cancel($data2['id'])) {
					echo "Your account has been cancelled successfully!";
					session_destroy();
				}
				else {
					echo "Your account wasn't cancelled! Try again..";	
				}
			}
			else {
				echo "That password is wrong!";	
			}
		}
		*/
	}
	
	public function template() {
		global $main, $db, $style;
		if ($main->getCurrentStaffId()) {
			$main->getvar['id'] = intval($main->getvar['id']);
			$query = $db->query("SELECT * FROM `<PRE>templates` WHERE id = '{$main->getvar['id']}'");
			if($db->num_rows($query) > 0) {				
				$data = $db->fetch_array($query);
				echo $data['subject']."{}[]{}".$data['description']."{}[]{}".$data['content'];
			}
		}
	}
	
	public function cat() {
		global $main, $db, $style;
		if ($main->getCurrentStaffId()) {
			$main->getvar['id'] = intval($main->getvar['id']);
			$query = $db->query("SELECT * FROM `<PRE>cats` WHERE id = '{$main->getvar['id']}'");
			if($db->num_rows($query) > 0) {			
				$data = $db->fetch_array($query);
				echo $data['name']."{}[]{}".$data['description'];
			}
		}
	}
	public function art() {
		global $main, $db, $style;
		if ($main->getCurrentStaffId()) {
			$main->getvar['id'] = intval($main->getvar['id']);
			$query = $db->query("SELECT * FROM `<PRE>articles` WHERE `id` = '{$main->getvar['id']}'");
			if($db->num_rows($query) > 0) {				
				$data = $db->fetch_array($query);
				echo $data['name']."{}[]{}".$data['content']."{}[]{}".$data['catid'];
			}
		}
	}
	
	/**
	 * Searchs an user
	 * 
	 */
	public function search() {
		global $main, $db, $style;
		if ($main->getCurrentStaffId()) {
			$type = $main->getvar['type'];	
			
			if(in_array($type, array('user','id','ip','email'))) {				
				$value = $db->strip($main->getvar['value']);
				if($main->getvar['num']) {
					$show = intval($main->getvar['num']);
				} else {
					$show = 10;	
				}
				if($main->getvar['page'] != 1) {
					$lower = intval($main->getvar['page']) * $show;
					$lower = $lower - $show;
					$upper = $lower + $show;
				} else {
					$lower = 0;
					$upper = $show;
				}
				$sql = "SELECT * FROM `<PRE>users` u WHERE u.{$type} LIKE '%{$value}%' ORDER BY u.{$type} ASC LIMIT {$lower}, {$upper}";
				$query = $db->query($sql);
				$rownum = $db->num_rows($query);
				
				echo '<table class="content_table" width="100%" border="0" cellpadding="0" cellspacing="2">
			          <tr>
			            <th width="250px">User</th>
			            <th width="250px">Status</th>
			            <th width="250px" align="right">Actions</th>
			          </tr>';		        
						
				if($db->num_rows($query) == 0) {
					echo "No clients found";	
				} else {					
					while($data = $db->fetch_array($query)) {
						if($n != $show) {
							//$client = $db->client($data['userid']);
							$array['ID']	= $data['id'];
							$array['USER'] 	= $data['user'];
							$array['URL'] 	= URL;
							$user_status 	= $main->getUserStatusList();
							$array['STATUS']= $user_status[$data['status']];						
							echo $style->replaceVar("tpl/user/clientsearchbox.tpl", $array);	
							$n++;
						}
					}
					echo '</table>';
					
					echo '<div class="break"></div>';
					echo '<div align="center">';
					$query = $db->query("SELECT * FROM `<PRE>users` u  WHERE u.{$type} LIKE '%{$value}%' ORDER BY u.{$type} ASC");
					$num = $db->num_rows($query);
					$pages = ceil($num/$show);
					echo "Page ";
					for($i=1; $i != $pages + 1; $i += 1) {
						echo ' <a href="Javascript: page(\''.$i.'\')">'.$i.'</a>';
					}
					echo '</div>';
				}
			}
		}
	}
	
	/**
	 * Gets the select of subdomains
	 */
	public function sub() {
		global $main, $db, $type;
		$package_id = $main->getvar['pack'];
		if (!empty($package_id)) {
			$server_id = $type->determineServer($package_id);			
			$values = $main->getSubDomainByServer($server_id);			
			if (!empty($values)) {
				echo $main->createSelect('csub2', $values);
			} else {
				echo 0;
			}
		}
	}
	
	public function status() {
		global $db, $main;
		if ($main->getCurrentStaffId()) {
			$id 	= intval($main->getvar['id']);
			$status = intval($main->getvar['status']);
			
			$query = $db->query("UPDATE <PRE>tickets SET status = '$status' WHERE id = $id");
			if ($query) {
				echo " <img src=". URL ."themes/icons/accept.png> Status saved  ";
			} else {
				echo " <img src=". URL ."themes/icons/cross.png> There was a problem while saving the status";
			}
		}
	}
	
	public function serverhash() {
		global $main, $server;
		if ($main->getCurrentStaffId()) {
			$type = $main->getvar['type'];
			require_once LINK.'servers/panel.php';	
			if (in_array($type, $server->getAvailablePanels())) {		
				require_once LINK ."servers/". $type .".php";
				$server = new $type(-1);
				if($server->hash) {
					echo 0;	
				} else {
					echo 1;	
				}
			}
		}
	}
	
	public function editserverhash() {
		global $main, $db, $server;
		if ($main->getCurrentStaffId()) {			
			$type 	= $main->getvar['type'];
			$id 	= intval($main->getvar['server']);
			require_once LINK.'servers/panel.php';
			if (in_array($type, $server->getAvailablePanels())) {
				require_once LINK."servers/". $type .".php";
				$serverphp = new $type(-1);
				if($serverphp->hash) {
					echo 0;	
				} else {
					echo 1;	
				}
				$server_info = $server->getServerById($id);				
				echo ";:;". $server_info['accesshash'];
			}
		}
	}
	
	function massemail() {
		global $main, $email, $db;		
		if ($main->getCurrentStaffId()) {			
			$subject = $main->getvar['subject'];
			$msg = $main->getvar['msg'];
			$query = $db->query("SELECT * FROM <PRE>users");
			while($client = $db->fetch_array($query)) {
				$email->send($client['email'], $subject, $msg);	
			}
			echo 1;
		}
	}
	
	function porder() {
		//deprecated?
		/*
		global $main, $db;
		$order = $main->getvar['order'];
		print_r($main->getvar);
		*/
	}
	
	function padd() {
		//deprecated?
		/*
		global $style;
		echo $style->replaceVar("tpl/acppacks/addbox.tpl");
		*/	
	}
	
	function pedit() {
		//deprecated?
		/*
		if($_SESSION['logged']) {
			global $db, $style, $main;
			$query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$main->getvar['do']}'");
			$data = $db->fetch_array($query);
			$array['ID'] = $data['id'];
			$array['BACKEND'] = $data['backend'];
			$array['DESCRIPTION'] = $data['description'];
			$array['NAME'] = $data['name'];
			if($data['admin'] == 1) {
				$array['CHECKED'] = 'checked="checked"';	
			}
			else {
				$array['CHECKED'] = "";
			}
			if($data['reseller'] == 1) {
				$array['CHECKED2'] = 'checked="checked"';	
			}
			else {
				$array['CHECKED2'] = "";
			}
			$additional = explode(",", $data['additional']);
			foreach($additional as $key => $value) {
				$me = explode("=", $value);
				$cform[$me[0]] = $me[1];
			}
			global $type;
			$array['FORM'] = $type->acpPedit($data['type'], $cform);
			$query = $db->query("SELECT * FROM `<PRE>servers`");
			while($data = $db->fetch_array($query)) {
				$values[] = array($data['name'], $data['id']);	
			}
			$array['SERVER'] = $array['THEME'] = $main->dropDown("server", $values, $data['server']);	
			echo $style->replaceVar("tpl/acppacks/editbox.tpl", $array);
		}
		*/
	}

    function nedit() {
    	//deprecated?
    	/*
        if($_SESSION['logged']) {
            global $db, $style, $main;
            $query = $db->query("SELECT * FROM `<PRE>navbar` WHERE `id` = '{$main->getvar['do']}'");
            $data = $db->fetch_array($query);
            $array['ID'] = $data['id'];
            $array['NAME'] = $data['name'];
            $array['VISUAL']= $data['visual'];
            $array['LINK'] = $data['link'];
            $array['ICON'] = $data['icon'];
            //echo $style->replaceVar("tpl/navedit/pbox.tpl", $array);
            //echo "\n<!-- O NOEZ IT R H4XX -->\n"; // <-- Don't remove this.
            echo $style->replaceVar("tpl/navedit/editbox.tpl", $array);
            return true;
        }*/
    }
    

    
	//not supported
    function genkey() {
        global $main, $db;
        /*
        if($_SESSION['logged'] and $main->getvar['do'] == "it") {
            $random = $this->randomString();
            $key = hash('sha512', $random);
            $db->updateConfig('api-key', $key);
            echo '<span style="color:green;">API Key Generated!</span>'."\n".
            '<br /> To get your new key go to the Get API Key page.';
            echo "\n<br />";
            return true;
        }
        */
    }
    
	/**
	 * 
	 * Disabled for security reasons
	 * 	 
	 */	 
    function editcss() {
        global $main, $db, $style;
        if($_SESSION['logged']) {
        	/*
            if(isset($_POST['css'])) {
                $url = $db->config('url')."themes/".$db->config('theme')."/images/";
                $slash = stripslashes(str_replace("&lt;IMG&gt;", "<IMG>", $_POST['css'])); #Strip it back
                $filetochange = LINK."../themes/".$db->config('theme')."/style.css";
                file_put_contents($filetochange, $slash);
                echo "CSS File Modified! Refresh for changes.";
            }
            else {
                return;
            }*/
            
        }
        return true;
    }
    
	/**
	 * 
	 * Disabled for security reasons
	 * 	 
	 */	 
    function edittpl() {
        global $main, $db, $style;
        if($_SESSION['logged']) {
            if(isset($_POST['file']) and isset($_POST['contents'])) {
            	/*
                $file = $_POST['file'];
                $contents = $_POST['contents'];
                $slash = $contents;
                //We have to do some special stuff for the footer.
                //This gets complex. But it works. I might simplify it sometime.
                if($file == "footer") {
                    $foundcopy = false;
                    $diemsg = 'Trying to remove the copyright? No thanks.';
                    if(!strstr($contents, '<COPYRIGHT>')) {
                        $slash = str_replace("&lt;COPYRIGHT&gt;", "<COPYRIGHT>", $slash);
                        if(!strstr($slash, '<COPYRIGHT>')) {
                            die($diemsg);
                        }
                        else {
                            $foundcopy = true;
                        }
                    }
                    else {
                        $foundcopy = true;
                    }
                    if($foundcopy == true) {
                        $slash = stripslashes(str_replace("&lt;PAGEGEN&gt;", "<PAGEGEN>", $slash)); # Yay, strip it
                        //$slash = str_replace("&lt;COPYRIGHT&gt;", "<COPYRIGHT>", $slash);
                    }
                }
                $slash = stripslashes(str_replace("&lt;THT TITLE&gt;", "<THT TITLE>", $slash)); # Yay, strip it
                $slash = str_replace("&lt;JAVASCRIPT&gt;", "<JAVASCRIPT>", $slash); #jav
                $slash = str_replace("&lt;CSS&gt;", "<CSS>", $slash); #css
                $slash = str_replace("&lt;ICONDIR&gt;", "<ICONDIR>", $slash); #icondir
                $slash = str_replace("&lt;IMG&gt;", "<IMG>", $slash);
                $slash = str_replace("&lt;MENU&gt;", "<MENU>", $slash);
                $slash = str_replace("&#37;INFO%", "%INFO%", $slash);
                #Alrighty, what to do nexty?
                $filetochange = LINK."../themes/".$db->config('theme')."/".$file.".tpl";
                $filetochangeOpen = fopen($filetochange,"w");
                fputs($filetochangeOpen,$slash);
                fclose($filetochangeOpen) or die ("Error Closing File!");
                echo $file . '.tpl Modified! Refresh for changes.';
                die();
                */
            }
        }
        return true;
    }
	
	//Deprecated?
    function notice() {
    	/*
        global $style;
        if(isset($_REQUEST['status']) and isset ($_REQUEST['message'])) {
            if($_REQUEST['status'] == "good") {
                $status = true;
            }
            else {
                $status = false;
            }
            echo $style->notice($status, $_REQUEST['message']);
        }
        return true;*/
    }
	//deprecated?
   function upload() {
   	//deprecated?
   	/*
       global $main;
       if($_SESSION['logged']) {               
       }*/
   }

   function navbar() {
       global $main, $db;
       if ($main->getCurrentStaffId()) {         
           if(isset($main->postvar['action']) || isset($main->getvar['action'])) {
               $action  = $_REQUEST['action'];
               
               $id 		= intval($main->postvar['id']);               
               $name 	= $db->strip($main->postvar['name']);
               $icon 	= $db->strip($main->postvar['icon']);
               $link 	= $db->strip($main->postvar['link']);
               
               if ($main->checkToken(false)) {
	               switch($action) {
	                   case "add":
	                       if(isset($name) and isset($icon) and isset($link)) {                       		
								$db->query("INSERT INTO `<pre>navbar` (visual, icon, link) VALUES('{$link}', '{$icon}','{$link}')");
	                       }
	                       break;
	                   case "edit":
	                       if(isset($id) and isset($name) and isset($icon) and isset($link)) {
	                            $db->query("UPDATE `<pre>navbar` SET
	                            visual = '{$name}',
	                            icon = '{$icon}',
	                            link = '{$link}'
	                            WHERE `id` = '{$id}'");
	                       }
	                       break;
	                   case "delete":
	                       if(isset($_GET['id'])) {
	                       		$id = intval($main->getvar['id']);
	                           $db->query("DELETE FROM `<PRE>navbar` WHERE id = '$id'");
	                       }
	                       break;
	                   case "order":
	                       if(isset($main->postvar['order'])) {
	                           $list = explode("-", $main->postvar['order']);
	                           $i = 0;
	                           foreach($list as $id) {                           		
	                           	  $id = intval($id);
	                           	  $sql = "UPDATE `<PRE>navbar` SET `order` = '{$i}' WHERE id = {$id}";                           	  
	                              $db->query($sql);
	                              $i++;
	                           }
	                       }
	                       break;
	               }
               }
           }
       }
   }
	/**
	 * @todo this function might be deprecated
	 */
   function acpPackages() {
   	/*
       global $main, $db, $type;
       return; //disabled not used yet 
       if ($_SESSION['logged']) {
           $P = $_POST;
           $G = $_GET;
           $R = $_REQUEST;
           $action = $R['action'];
           
           $id = intval($main->postvar['id']);
           $name = $db->strip($main->postvar['name']);
           $backend = $db->strip($main->postvar['backend']);
           $description = $db->strip($main->postvar['description']);
           $type2 = $db->strip($main->postvar['type']);
           $val = $db->strip($main->postvar['val']);
           $reseller = $db->strip($main->postvar['reseller']);
           $order = $db->strip($main->postvar['order']);
           $additional = $db->strip($main->postvar['additional']);
           $server = $db->strip($main->postvar['server']);

           if(isset($P['action']) or $G['action']) {
               switch($action) {
                   case "edit":
                       if(empty($P['additional']) or $P['additional'] == "undefined") {
                           $db->query("UPDATE `<PRE>packages` SET
                        `name` = '{$name}',
                        `backend` = '{$backend}',
                        `description` = '{$description}',
                        `admin` = '{$val}',
                        `reseller` = '{$reseller}'
                        WHERE `id` = '{$id}'");
                       }
                       else {
                        $db->query("UPDATE `<PRE>packages` SET
                        `name` = '{$name}',
                        `backend` = '{$backend}',
                        `description` = '{$description}',
                        `admin` = '{$val}',
                        `reseller` = '{$reseller}',
                        `additional` = '{$additional}'
                        WHERE `id` = '{$id}'");
                       }
                       break;

                   case "add":
                       if(empty($P['additional']) or $P['additional'] == "undefined") {
                           $db->query("INSERT INTO <PRE>packages
                           (
                           `name`,
                           `backend`,
                           `description`,
                           `type`,
                           `server`,
                           `admin`,
                           `reseller`
                           )
                           VALUES
                           (
                           '{$name}',
                           '{$backend}',
                           '{$description}',
                           '{$type2}',
                           '{$server}',
                           '{$val}',
                           '{$reseller}'
                           );
                            ");
                       }
                       else {
                           $db->query("INSERT INTO <PRE>packages
                           (
                           `name`,
                           `backend`,
                           `description`,
                           `type`,
                           `server`,
                           `admin`,
                           `reseller`,
                           `additional`
                           )
                           VALUES
                           (
                           '{$name}',
                           '{$backend}',
                           '{$description}',
                           '{$type2}',
                           '{$server}',
                           '{$val}',
                           '{$reseller}',
                           '{$additional}'
                           );
                            ");
                       }
                       break;

                   case "delete":
                       if(isset($G['id'])) {
                       	$id= intval($main->getvar['id']);
                           $db->query("DELETE FROM `<PRE>packages` WHERE `id` = '$id'");
                       }
                       break;


                   case "order":
                        if(isset($P['order'])) {
                            $ids = explode("-", $order);
                            $i = 0;
                            foreach($ids as $id) {
                            	$id = intval($id);
                                $db->query("UPDATE `<PRE>packages` SET `order` = '{$i}' WHERE `id` = '{$id}'");
                                $i++;
                            }
                        }
                   break;

                   case "typeInfo":
                       if(isset($G['type'])) {
                        echo $type->acpPadd($G['type']);
                       }
                       break;
               }
           }
       }
*/
   }

   function uiThemeChange() {
       global $main, $db;       
       if ($main->getCurrentStaffId()) {         
           if(isset($main->getvar['theme'])) {
               $db->updateConfig('ui-theme', $main->getvar['theme']);
           }
       }
   }
   
   function ispaid() {   		
		global $db, $main, $invoice;		
		if ($main->getCurrentUserId()) {
			if (isset($_SESSION['last_invoice_id']) && !empty($_SESSION['last_invoice_id']) && is_numeric($_SESSION['last_invoice_id'])) {
				//$invoice_info = $invoice->getInvoiceInfo($_SESSION['last_invoice_id']);
				echo intval($_SESSION['last_invoice_id']);
				//Deleting session	
				unset($_SESSION['last_invoice_id']);				
			} else {
				echo 0;
			}
			unset($_SESSION['last_invoice_id']);
			//Last event before redirect
			$main->clearToken();
		}
   }
   
	function deleteTicket() {
		global $main, $db;
		if ($main->getCurrentStaffId()) {
			$tid = intval($main->getvar['ticket']);
			if($tid != "" && is_numeric($tid)) {
				$query = "DELETE FROM <PRE>tickets WHERE id = $tid";
			    $db->query($query);
			    $query = "DELETE FROM <PRE>tickets WHERE ticketid = $tid";
			    $db->query($query);
		    }
	    }
	}
   
   /**
    * Get addons in the Order Form
    */
   function getAddons() {
   		global $main, $db, $currency;
		$billing_id = intval($main->getvar['billing_id']);			
   		$package_id = intval($main->getvar['package_id']);	  
   		
   		if(!empty($billing_id) && !empty($package_id)) {
   		
	   		$html = '<fieldset style="width: 98%;"><legend><b>Package Order</b></legend><table width="100%" >';
	   		
	   		$sql = "SELECT a.name, amount, bc.name  as billing_name  FROM `<PRE>packages` a INNER JOIN `<PRE>billing_products` b ON (a.id = b.product_id) INNER JOIN `<PRE>billing_cycles` bc
					ON (bc.id = b.billing_id) WHERE a.id = $package_id AND bc.id = $billing_id  AND b.type = '".BILLING_TYPE_PACKAGE."' ";
			$result = $db->query($sql); 
			$package_billing_info_exist = false;
			if ($db->num_rows($result) > 0) {				
				while($data = $db->fetch_array($result)) {
					$amount_to_show  = $currency->toCurrency($data['amount']);			
			       	$html .= "<tr><td width=\"33%\"> {$data['name']}</td>
			            <td width=\"33%\" align=\"right\"><strong>{$data['billing_name']}</strong></td>
			            <td width=\"33%\" align=\"right\">{$amount_to_show}</td>		     
			        	</tr>";
			        $package_billing_info_exist = true;
				} 
			} else {
				$html .='No data for this package at the moment'; 					
			}
			
	   		$html .='</table></fieldset><br />';
	   		
	   		$sql = "SELECT * FROM <PRE>package_addons WHERE package_id = $package_id ";
	   		$result = $db->query($sql); 		
	   		
	   		if ($db->num_rows($result) > 0) {
	   			$info_exist = false;
		   		$html .= '<fieldset style="width:98%;"><legend><b>Order Add-Ons</b></legend>';
		   		$html .= '<table width="100%" >';
		   		
		   		while($data = $db->fetch_array($result,'ASSOC')) {		   			
		   			$sql = "SELECT a.name, a.mandatory, description, setup_fee, bc.name as billing_name, b.amount FROM `<PRE>addons` a INNER JOIN `<PRE>billing_products` b ON (a.id = b.product_id) INNER JOIN `<PRE>billing_cycles` bc
							ON (bc.id = b.billing_id) WHERE a.status = ".ADDON_STATUS_ACTIVE." AND a.id = {$data['addon_id']} AND bc.id = $billing_id  AND b.type = '".BILLING_TYPE_ADDON."' ORDER BY a.name";
					$addon_result = $db->query($sql);
					if ($db->num_rows($addon_result) > 0) {
						$addon = $db->fetch_array($addon_result, 'ASSOC');
						
						if (!empty($addon['amount']) && intval($addon['amount']) != 0 ) {
							$addon['amount'] = $currency->toCurrency($addon['amount']);
						} else {
							$addon['amount'] = ' - ';
						}
					
						//@todo setup feee per 	
						//$setup_fee = '<b>Setup Fee:</b></td><td align="right">'.$addon['setup_fee'];
						$setup_fee ='';
						$html .='<tr><td width="1%">';
						$checked = '';
						$addon_mandatory_text = '';						
						if ($addon['mandatory'] == 1) {
							$checked = 'checked="on" disabled';
							$addon_mandatory_text = '(Mandatory)';
						}						
						$html .='<input id="addon_ids" '.$checked.' value="'.$data['addon_id'].'" name="addon_ids" type="checkbox"></td>';
						$html .='<td width="33%">'.$addon['name'].' '.$addon_mandatory_text.' </td><td align="right">'.$setup_fee.'</td><td align="right"><strong>'.$addon['billing_name'].'</strong></td>';
						$html .='<td width="33%" align="right">'.$addon['amount'].'</td></tr>';
						$info_exist = true;
					}
		   		}
		   		$html .='</table></fieldset>';
		   		$html .='<input type="hidden" name="billing_id" value="'.$billing_id.'">';
	   		}
			if ($package_billing_info_exist) {
	   			echo $html;
	   		} else {
	   			echo 'Please select a Billing cycle';
	   		}
   		} else  {
   			echo 'Please select a Billing cycle';
   		}
   }
   
   /**
    * Get Order summary
    * @todo remove some html and move it in a template 
    */
   function getSummary() {
   		global $main, $db, $currency;
   		
   		$package_id = intval($main->getvar['package_id']);
		$billing_id = intval($main->getvar['billing_id']);
		$addon_list = $main->getvar['addon_list'];		
		$addon_list = explode('-' , $addon_list);
		
		$new_addon_list = array();
		foreach($addon_list as $addon) {
			if (!empty($addon) && is_numeric($addon)) {
				$addon = intval($addon);
				$addon = "'$addon'";
				$new_addon_list[] =  $addon;
			}
		}
		$new_addon_list = implode(',', $new_addon_list);
		
		$sql = "SELECT a.name, amount , bc.name as billing_name  FROM `<PRE>packages` a INNER JOIN `<PRE>billing_products` b ON (a.id = b.product_id) INNER JOIN `<PRE>billing_cycles` bc
				ON (bc.id = b.billing_id) WHERE a.id = {$package_id} AND bc.id = $billing_id AND b.type = '".BILLING_TYPE_PACKAGE."'";
		$result = $db->query($sql); 
		$html = '';
		$total = 0;
		
		$html  = '<fieldset  style="width: 98%;"><legend><b>Summary</b></legend>';
		$html .= '<table width="100%" align="center" border="0" cellpadding="3" cellspacing="3">
				        <tr>
				            <td width="2%"></td>
				            <td width="28%"><b>Items in basket</b></td>
				            <td width="50%"><b>Description</b></td>
				            <td width="18%" align="right"><b>Cost</b></td>
				            <td width="2%"></td>
				        </tr>';
				        					        
		while($data = $db->fetch_array($result,'ASSOC')) {
			
			/*if (!empty($data['amount']) && intval($data['amount']) != 0 ) {
				$amount_to_show  = $currency->toCurrency($data['amount']);
			} else {
				$amount_to_show  = ' - ';
			}*/
					
			$amount_to_show  = $currency->toCurrency($data['amount']);			
			
	       	$html .= "<tr>
	            <td></td>
	            <td>{$data['name']}</td>
	            <td>{$data['billing_name']} </td>
	            <td align=\"right\">{$amount_to_show}</td>
	            <td></td>
	        	</tr>";		        	
	        $total = $total + $data['amount'];
		}			
		
		if (!empty($new_addon_list) && !empty($main->getvar['billing_id'])) {
			$sql = "SELECT a.name, setup_fee, bc.name as billing_name, b.amount FROM `<PRE>addons` a INNER JOIN `<PRE>billing_products` b ON (a.id = b.product_id) INNER JOIN `<PRE>billing_cycles` bc
					ON (bc.id = b.billing_id) WHERE a.id IN ({$new_addon_list}) AND bc.id = $billing_id AND b.type = '".BILLING_TYPE_ADDON."' ORDER BY a.name";
			$result = $db->query($sql); 
		
			while($data = $db->fetch_array($result)) {
				//$amount_to_show  = $currency->toCurrency($data['amount']);		
				//$amount_to_show = '';	
				
				if (!empty($data['amount']) && intval($data['amount']) != 0 ) {
					$amount_to_show = $currency->toCurrency($data['amount']);
				} else {
					$amount_to_show = ' - ';
				}
							
		       	$html .= "<tr>
		            <td></td>
		            <td>{$data['name']}</td>
		            <td>{$data['billing_name']} </td>
		            <td align=\"right\">{$amount_to_show}</td>
		            <td></td>
		        	</tr>";
		        $total = $total + $data['amount'];
			}
		}
		
		$total_to_show  = $currency->toCurrency($total);	
		$html .='<tr>
		            <td></td>
		            <td></td>
		            <td><b><p class="price" >Total</p></b></td>
		            <td align="right"><p class="price">'.$total_to_show.'</p></td>
		            <td></td>
		        </tr>';
		$html .='</table>';
		$html .='</fieldset>';	  	        
		echo $html;
   }
   
   function changeAddons() {
   		global $main, $db, $addon, $currency, $order;
   		if ($main->getCurrentStaffId()) {   		
	   		$package_id = $main->getvar['package_id'];
			$order_id	= $main->getvar['order_id'];	  
			 		
	   		$order_info = $order->getOrderInfo($order_id);	   		
	   		$billing_id = $order_info['billing_cycle_id'];
	   		$html = '';
	   		$addon_list = $addon->getAddonsByPackage($package_id);
	   		if (is_array($addon_list) && count($addon_list) > 0 ) { 
		   		foreach($addon_list as $addon_item) {
					$checked = false;
					if (isset($selected_values[$addon_item['id']])) {
						$checked = true;
					}	
					$html .= $main->createCheckbox($addon_item['name'], 'addon_'.$addon_item['id'], $checked);					
				}
	   		}
			echo $html;   		
   		}
   }	   
   
   function loadaddons() {
   		global $main, $db, $addon, $currency, $order;
   		if ($main->getCurrentStaffId()) {  
	   		$package_id = $main->getvar['package_id'];
			$billing_id	= $main->getvar['billing_id'];
			$order_id	= $main->getvar['order_id'];
			$action		= $main->getvar['action'];
			
			$addon_selected_list = array();
			if (!empty($order_id)) {
				$order_info = $order->getOrderInfo($order_id);
				$addon_selected_list = $order_info['addons'];
			}
			if ($action == 'add') {
				$generate_checkbox = true;
			} else {
				$generate_checkbox = false;
			}				
			$result = $addon->showAllAddonsByBillingCycleAndPackage($billing_id, $package_id, array_flip($addon_selected_list),$generate_checkbox);
			if (!empty($result) && isset($result['html'])) {
				echo $result['html'];
			}
   		}	
   }
   
   
   function searchuser() {
		global $main, $user;			
		if ($main->getCurrentStaffId()) {
	   		$query 		= $main->postvar['query'];
	   		$user_list 	= $user->searchUser($query);
	   		if (is_array($user_list) && count($user_list) > 0) {
	   			foreach($user_list as $user_item) {
	   				$user_name = $user->formatUsername($user_item['firstname'], $user_item['lastname']);
	   				$user_name = $user_name." (".$user_item['email'].")";
	   				echo "<li onclick=\"fill('{$user_name}', '{$user_item['id']}');\">$user_name</li>";	
	   			}
	   		}
		}
   }
   
   function loadpackages() {
   		global $main, $db, $addon, $currency, $order, $package;
   		if ($main->getCurrentStaffId()) {	   		
	   		$billing_id = intval($main->getvar['billing_id']);
	   		$order_id	= intval($main->getvar['order_id']);   		
	   		$action	= $main->getvar['action'];
	   		
			$order_info = $order->getOrderInfo($order_id);
			
			$packages = $package->getAllPackagesByBillingCycle($billing_id);
					
	   		$package_list = array();
	   		
			foreach($packages as $package) {
				$package_list[$package['id']] = $package['name'].' - '.$currency->toCurrency($package['amount']);				
			}
			if ($action == 'add') {	
				echo $main->createSelect('package_id', $package_list, $order_info['pid'], array('onchange'=>'loadAddons(this);', 'class'=>'required'));
			} elseif ($action == 'edit') {
				echo $package_list[$order_info['pid']];	
			}
   		}
   }
   
   function sendtemplate() {
   	//Not implemented yet
   	/*
		global $db, $main, $email,$user, $order;	
			
		$template 				= $main->getvar['template'];
		$order_id 				= $main->getvar['order_id'];
		
		$order_info 			= $order->getOrder($order_id, true);		
		$emailtemp 				= $db->emailTemplate($template);
		$user_info 				= $user->getUserById($order_info['USER_ID']);			
					
		$array['FIRSTNAME']		= $user_info['firstname'];
		$array['LASTNAME'] 		= $user_info['lastname'];			
		$array['SITENAME'] 		= $db->config('name');
		$array['ORDER_ID'] 		= $order_id;
		$array['COMPANY'] 		= $user_info['company'];			
		$array['VATID'] 		= $user_info['vatid'];			
		$array['FISCALID'] 		= $user_info['fiscalid'];
		$array['PACKAGE'] 		= $order_info['PACKAGES'];
		$array['ADDONS'] 		= $order_info['ADDON'];
		$array['DOMAIN'] 		= $order_info['domain'];
		$array['BILLING_CYCLE'] = $order_info['BILLING_CYCLES'];
		$array['TOTAL'] 		= $order_info['TOTAL'];
		$array['TOS'] 		    = $db->config('TOS');
		//$array['ADMIN_EMAIL'] 	= $db->config('EMAIL');
		
		$email->send($user_info['email'], $emailtemp['subject'], $emailtemp['content'], $array);
		echo 'Email sent';		
		*/
   }
   
   function getOrders() {
   		global	$main, $order;
   		if ($main->getCurrentStaffId()) {
   			$page = $main->getvar['page'];	   			   		
			$array = $order->getAllOrdersToArray('', $page);
			echo $array['list'];
   		}
   }
   
   function getInvoices() {
   		global	$main, $invoice;
   		if ($main->getCurrentStaffId()) {
	   		$page = $main->getvar['page'];	   			   		
			$array = $invoice->getAllInvoicesToArray('', $page);
			echo $array['list'];
   		}
   }
   
   	public function checkSubDomainExists() {
		global $main, $db, $package, $order;			
		$domain  		= $main->getvar['domain'];
		$package_id  	= $main->getvar['package_id'];			
		$package_info 	= $package->getPackage($package_id);		
		$final_domain 	= $main->getvar['final_domain'];		
		$subdomain_id 	= 0;
		
		if($main->getvar['domain'] == 'sub') { # If Subdomain				
			$subdomain_list = $main->getSubDomainByServer($package_info['server']);			
			$subdomain 		= $subdomain_list[$main->getvar['subdomain_id']];			
			$subdomain_id	= $main->getvar['subdomain_id'];	
		}			
		if ($order->domainExistInOrder($final_domain, $subdomain_id) ) {
			echo 1;				
		} else {
			echo 0;	
		}			
		return;
	}
	
	public function checkSubDomainExistsSimple() {
		global $main, $db, $package, $order;			
		$final_domain  = $main->getvar['domain'];
		$subdomain_id  = $main->getvar['subdomain_id'];				
		if ($order->domainExistInOrder($final_domain, $subdomain_id) ) {
			echo 1;				
		} else {
			echo 0;	
		}			
		return;
	}
	
	public function usernameExists() {
		global $user, $main;		
		$user_info = $user->getUserByUserName($main->getvar['user']);			
		if ($user_info == false) {
			echo '0';
		} else {
			echo '1';
		}
	}
	public function validateUserName() {
		global $main, $user;
		$result = $user->validateUserName($main->getvar['user']);
		if ($result) {
			echo '0';
		} else {
			echo '1';
		}		
	}
	
	public function validateDomain() {
		global $main;
		$result = $main->validDomain($main->getvar['domain']);
		if ($result) {
			echo '0';
		} else {
			echo '1';
		}		
	}
	
	
	public function clientLogin() {
		global $main;
		$user  = $main->getvar['user'];	
		$pass  = $main->getvar['pass'];
		if ($main->clientLogin($user, $pass)) {
			echo '1';		
		} else {
			echo '0';
		}		
	}
	
	public function getNavigation() {
		global $main;
		$user_info = $main->getCurrentUserInfo();		
		if (!empty($user_info)) {
			echo _('Logged in as').'<a href="'.URL.'client">'.$user_info['user'].'</a> | <a href="'.URL.'client/?page=logout">'._('Logout').'</a>';
		} else {
			echo _('Log in to your account');
		}		
	}
		
}

if(isset($_GET['function']) && !empty($_GET['function'])) {
	//If this is an AJAX request?	
	$is_xml_request = $main->isXmlHttpRequest();
	if (SERVER_STATUS == 'test') {
		//If this is a server test we set this to true to easy debug
		$is_xml_request = true;
	}	
	if ($is_xml_request) {
		$ajax = new AJAX();
		if (method_exists($ajax, $_GET['function'])) {
			if (INSTALL == 1) {
				//Protecting AJAX calls now we need a token set in variables.php
				if ($main->checkToken(false)) {
					$ajax->{$_GET['function']}();
				}
			} else {
				$ajax->{$_GET['function']}();
			}		
		}
	} else {
		//Someone is trying to check the AJAX Reponse from a browser
		$main->redirect();
	}
}