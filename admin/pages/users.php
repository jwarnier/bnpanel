<?php
/* For licensing terms, see /license.txt */

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Clients Sub Menu";
		$this->navlist[] = array("View All Clients", "magnifier.png", "search");
		$this->navlist[] = array("Add Client", "add.png", "add");		
		$this->navlist[] = array("Client Statistics", "book.png", "stats");
		
		//$this->navlist[] = array("Admin Validate", "user_suit.png", "validate");
	}
	
	public function description() {
		global $db, $main;
		$query = $db->query("SELECT * FROM `<PRE>users` ORDER BY `signup` DESC");
		$newest = '';
		if ($db->num_rows($query) != 0) {
			$data = $db->fetch_array($query);
			$newest = $main->sub("Latest Signup:", $data['user']);
		}
		return "<strong>Clients</strong><br />
		This is the area where you can manage all your clients that have signed up for your service. You can perform a variety of tasks like suspend, terminate, email and also check up on their requirements and stats.". $newest;	
	}
	
	public function content() { # Displays the page 
		global $main, $style, $db, $order,$package, $invoice, $server, $email,$user;
		require_once LINK.'validator.class.php';
		
		switch($main->getvar['sub']) {			
			case 'add':
				
				$asOption = array(
					    'rules' => array(
					        'user' 			=> array('required'=>true,'validateUsername'=>'error','UsernameExists'=>'Error'),			        
					        'password' 		=> 'required',
					        'confirmp' 		=> 'required',
					        'email' 		=> array('required'=>true, 'email'=>true),
					        'status' 		=> 'required'					        					            
					     ),			    
					    'messages' => array(			
					    	'user'=>array('required'=>'This field is required', 'validateUsername'=>'Not a valid Username (6 character minimum)',  'UsernameExists'=>'Username already exists' )		        			       
					    )
					);				
				$array = $user->setDefaults();	
				$array['json_encode'] = json_encode($asOption);				
				$oValidator = new Validator($asOption);		
								
				if ($_POST && $main->checkToken()) {		
					$result = $oValidator->validate($_POST);					
					if (empty($result)) {	
						$user_id = $user->create($main->postvar);					
						if (!empty($user_id) && is_numeric($user_id)) {
							$main->errors("Account added!");
							$main->redirect('?page=users&sub=search&msg=1');
						} else {
							$main->errors("Account NOT added!", true);	
							$array = $main->postvar;					
							$main->redirect('?page=users&sub=add&msg=1');												
						}
					} else {
						$main->errors("Account NOT added!", true);	
						$array = $main->postvar;					
						$main->redirect('?page=users&sub=add&msg=1');
					}
					$main->generateToken();		
				}			
				$array['country']		= $main->countrySelect($array['country']);
				$array['status'] = $main->createSelect('status', $main->getUserStatusList(), '');				
				echo $style->replaceVar("tpl/user/add.tpl", $array);				
			break;
			
			case 'edit':
				if ($main->getvar['do']) {
					
					$asOption = array(
					    'rules' => array(
					        'user' 			=> array('required'=>true,'validateUsername'=>'error'),
					        'email' 		=> array('required'=>true, 'email'=>true),
					        'status' 		=> 'required'					        					            
					     ),			    
					    'messages' => array(			
					    	'user'=>array('required'=>'This field is required', 'validateUsername'=>'Not a valid Username (6 character minimum)')
					    )
					);
							
					$oValidator = new Validator($asOption);	
							
					if ($_POST && $main->checkToken()) {
						$result = $oValidator->validate($_POST);
						if (empty($result)) {
							$user->edit($main->getvar['do'], $main->postvar);
							if ($main->postvar['status'] == USER_STATUS_DELETED) {
								$main->redirect('?page=users&sub=search');
							}
							$main->errors('Client edited');
							$main->redirect('?page=users&sub=search&msg=1&do='.$main->getvar['do']);
						} else {
							$main->errors("Account NOT edited!", true);												
							$main->redirect('?page=users&sub=search&msg=1');
						}
					}
					
					$array 					= $user->getUserById($main->getvar['do']);
					
					$array['status'] 		= $main->createSelect('status', $main->getUserStatusList(), $array['status']);					
					$array['country']		= $main->countrySelect($array['country']);
					$array['json_encode'] 	= json_encode($asOption);
						
					$main_array['CONTENT'] 	= $style->replaceVar("tpl/user/edit.tpl", $array);
					$main_array['BOX'] 		= "";
					$main_array['ID'] 		= $main->getvar['do'];
										
					echo $style->replaceVar("tpl/user/clientview.tpl", $main_array);							
				}
			break;
			
			case 'search':
				if(isset($main->getvar['do'])) {			
							
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
					$array2['COUNTRY'] 	= strtolower($client['country']);
					$array2['PHONE'] 	= $client['phone'];
					
					$array2['COMPANY'] 	= $client['company'];
					$array2['VATID'] 	= $client['vatid'];
					$array2['FISCALID'] = $client['fiscalid'];

					$user_status_list 	= $main->getUserStatusList();										
					$array2['STATUS']  	= $user_status_list[$client['status']];					
					$array['CONTENT'] 	= $style->replaceVar("tpl/user/clientdetails.tpl", $array2);					
					$array['URL'] 		= URL;
					$array['ID'] 		= $client['id'];
					$array['BOX'] 		= '';
					
					echo $style->replaceVar("tpl/user/clientview.tpl", $array);
					
				} else {
					//selecting all clients
					$array['NAME'] = $db->config("name");
					$array['URL'] = $db->config("url");
					$values[] = array("Admin Area", "admin");
					$values[] = array("Order Form", "order");
					$values[] = array("Client Area", "client");
					$array['DROPDOWN'] = $main->dropDown("default", $values, $db->config("default"));
					echo $style->replaceVar("tpl/user/clientsearch.tpl", $array);			
				}
			break;
			
			
			case 'orders':
				if($main->getvar['do'] ) {					
					$return_array  		= $order->getAllOrdersToArray($main->getvar['do']);	
					$array['CONTENT'] 	=  $style->replaceVar("tpl/orders/client-page.tpl", $return_array);		
					$array['BOX'] 		= "";										
					$array['URL'] 		= URL;
					$array['ID'] 		= $main->getvar['do'];			
					echo $style->replaceVar("tpl/user/clientview.tpl", $array);											
				}			
			break;
			
			case 'invoices':
				if($main->getvar['do'] ) {
					$return_array  		= $invoice->getAllInvoicesToArray($main->getvar['do']);
					$array['CONTENT'] 	=  $style->replaceVar("tpl/invoices/client-page.tpl", $return_array);
					$array['BOX'] 		= "";										
					$array['URL'] 		= URL;
					$array['ID'] 		= $main->getvar['do'];
					
					echo $style->replaceVar("tpl/user/clientview.tpl", $array);	
				}			
			break;			
			
			
			case 'email':
				if($main->getvar['do']) {
					if($_POST && $main->checkToken()) {
						global $email;
						$user_info = $user->getUserById($main->getvar['do']);
						$email->send($user_info['email'] ,$main->postvar['subject'], $main->postvar['content']);
						$main->errors("Email sent");
						$main->generateToken(); // Allow resend an email
					}
					$array['BOX'] = "";
					$array['CONTENT'] = $style->replaceVar("tpl/email/emailclient.tpl");
					$array['ID'] 	  = $main->getvar['do'];
					echo $style->replaceVar("tpl/user/clientview.tpl", $array);
				}
			break;
			
			case 'passwd':
				if($main->getvar['do']) {							
					if($_POST && $main->checkToken()) {
						if(empty($main->postvar['passwd'])) {
							$main->errors('A password was not provided.');
							$array['BOX'] = "";
							$array['CONTENT'] = $style->replaceVar("tpl/user/clientpwd.tpl");
						} else {						
							$command = $user->changeClientPassword($main->getvar['do'], $main->postvar['passwd']);
							if($command === true) {
								$main->errors('Password changed!');
							} else {
								$main->errors((string)$command);
							}
						}
						$main->generateToken(); // Allow resend an email
					}
					$array['ID'] 		= $main->getvar['do'];
					$array['BOX'] = "";
					$array['CONTENT'] = $style->replaceVar("tpl/user/clientpwd.tpl");
					echo $style->replaceVar("tpl/user/clientview.tpl", $array);
				}
			break;	
			
			default:		
			break;
			case 'search':
				if($main->getvar['do'] ) {
					echo $style->replaceVar("tpl/user/clientview.tpl", $array);
				} else {					
					$array['NAME'] = $db->config("name");
					$array['URL'] = $db->config("url");
					$values[] = array("Admin Area", "admin");
					$values[] = array("Order Form", "order");
					$values[] = array("Client Area", "client");
					$array['DROPDOWN'] = $main->dropDown("default", $values, $db->config("default"));
					echo $style->replaceVar("tpl/user/clientsearch.tpl", $array);
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
				
				echo $style->replaceVar("tpl/user/clientstats.tpl", $array);
				break;
			
			
			case 'validate':		
			//code removed from THT
			break;					
		}		
	}
}