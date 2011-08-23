<?php
/* For licensing terms, see /license.txt */

class page {	
	public function content() {
		global $style, $db, $main, $user;
		
		$data = $user->getUserById($main->getCurrentUserId());
				
		$array['USER']		= $data['user'];
		$array['EMAIL'] 	= $data['email'];
		//$array['DOMAIN'] 	= $data['domain'];
		$array['FIRSTNAME'] = $data['firstname'];
		$array['LASTNAME']	= $data['lastname'];
		$array['COMPANY']	= $data['company'];
		$array['VATID']		= $data['vatid'];
		$array['FISCALID']	= $data['fiscalid'];
		$array['ADDRESS'] 	= $data['address'];
		$array['CITY']		= $data['city'];
		$array['STATE'] 	= $data['state'];
		$array['ZIP'] 		= $data['zip'];
		$array['COUNTRY'] 	= strtolower($data['country']);
		$array['PHONE'] 	= $data['phone'];
		
		if (isset($main->getvar['sub']) && $main->getvar['sub'] != 'edit') {			
			echo $style->replaceVar("tpl/user/client_view.tpl", $array);
		} else {
			$array['DISP'] 		= "<div>";			
			if($_POST && $main->checkToken(true)) {
				$main->generateToken();
				if(!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',$main->postvar['email'])) {
					$main->errors("Your email is the wrong format!");				
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				$main->postvar['email'] = $db->strip($main->postvar['email']);
				$query = $db->query("SELECT * FROM `<PRE>users` WHERE `email` = '{$main->postvar['email']}' AND `id` != '{$data['id']}'");
				if($db->num_rows($query) != 0) {
					$main->errors("That e-mail address is already in use!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if(!$main->postvar['state']) {
					$main->errors("Please enter a valid state!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if (!preg_match("/^([a-zA-Z\.\ -])+$/",$main->postvar['state'])) {
					$main->errors("Please enter a valid state!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if(!$main->postvar['address']) {
					$main->errors("Please enter a valid address!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if(!preg_match("/^([0-9a-zA-Z\.\ \-])+$/",$main->postvar['address'])) {
					$main->errors("Please enter a valid address!");
					echo $style->replaceVar("tpl/user/user/cedit.tpl", $array);
					return;
				}
				if(!$main->postvar['phone']) {
					$main->errors("Please enter a valid phone number!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if (!preg_match("/^([0-9\-])+$/",$main->postvar['phone'])) {
					$main->errors("Please enter a valid phone number!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if(strlen($main->postvar['phone']) > 15) {
					$main->errors("Phone number is to long!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if(!$main->postvar['zip']) {
					$main->errors("Please enter a valid zip/postal code!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if(strlen($main->postvar['zip']) > 7) {
					$main->errors("Zip/postal code is to long!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if (!preg_match("/^([0-9a-zA-Z\ \-])+$/",$main->postvar['zip'])) {
					$main->errors("Please enter a valid zip/postal code!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if(!$main->postvar['city']) {
					$main->errors("Please enter a valid city!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				if (!preg_match("/^([a-zA-Z ])+$/",$main->postvar['city'])) {
					$main->errors("Please enter a valid city!");
					echo $style->replaceVar("tpl/user/cedit.tpl", $array);
					return;
				}
				
				$user->edit($data['id'], $main->postvar);
					
				if ($main->postvar['change']) {
					$data = $db->client($data['id']);
					if (md5(md5($main->postvar['currentpass']) . md5($data['salt'])) == $data['password']) {
						if($main->postvar['newpass'] === $main->postvar['cpass']) {
						$cmd = $user->changeClientPassword($data['id'], $main->postvar['newpass']);
						if($cmd === true) {
							$main->errors("Details updated");
						} else {
							$main->errors((string)$cmd);						
						}
					} else {
							$main->errors("Your passwords don't match");						
						}
					} else {
						$main->errors("Your current password is incorrect.");					
					}
				} else {
					$array['DISP'] = "<div style=\"display:none;\">";
					$main->errors("Details updated");					
				}
				$main->redirect('?page=details&sub=view&msg=1');
				
			}		
			echo $style->replaceVar("tpl/user/cedit.tpl", $array);
		}
	}	
}