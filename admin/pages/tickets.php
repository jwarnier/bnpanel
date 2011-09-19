<?php

/* For licensing terms, see /license.txt */

class page extends Controller {

	public $navtitle;
	public $navlist = array ();
	public $show_content = true;

	public function __construct() { # When class is made, retrieves all details like sending method, details.		
		$this->navtitle = "Tickets Menu";
		$this->navlist[] = array ("Open Tickets","page_white_go.png","view");
		$this->navlist[] = array ("Tickets","page_white_go.png","all");
	}

	public function description() {
		return '';
	}

	public function content() { # Displays the page 
	
		
		global $main, $style, $db, $email, $ticket;
		$main->getvar['do'] = intval($main->getvar['do']);
		
		$ticket_urgency_list = $main->getTicketUrgencyList();
		$ticket_status_list = $main->getTicketStatusList();
		

		if ($main->getvar['sub'] == 'all') {

			$query = $db->query("SELECT * FROM <PRE>tickets WHERE reply = '0' AND status ORDER BY id DESC");
			if (!$db->num_rows($query)) {
				echo "You currently have no new tickets";
			} else {
				echo "<div style=\"display: none;\" id=\"nun-tickets\">You currently have no new tickets!</div>";
				$num_rows = $db->num_rows($query);
				$this->replaceVar("tpl/support/acpticketjs.tpl", array ('NUM_TICKETS' => $num_rows));
				while ($data = $db->fetch_array($query)) {					
					$urg 					= $ticket_urgency_list[$data['urgency']]['color'];
					$array['STATUS_TITLE'] 	= $ticket_status_list[$data['status']];					
					$array['TITLE'] = $data['title'];
					$array['UPDATE'] = $ticket->lastUpdated($data['id']);
					$array['STATUS'] = $ticket_status_list[$data['status']];
					$array['URGCOLOR'] = $urg;
					$array['ID'] = $data['id'];
					$array['STATUS_IMG'] = $data['status'];
					
					$this->replaceVar("tpl/support/acpticketviewbox.tpl", $array);
				}
			}
		} else {
			if (!$main->getvar['do']) {
				$query = $db->query("SELECT * FROM <PRE>tickets WHERE reply = '0' AND status != '3' ORDER BY id DESC");
				if (!$db->num_rows($query)) {
					echo 'No new tickets available';
				} else {
					echo "<div style=\"display: none;\" id=\"nun-tickets\">You currently have no new tickets!</div>";
					$num_rows = $db->num_rows($query);
					$this->replaceVar("tpl/support/acpticketjs.tpl", array ('NUM_TICKETS' => $num_rows));
					
					while ($data = $db->fetch_array($query)) {
						$urg 					= $ticket_urgency_list[$data['urgency']]['color'];
						$array['STATUS_TITLE'] 	= $ticket_status_list[$data['status']];
							
						$array['TITLE'] = $data['title'];
						$array['UPDATE'] = $ticket->lastUpdated($data['id']);
						$array['STATUS'] = $ticket_status_list[$data['status']];
						$array['URGCOLOR'] = $urg;
						$array['ID'] = $data['id'];
						$array['STATUS_IMG'] = $data['status'];
						
						$this->replaceVar("tpl/support/acpticketviewbox.tpl", $array);
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
				
				
				$query = $db->query("SELECT * FROM <PRE>tickets WHERE id = '{$main->getvar['do']}' OR ticketid = '{$main->getvar['do']}' ORDER BY id DESC");
				if (!$db->num_rows($query)) {
					echo "That ticket doesn't exist";
				} else {
					if ($_POST && $main->checkToken()) {					
						$result = $oValidator->validate($_POST);													
						if (empty($result)) {	
							$time = time();

							$staff_id = $main->getCurrentStaffId();

							$ticket_params['title'] 	= $main->postvar['title'];
							$ticket_params['content'] 	= $main->postvar['content'];
							$ticket_params['time'] 		= $time;
							$ticket_params['userid'] 	= $staff_id;
							$ticket_params['reply'] 	= 1;
							$ticket_params['ticketid'] 	= $main->getvar['do'];
							$ticket_params['staff'] 	= 1;
							
							$ticket->create($ticket_params);

							$main->errors("Reply has been added", true);
							//$style->showMessage("Reply has been added");
							
							$data = $db->fetch_array($query);
							$client = $db->staff($staff_id);
							
							$user = $db->client($data['userid']);
							$template = $db->emailTemplate("clientresponse");
							$array['TITLE'] = $data['title'];
							$array['STAFF'] = $client['name'];
							$array['CONTENT'] = $main->postvar['content'];
							$email->send($user['email'], $template['subject'], $template['content'], $array);
							
							$main->redirect("?page=tickets&sub=view&msg=1&do=" . $main->getvar['do']);
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
					$array['STATUS_ID'] = $data['status'];

					$array['REPLIES'] = "";
					$n = 0;
					while ($reply = $db->fetch_array($query)) {
						if (!$n) {
							$array['REPLIES'] .= "<br /><b>Replies</b>";
						}
						$array['REPLIES'] .= $ticket->showReply($reply['id']);
						$n++;
					}

					$array['ADDREPLY'] .= "<br /><b>Change Ticket Status</b>";
					$array3['DROPDOWN'] = $main->createSelect("status", $main->getTicketStatusList(), $data['status'], array('onchange'=>'status('.$data['id'].', this.value)'));
					
					$array3['ID'] = $data['id'];
					$array['ADDREPLY'] .= $style->replaceVar("tpl/support/changestatus.tpl", $array3);

					$array['ADDREPLY'] .= "<br /><b>Add Reply</b>";
					$array2['TITLE'] = "RE: " . $data['title'];
					$array['ADDREPLY'] .= $style->replaceVar("tpl/support/addreply.tpl", $array2);
					$array['ID'] = $data['id'];
					
					$this->replaceVar("tpl/support/viewticket.tpl", $array);
				}
			}
		}
	}
}