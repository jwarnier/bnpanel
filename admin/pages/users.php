<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Clients Sub Menu";
		$this->navlist[] = array("Client List", "magnifier.png", "search");
		$this->navlist[] = array("Add Client", "add.png", "add");		
		$this->navlist[] = array("Client Statistics", "book.png", "stats");
		
		//$this->navlist[] = array("Admin Validate", "user_suit.png", "validate");
	}
	
	public function description() {
		global $db, $main;
		$query = $db->query("SELECT * FROM `<PRE>users` ORDER BY `signup` DESC");
		if($db->num_rows($query) != 0) {
			$data = $db->fetch_array($query);
			$newest = $main->sub("Latest Signup:", $data['user']);
		}
		return "<strong>Clients</strong><br />
		This is the area where you can manage all your clients that have signed up for your service. You can perform a variety of tasks like suspend, terminate, email and also check up on their requirements and stats.". $newest;	
	}
	
	public function content() { # Displays the page 
		global $main, $style, $db, $order,$package, $invoice, $server, $email,$user;
		
		switch($main->getvar['sub']) {
			case 'search':
				if($main->getvar['do'] ) {					
					$client = $user->getUserById($main->getvar['do']);
					$array2['DATE'] 	= strftime("%D", $client['signup']);
					$array2['EMAIL'] 	= $client['email'];
					$array2['USER'] 	= $client['user'];
					$array2['DOMAIN'] 	= $client['domain'];
					$array2['CLIENTIP'] = $client['ip'];
					$array2['FIRSTNAME']= $client['firstname'];
					$array2['LASTNAME'] = $client['lastname'];
					$array2['ADDRESS'] 	= $client['address'];
					$array2['CITY'] 	= $client['city'];
					$array2['STATE'] 	= $client['state'];
					$array2['ZIP'] 		= $client['zip'];
					$array2['COUNTRY'] 	= $client['country'];
					$array2['PHONE'] 	= $client['phone'];			
					
					$user_status_list 	= $main->getUserStatusList();										
					$array2['STATUS']  	= $user_status_list[$client['status']];					
					$array['CONTENT'] 	= $style->replaceVar("tpl/clientdetails.tpl", $array2);					
					$array['URL'] 		= URL;
					$array['ID'] 		= $client['id'];
					echo $style->replaceVar("tpl/clientview.tpl", $array);
					
				} else {
					//selecting all clients
					$array['NAME'] = $db->config("name");
					$array['URL'] = $db->config("url");
					$values[] = array("Admin Area", "admin");
					$values[] = array("Order Form", "order");
					$values[] = array("Client Area", "client");
					$array['DROPDOWN'] = $main->dropDown("default", $values, $db->config("default"));
					echo $style->replaceVar("tpl/clientsearch.tpl", $array);			
				}
			break;
			
			case 'orders':
				if($main->getvar['do'] ) {					
					$return_array  		= $order->getAllOrdersToArray($main->getvar['do']);	
					$array['CONTENT'] 	=  $style->replaceVar("tpl/orders/client-page.tpl", $return_array);		
					$array['BOX'] 		= "";										
					$array['URL'] 		= URL;
					$array['ID'] 		= $main->getvar['do'];			
					echo $style->replaceVar("tpl/clientview.tpl", $array);											
				}			
			break;
			
			case 'invoices':
				if($main->getvar['do'] ) {
					$return_array  		= $invoice->getAllInvoicesToArray($main->getvar['do']);					
					$array['CONTENT'] 	=  $style->replaceVar("tpl/invoices/client-page.tpl", $return_array);
					$array['BOX'] 		= "";										
					$array['URL'] 		= URL;
					$array['ID'] 		= $main->getvar['do'];
					
					echo $style->replaceVar("tpl/clientview.tpl", $array);	
				}			
			break;			
			case 'edit':
				if($main->getvar['do']) {
					if ($_POST) {
						$user->edit($main->getvar['do'], $main->postvar);
						if ($main->postvar['status'] == USER_STATUS_DELETED) {
							$main->redirect('?page=users&sub=search');
						}
					}
					
					$array = $user->getUserById($main->getvar['do']);
					
					$array['status'] 		= $main->createSelect('status', $main->getUserStatusList(), $array['status']);					
					$array['country']		= $main->countrySelect($array['country']);
						
					$main_array['CONTENT'] 	= $style->replaceVar("tpl/user/edit.tpl", $array);
					$main_array['BOX'] 		= "";
					$main_array['ID'] 		= $main->getvar['do'];
										
					echo $style->replaceVar("tpl/clientview.tpl", $main_array);							
				}
			break;
			
			case 'email':
				if($main->getvar['do']) {
					if($_POST) {
						global $email;
						$user_info = $user->getUserById($main->getvar['do']);
						$email->send($user_info['email'] ,$main->postvar['subject'], $main->postvar['content']);
						$main->errors("Email sent!");
					}
					$array['BOX'] = "";
					$array['CONTENT'] = $style->replaceVar("tpl/email/emailclient.tpl");
					$array['ID'] 	  = $main->getvar['do'];
					echo $style->replaceVar("tpl/clientview.tpl", $array);
				}
			break;
			
			case 'passwd':
				if($main->getvar['do']) {							
					if($_POST) {
						if(empty($main->postvar['passwd'])) {
							$main->errors('A password was not provided.');
							$array['BOX'] = "";
							$array['CONTENT'] = $style->replaceVar("tpl/clientpwd.tpl");
						} else {						
							$command = $user->changeClientPassword($main->getvar['do'], $main->postvar['passwd']);
							if($command === true) {
								$main->errors('Password changed!');
							} else {
								$main->errors((string)$command);
							}
						}
					}
					$array['ID'] 		= $main->getvar['do'];
					$array['BOX'] = "";
					$array['CONTENT'] = $style->replaceVar("tpl/clientpwd.tpl");
					echo $style->replaceVar("tpl/clientview.tpl", $array);
				}
			break;	
			
			default:		
			break;
			case 'search':
				if($main->getvar['do'] ) {					
					echo $style->replaceVar("tpl/clientview.tpl", $array);
				} else {
					$array['NAME'] = $db->config("name");
					$array['URL'] = $db->config("url");
					$values[] = array("Admin Area", "admin");
					$values[] = array("Order Form", "order");
					$values[] = array("Client Area", "client");
					$array['DROPDOWN'] = $main->dropDown("default", $values, $db->config("default"));
					echo $style->replaceVar("tpl/clientsearch.tpl", $array);
				}
				break;
			case 'stats':
				//@todo fix this queries
				$query = $db->query("SELECT * FROM `<PRE>users`");
				$array['CLIENTS'] = $db->num_rows($query);
				$query = $db->query("SELECT * FROM `<PRE>orders` WHERE `status` = '".USER_STATUS_ACTIVE."'");
				$array['ACTIVE'] = $db->num_rows($query);
				$query = $db->query("SELECT * FROM `<PRE>orders` WHERE `status` = '".USER_STATUS_SUSPENDED."'");
				$array['SUSPENDED'] = $db->num_rows($query);
				$query = $db->query("SELECT * FROM `<PRE>orders` WHERE `status` = '".USER_STATUS_WAITING_ADMIN_VALIDATION."'");
				$array['ADMIN'] = $db->num_rows($query);				
				$query = $db->query("SELECT * FROM `<PRE>orders` WHERE `status` = '".USER_STATUS_WAITING_USER_VALIDATION."'");
				$array['WAITING'] = $db->num_rows($query);	
							
				$query = $db->query("SELECT * FROM `<PRE>orders` WHERE `status` = '".USER_STATUS_DELETED."'"); 
				$array['CANCELLED'] = $db->num_rows($query);
				
				echo $style->replaceVar("tpl/clientstats.tpl", $array);
				break;
			
			case 'add':
				$array = $user->setDefaults();				
				if ($_POST) {					
					$user_id = $user->create($main->postvar);					
					if (!empty($user_id) && is_numeric($user_id)) {
						$main->errors("Account added!");
												
					} else {
						$main->errors("Account NOT added!");
						$array = $main->postvar;						
					}					
				}			
				$array['status'] = $main->createSelect('status', $main->getUserStatusList(), '');				
				echo $style->replaceVar("tpl/user/add.tpl", $array);				
			break;
			case 'validate':		
			break;					
		}		
	}
}