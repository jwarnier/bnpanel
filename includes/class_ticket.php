<?php
/* For licensing terms, see /license.txt */


//Check if called by script
if(THT != 1){die();}

class ticket {	
	
	public function __construct() { # When class is made, retrieves all details like sending method, details.


	}
	
	public function showReply($id) { # Returns the HTML for a ticket box
		global $db, $main, $style;
		$query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `id` = '{$id}'");
		$data = $db->fetch_array($query);
		$array['AUTHOR'] = $this->determineAuthor($data['userid'], $data['staff']);
		$array['CREATED'] = "Posted on: ". strftime("%D at %T", $data['time']);
		$array['REPLY'] = $data['content'];
		$array['TITLE'] = $data['title'];
		$orig = $db->query("SELECT * FROM `<PRE>tickets` WHERE `id` = '{$data['ticketid']}'");
		$dataorig = $db->fetch_array($orig);
		if($dataorig['userid'] == $data['userid']) {
			$array['DETAILS'] = "Original Poster";	
		}
		elseif($data['staff'] == 1) {
			$array['DETAILS'] = "Staff Member";
		}
		else {
			$array['DETAILS'] = "";	
		}
		return $style->replaceVar("tpl/support/replybox.tpl", $array);
	}
	
	
	public function lastUpdated($id) { # Returns a the date of last updated on ticket id
		global $db;
		$query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `ticketid` = '{$db->strip($id)}' AND `reply` = '1' ORDER BY `time` DESC");
		if(!$db->num_rows($query)) {
			return "None";	
		}
		else {
			$data = $db->fetch_array($query);
			$username = $this->determineAuthor($data['userid'], $data['staff']);
			return strftime("%D - %T", $data['time']) ." by ". $username;
		}
	}

	
	public function determineAuthor($id, $staff) { # Returns the text of the author of a reply
		global $db;
		switch($staff) {
			case 0:
				$client = $db->client($id);
				$username = $client['user'];
				break;
				
			case 1:
				$client = $db->staff($id);
				$username = $client['name'];
				break;
		}
		return $username;
	}
	
	
	public function status($status) { # Returns the text of the status
		switch($status) {
			default:
				return "Other";
				break;
			
			case 1:
				return "Open";
				break;
				
			case 2:
				return "On Hold";
				break;
				
			case 3:
				return "Closed";
				break;
		}
	}
}