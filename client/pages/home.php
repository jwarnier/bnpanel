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
		$lquery = $db->query("SELECT * FROM `<PRE>logs` WHERE `uid` = '{$user_id}' AND `message` LIKE 'Login%' ORDER BY `id` DESC LIMIT 2,1");
		$ldata = $db->fetch_array($lquery);
		$array['LASTLOGIN'] = $ldata['message'];
		$array['LASTDATE'] = strftime("%m/%d/%Y", $ldata['logtime']);
		$array['LASTTIME'] = strftime("%T", $ldata['logtime']);
		$array['EMAIL'] = $data['email'];
		$array['ALERTS'] = $db->config('alerts');
		/*
		$query2 = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$db->strip($data['id'])}'");
		$data3 = $db->fetch_array($query2);
		$query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$db->strip($data3['pid'])}'");
		$data2 = $db->fetch_array($query);
		$array['PACKAGE'] = $data2['name'];
		
		
		$invoicesq = $db->query("SELECT * FROM `<PRE>invoices` WHERE `uid` = '{$db->strip($data['id'])}' AND `is_paid` = '0'");
		
		$invoice_list = $invoice->getInvoicesByUser($data['id']);
		
		$array['INVOICES'] = $db->num_rows($invoicesq);
		switch($data3['status']) {			
			case "1":
				$array['STATUS'] = "Active";
				break;
				
			case "2":
				$array['STATUS'] = "Suspended";
				break;
				
			case "3":
				$array['STATUS'] = "Awaiting Admin";
				break;
			
			case "4":
				$array['STATUS'] = "Awaiting Payment";
				break;
			
			case "9":
				$array['STATUS'] = "Cancelled";
				break;
		}
		$classname = $type->determineType($data3['pid']);
		$phptype = $type->classes[$classname];
		if($phptype->clientBox) {
			$box = $phptype->clientBox();	
			$array['BOX'] = $main->sub($box[0], $box[1]);
		}
		else {
			$array['BOX'] = "";	
		}*/
		$array['BOX'] = "";	
		
		$alerts = $db->config('alerts');
		$array['ALERTS'] = '';
		if (!empty($alerts)) {
			$array['ALERTS'] = '<h2>Important  Announcements </h2>'.$db->config('alerts').'<br />';
		}		
			
		echo $style->replaceVar("tpl/user/clienthome.tpl", $array);
	}
}