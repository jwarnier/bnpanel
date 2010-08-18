<?php
/* For licensing terms, see /license.txt */
//Check if called by script
if(THT != 1){die();}

class page {
	
	public function content() { # Displays the page 
		global $style, $db, $main, $type;
		$user_id = $main->getCurrentUserId();
		$data = $db->client($user_id);
		$query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `reply` = '0' AND `userid` = '{$user_id}'");
		$array['TICKETS'] = $db->num_rows($query);
		$query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `reply` = '0' AND `userid` = '{$user_id}' AND `status` = '1'");
		$array['OPENTICKETS'] = $db->num_rows($query);
		$query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `reply` = '0' AND `userid` = '{$user_id}' AND `status` = '3'");
		$array['CLOSEDTICKETS'] = $db->num_rows($query);
		$array['DATE'] = strftime("%D", $data['signup']);
		
		//$lquery = $db->query("SELECT * FROM `<PRE>logs` WHERE `uid` = '{$user_id}' AND `message` LIKE 'Login%' ORDER BY `id` DESC LIMIT 2,1");
		//$ldata = $db->fetch_array($lquery);
		
		//$array['LASTLOGIN'] = "";$ldata['message'];
		//$array['LASTDATE'] = strftime("%m/%d/%Y", $ldata['logtime']);
		//$array['LASTTIME'] = strftime("%T", $ldata['logtime']);
		$array['EMAIL'] = $data['email'];
		$array['ALERTS'] = $db->config('alerts');		
		$array['BOX'] = "";	
		
		$alerts = $db->config('alerts');		
		$array['ALERTS'] = '';
		if (!empty($alerts)) {
			$array['ALERTS'] = '<h3>Important Announcements </h3>'.$db->config('alerts').'';
		}		
			
		echo $style->replaceVar("tpl/user/clienthome.tpl", $array);
	}
}