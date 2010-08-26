<?php
//////////////////////////////
// The Hosting Tool
// cPanel/WHM Server Class
// By Jonny H and Kevin M
// Released under the GNU-GPL
//////////////////////////////

class whm extends Panel {
	
	# START THE MO TRUCKIN FUNCTIONS #
	
	public $name = "cPanel/WHM"; # THT Values
	public $hash = true; # Password or Access Hash?
	
	private $server_id;
	
	private function remote($url, $xml = 0, $term = false) {
                global $db;
		$data = $this->serverDetails($this->server_id);
		//Curl Script done by Krakjoe and Kevin, Thanks.
		$cleanaccesshash = preg_replace("'(\r|\n)'","",$data['accesshash']);
		$authstr = $data['user'] . ":" . $cleanaccesshash;
		$ch = curl_init();
                if($db->config("whm-ssl") == 1) {
                    $serverstuff = "https://" . $data['host'] . ":2087" . $url;
                    curl_setopt($ch, CURLOPT_URL, $serverstuff);
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }
                else {
                    $serverstuff = "http://" . $data['host'] . ":2086" . $url;
                    curl_setopt($ch, CURLOPT_URL, $serverstuff);
                }
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$curlheaders[0] = "Authorization: WHM $authstr";
		curl_setopt($ch,CURLOPT_HTTPHEADER,$curlheaders);
		$data = curl_exec ($ch);
		curl_close ($ch);
		//END
                if($term == true) {
                    return true;
                }
		elseif(!$xml) {
			$xml = new SimpleXMLElement($data);
		}
		else {
			return $data;
		}
		return $xml;
	}
	
	public function signup($order_id, $user = '', $email = '', $pass = '') {
		global $main, $db, $package, $order;
		
		$order_info		= $order->getOrderInfo($order_id);
		$package_info 	= $package->getPackage($order_info['pid']);		
		
		//The server::signup function generates the username + password for the Control Panel
		/*if ($user == '') { $user = $main->getvar['username']; }
		if ($email == '') { $email = $main->getvar['email']; }
		if ($pass == '') { $pass = $main->getvar['password']; }
		
		$user = $this->GenUsername();
		$pass = $this->GenPassword();
		*/
		
		$this->server_id = $package_info['server'];
		$action = "/xml-api/createacct".
					"?username=". $user . "".
					"&password=". $pass ."".
					"&domain=". $main->getvar['fdom'] ."".
					"&plan=". $main->getvar['fplan'] ."".
					"&contactemail=". $email ."";
		if($package_info['reseller']) {
			$action .= "&reseller=1";	
		}
		//echo $action."<br />". $reseller;
		$command = $this->remote($action);
		
		
		if($command->result->status == 1) {
			
			$params['username'] = $user;
			$params['password'] = $pass;
			$order->edit($order_id, $params);			
			return true;	
		}
		else {
			echo "Error: ". $command->result->statusmsg;	
		}
	}
	
	public function suspend($order_id, $server, $reason = false) {
		global $order, $user;
		$order_info = $order->getOrderInfo($order_id);
		$user_info	= $user->getUserById($order_info['userid']);
		$this->server_id = $server;
		$action = "/xml-api/suspendacct?user=" . strtolower($user_info['user']);
		$command = $this->remote($action);
                if($reason == false) {
                    $command = $this->remote($action);
                }
                else {
                    $command = $this->remote($action . "&reason=" . str_replace(" ", "%20", $reason));
                }
		if($command->result->status == 1) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function unsuspend($order_id, $server) {		
		global $main, $db, $order, $user;
		$order_info = $order->getOrderInfo($order_id);
		$user_info	= $user->getUserById($order_info['userid']);
		$this->server_id = $server;
		$action = "/xml-api/unsuspendacct?user=" . strtolower($user_info['user']);
		$command = $this->remote($action);
		if($command->result->status == 1) {
			return true;
		}
		else {
			return false;
		}
	}
	public function terminate($user, $server) {
		$this->server_id = $server;
		$action = "/xml-api/removeacct?user=" . strtolower($user);
		$command = $this->remote($action, 0, true);
		if($command == true) {
			return true;
		}
		else {
			return false;
		}
	}
	public function listaccs($server) {
		$this->server_id = $server;
		$action = "/xml-api/listaccts";
		$command = $this->remote($action, 1);
		$xml = new DOMDocument();
		$xml->loadXML($command);
		$list = $xml->getElementsByTagName('user');
		//This code underneath taken from http://www.phpclasses.org/browse/file/20658.html CBA to code my own =]
		$i=0;
		foreach ($list AS $element)
		{
			foreach ($element->childNodes AS $item)
			{
				$result[$i]['user']=$item->nodeValue;
				$i++;
			}
		}

		$list = $xml->getElementsByTagName('domain');
		$i=0;
		foreach ($list AS $element)
		{
			foreach ($element->childNodes AS $item)
			{
				$result[$i]['domain']=$item->nodeValue;
				$i++;
			}
		}

		$list = $xml->getElementsByTagName('plan');
		$i=0;
		foreach ($list AS $element)
		{
			foreach ($element->childNodes AS $item)
			{
				$result[$i]['package']=$item->nodeValue;
				$i++;
			}
		}

		$list = $xml->getElementsByTagName('unix_startdate');
		$i=0;
		foreach ($list AS $element)
		{
			foreach ($element->childNodes AS $item)
			{
				$result[$i]['start_date']=$item->nodeValue;
				$i++;
			}
		}
		
		$list = $xml->getElementsByTagName('email');
		$i=0;
		foreach ($list AS $element)
		{
			foreach ($element->childNodes AS $item)
			{
				$result[$i]['email']=$item->nodeValue;
				$i++;
			}
		}
		//return the result array
		return $result;
	}
	public function changePwd($acct, $newpwd, $server)
	{
		$this->server_id = $server;
		$action = '/xml-api/passwd?user=' . $acct . '&pass=' . $newpwd;
		$command = $this->remote($action);
		if($command->passwd->status == 1) {
			return true;
		}
		else {
			if(isset($command->passwd->statusmsg)) {
				return $command->passwd->statusmsg;
			}
			else {
				return false;
			}
		}
	}
}