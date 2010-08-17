<?php
/* For licensing terms, see /license.txt */
//Check if called by script
if(THT != 1){die();}

class page {
	
	public $navtitle;
	public $navlist = array();
	public $show_content = true;
		
	public function __construct() { # When class is made, retrieves all details like sending method, details.		
		$this->navtitle = "Tickets Menu";	
		$this->navlist[] = array("View Open Tickets", "page_white_go.png", "view");
		$this->navlist[] = array("View All Tickets", "page_white_go.png", "all");
	}
	
	public function description() {
		return '';	
	}
		
	
	public function content() { # Displays the page 
		global $main, $style, $db, $email, $ticket;
		
		if ($main->getvar['sub'] == 'all') {
			
			$query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `reply` = '0' AND `status` ORDER BY `time` DESC");
			if(!$db->num_rows($query)) {
				echo "You currently have no new tickets!";
			}
			else {
				echo "<div style=\"display: none;\" id=\"nun-tickets\">You currently have no new tickets!</div>";
				$num_rows = $db->num_rows($query);
				echo $style->replaceVar("tpl/support/acpticketjs.tpl", array('NUM_TICKETS' => $num_rows));
				while($data = $db->fetch_array($query)) {
					if($data['urgency'] == "Very High") {
						$urg = " bgcolor=\"#FF0000\">";
					}
					elseif($data['urgency'] == "High") {
						$urg = " bgcolor=\"#FFFF00\">";
					}
					elseif($data['urgency'] == "Medium") {
						$urg = " bgcolor=\"#00FFFF\">";
					}
					else {
						$urg = ">";
					}
					$array['TITLE'] = $data['title'];
					$array['UPDATE'] = $ticket->lastUpdated($data['id']);
					$array['STATUS'] = $data['status'];
					$array['URGCOLOR'] = $urg;
					$array['ID'] = $data['id'];
					echo $style->replaceVar("tpl/support/acpticketviewbox.tpl", $array);
				}				
			}		
		} else  {
		
			if(!$main->getvar['do']) {
				$query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `reply` = '0' AND `status` != '3' ORDER BY `time` DESC");
				if(!$db->num_rows($query)) {
					echo 'You currently have no new tickets!';
				} else {
					echo "<div style=\"display: none;\" id=\"nun-tickets\">You currently have no new tickets!</div>";
					$num_rows = $db->num_rows($query);
					echo $style->replaceVar("tpl/support/acpticketjs.tpl", array('NUM_TICKETS' => $num_rows));
					while($data = $db->fetch_array($query)) {
						if($data['urgency'] == "Very High") {
							$urg = " bgcolor=\"#FF0000\">";
						}
						elseif($data['urgency'] == "High") {
							$urg = " bgcolor=\"#FFFF00\">";
						}
						elseif($data['urgency'] == "Medium") {
							$urg = " bgcolor=\"#00FFFF\">";
						}
						else {
							$urg = ">";
						}
						$array['TITLE'] = $data['title'];
						$array['UPDATE'] = $ticket->lastUpdated($data['id']);
						$array['STATUS'] = $data['status'];
						$array['URGCOLOR'] = $urg;
						$array['ID'] = $data['id'];
						echo $style->replaceVar("tpl/support/acpticketviewbox.tpl", $array);
					}					
				}
			} else {
				$query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `id` = '{$main->getvar['do']}' OR `ticketid` = '{$main->getvar['do']}' ORDER BY `time` ASC");
				if(!$db->num_rows($query)) {
					echo "That ticket doesn't exist!";	
				} else {
					if($_POST && $main->checkToken()) {
						foreach($main->postvar as $key => $value) {
							if($value == "" && !$n && $key != "admin") {
								$main->errors("Please fill in all the fields!");
								$n++;
							}
						}
						if(!$n) {
							$time = time();
							$staff_id = $main->getCurrentStaffId();
							$db->query("INSERT INTO `<PRE>tickets` (title, content, time, userid, reply, ticketid, staff) VALUES('{$main->postvar['title']}', '{$main->postvar['content']}', '{$time}', '{$staff_id}', '1', '{$main->getvar['do']}', '1')");
							$main->errors("Reply has been added!");
							$data = $db->fetch_array($query);
							$client = $db->staff($staff_id);
							$user = $db->client($data['userid']);
							$template = $db->emailTemplate("clientresponse");
							$array['TITLE'] = $data['title'];
							$array['STAFF'] = $client['name'];
							$array['CONTENT'] = $main->postvar['content'];
							$email->send($user['email'], $template['subject'], $template['content'], $array);
							$main->redirect("?page=tickets&sub=view&do=". $main->getvar['do']);
						}
					}
					$data = $db->fetch_array($query);
					$array['AUTHOR'] = $ticket->determineAuthor($data['userid'], $data['staff']);
					$array['TIME'] = strftime("%D", $data['time']);
					$array['NUMREPLIES'] = $db->num_rows($query) - 1;
					$array['UPDATED'] = $ticket->lastUpdated($data['id']);
					$array['ORIG'] = $ticket->showReply($data['id']);
					$array['URGENCY'] = $data['urgency'];
					$array['STATUS'] = $ticket->status($data['status']);
					
					$array['REPLIES'] = "";
					$n = 0;
					while($reply = $db->fetch_array($query)) {
						if(!$n) {
							$array['REPLIES'] .= "<br /><b>Replies</b>";
						}
						$array['REPLIES'] .= $ticket->showReply($reply['id']);
						$n++;
					}
					
					$array['ADDREPLY'] .= "<br /><b>Change Ticket Status</b>";
					$values[] = array("Open", 1);
					$values[] = array("On Hold", 2);
					$values[] = array("Closed", 3);
					$array3['DROPDOWN'] = $main->dropdown("status", $values, $data['status'], 0);
					$array3['ID'] = $data['id'];
					$array['ADDREPLY'] .= $style->replaceVar("tpl/support/changestatus.tpl", $array3);
					
					$array['ADDREPLY'] .= "<br /><b>Add Reply</b>";
					$array2['TITLE'] = "RE: ". $data['title'];
					$array['ADDREPLY'] .= $style->replaceVar("tpl/support/addreply.tpl", $array2);
					
					echo $style->replaceVar("tpl/support/viewticket.tpl", $array);	
				}
			}
		}
	}
}
