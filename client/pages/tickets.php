<?php
/* For licensing terms, see /license.txt */

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Tickets Menu";
		$this->navlist[] = array("New Ticket", "page_white_add.png", "add");
		$this->navlist[] = array("View Tickets", "page_white_go.png", "view");
	}
	
	public function description() {
		return "<strong>Tickets Area</strong><br />
		This is the area where you can add/view tickets that you've created or just created. Any tickets, responses will be sent via email.";	
	}
	
	public function content() { # Displays the page 
		global $main, $style, $db, $email, $ticket;
		
		require_once LINK.'validator.class.php';
		
		$ticket_urgency_list = $main->getTicketUrgencyList();
		$ticket_status_list = $main->getTicketStatusList();
		
		$user_id = $main->getCurrentUserId();
		$action = isset($main->getvar['sub']) ? $main->getvar['sub'] : 'view';
		
		switch($action) {
			case 'add':
				$asOption = array(
				    'rules' => array(
				        'title' 		=> 'required',
				        'urgency' 		=> 'required',
				        'content'		=> 'required'			            
				     ),			    
				     'messages' => array(   			       
					    )				    
				);	
				
				$array['json_encode'] = json_encode($asOption);				
				$oValidator = new Validator($asOption);		
				
				if($_POST && $main->checkToken()) {
					$result = $oValidator->validate($_POST);	
										
					if (empty($result)) {
						$time = time();
						
						$ticket_params['title']		= $main->postvar['title'];
						$ticket_params['content'] 	= $main->postvar['content'];
						$ticket_params['urgency'] 	= $main->postvar['urgency'];
						$ticket_params['time'] 		= $time;
						$ticket_params['userid'] 	= $user_id;
						$ticket_id = $ticket->create($ticket_params);						
						
						$main->errors(_('Ticket has been added'), true);
						$template = $db->emailTemplate("new_ticket");
						$array['TITLE'] 	= $main->postvar['title'];
						$array['URGENCY'] 	= $main->postvar['urgency'];
						$array['CONTENT'] 	= $main->postvar['content'];
						$array['ID'] = $ticket_id;
						$email->staff($template['subject'], $template['content'], $array);
						$main->redirect('?page=tickets&sub=view&msg=1');
					}
				}
				$array['URGENCY'] = $main->createSelect('urgency', $main->getTicketUrgencyList());
				echo $style->replaceVar("tpl/support/addticket.tpl", $array);
				break;
			default:
			case 'view':				
				if(!isset($main->getvar['do']) && $main->checkToken()) {
					$query = $db->query("SELECT * FROM <PRE>tickets WHERE userid = '{$user_id}' AND reply = '0' ORDER BY id DESC");
					if(!$db->num_rows($query)) {
						$style->showMessage('No open tickets');						
					} else {	
						echo '<ERRORS>'; 					
						while($data = $db->fetch_array($query)) {
							$array['TITLE'] = $data['title'];
							$array['UPDATE'] = $ticket->lastUpdated($data['id']);
							$array['ID'] = $data['id'];
							$array['STATUS'] = $data['status'];
							echo $style->replaceVar("tpl/support/ticketviewbox.tpl", $array);
						}
					}
				} else {					
					$asOption = array(
					    'rules' => array(
					        'title' 		=> 'required',					        
					        'content'		=> 'required'			            
					     ),			    
					     'messages' => array(   			       
						)				    
					);	
					
					$array2['json_encode'] = json_encode($asOption);				
					$oValidator = new Validator($asOption);		
					
					$query = $db->query("SELECT * FROM <PRE>tickets WHERE id = '{$main->getvar['do']}' OR ticketid = '{$main->getvar['do']}' ORDER BY time ASC");
					if(!$db->num_rows($query)) {
						echo "That ticket doesn't exist";	
					} else {
						if($_POST && $main->checkToken()) {
							$result = $oValidator->validate($_POST);	
										
							if (empty($result)) {
								$time = time();
								
								$ticket_params['title']		= $main->postvar['title'];
								$ticket_params['content'] 	= $main->postvar['content'];
								$ticket_params['time'] 		= $time;
								$ticket_params['userid'] 	= $user_id;
								$ticket_params['reply'] 	= 1;
								$ticket_params['ticketid'] 	= $main->getvar['do'];
								
								$ticket->create($ticket_params);					
								
								$main->errors(_("Reply has been added"),true);
								$data = $db->fetch_array($query);
								$client = $db->client($user_id);
								$template = $db->emailTemplate("new_response");
								$array['TITLE'] = $data['title'];
								$array['USER'] = $client['user'];
								$array['CONTENT'] = $main->postvar['content'];
								$email->staff($template['subject'], $template['content'], $array);
								
								$main->redirect("?page=tickets&sub=view&msg=1&do=". $main->getvar['do']);
							}
						}
						$data = $db->fetch_array($query);
						$array['AUTHOR'] = $ticket->determineAuthor($data['userid'], $data['staff']);
						$array['TIME'] = strftime("%D", $data['time']);
						$array['NUMREPLIES'] = $db->num_rows($query) - 1;
						$array['UPDATED'] = $ticket->lastUpdated($data['id']);
						//$array['ORIG'] = $ticket->showReply($data['id']);					
					
						$ticket_info = $ticket->find($data['id']);
						$array['TITLE'] = $ticket_info->title;
						$array['DESCRIPTION'] = $ticket_info->content;
						
						$array['URGENCY'] = $ticket_urgency_list[$data['urgency']]['name'];
						
						$array['STATUS'] = $ticket_status_list[$data['status']];
						
						$n = 0;
						$array['REPLIES'] = "";
						while($reply = $db->fetch_array($query)) {
							if(!$n) {
								$array['REPLIES'] .= "<b>History</b>";
							}
							$array['REPLIES'] .= $ticket->showReply($reply['id']);
							$n++;
						}
						
						if($data['status'] != 3) {
							$array['ADDREPLY'] .= "<br /><b>Add Reply</b>";
							$array2['TITLE'] = "RE: ". $data['title'];
							$array['ADDREPLY'] .= $style->replaceVar("tpl/support/addreply.tpl", $array2);
						}
						else {
							$array['ADDREPLY'] = "";	
						}						
						$array['ID'] = $data['id'];
						echo $style->replaceVar("tpl/support/viewticket.tpl", $array);	
					}
				}
				break;
		}
	}
}