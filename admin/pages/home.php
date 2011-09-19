<?php
/* For licensing terms, see /license.txt */

class page extends Controller {	
	/*public function curl_get_content($url="http://thehostingtool.com/updates/version.txt"){  
         $ch = curl_init();
         curl_setopt($ch,CURLOPT_URL, $url);
         curl_setopt($ch,CURLOPT_FRESH_CONNECT,TRUE);
         curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
         curl_setopt($ch,CURLOPT_REFERER,'TheHostingTool Admin Area');
         curl_setopt($ch,CURLOPT_TIMEOUT,10);
         $html=curl_exec($ch);
         if($html==false){
            $m=curl_error(($ch));
            error_log($m);
         }
         curl_close($ch);
         return $html;
    } */
    
	public function content() { 
		global $db, $main, $style, $page, $server, $package;
						
		//$current_version = rtrim($this->curl_get_content('http://thehostingtool.com/updates/version.txt')); #Clears the end whitespace. ARGHHH
		$current_version = '1.3';
		
		$running_version 	= $main->cleanwip($db->config('version'));
		$install_check 		= $main->checkDir(INCLUDES ."../install/");
		$conf_check 		= $main->checkFilePermission(INCLUDES ."conf.inc.php");
		
		if ($current_version == $running_version){
			$updatemsg = "<span style='color:green'>Up-To-Date</span>";
			$upgrademsg = "";
		} elseif($current_version > $running_version){
			$updatemsg = "<span style='color:red'>Upgrade Avaliable</span>";
		    $upgrademsg = "<div class='warn'><img src='../themes/icons/error.png' alt='' /> There is a new version v$current_version avaliable! Please download and upgrade!</div>";
		} elseif($current_version < $running_version){
			$updatemsg = "<span style='color:green'>Dev Area Mode</span>";
			$upgrademsg = "";
		} else {
			$updatemsg = "<span style='color:green'>Up-To-Date</span>";
			$upgrademsg = "";
		}
		
		unset($current_version);
		unset($running_version);
		
		$stats['VERSION'] 	= $db->config('version');
		$stats['THEME'] 	= $db->config('theme');
		$stats['CENABLED'] 	= $main->cleaninteger($db->config('cenabled'));
		$stats['SVID'] 		= $main->cleaninteger($db->config('show_version_id'));
		$stats['SENABLED'] 	= $main->cleaninteger($db->config('senabled'));
		$stats['DEFAULT'] 	= $db->config('default');
		$stats['EMETHOD'] 	= $db->config('emailmethod');
		$stats['SIGNENABLE']= $main->cleaninteger($db->config('general'));
		$stats['MULTI'] 	= $main->cleaninteger($db->config('multiple'));
		$stats['UPDATE'] 	= $updatemsg;
		$stats['UPG_BOX']	= $upgrademsg;
		$stats_box 			= $style->replaceVar('dashboard/stats.tpl', $stats);
		
		$cron = '<a href="'.$db->config('url').'includes/cron.php" target="_blank">Run cron here</a>';
		
		$content = '<br />'.$stats_box.$cron.'<br />';
		
		if ($_POST) {
			foreach($main->postvar as $key => $value) {
				if($value == "" && !$n) {
					$main->errors("Please fill in all the fields!");
					$n++;
				}
			}
			if(!$n) { 
				foreach($main->postvar as $key => $value) {
					$db->updateResource($key, $value);
				}
				$main->errors("Settings Updated!");
				$main->done();
			}
		}
		$array['NOTEPAD'] = $db->resources('admin_notes');
		$content_notepad  = $style->replaceVar('notepad.tpl', $array);
		
		$subdomain_list = $main->getSubDomains();
		
		$server_list = $server->getAllServers();
		
		$todo_content = '';
		
		if (empty($server_list)) {
			$todo_content = $style->returnMessage(_('You need to create a Server <a href="?page=servers&sub=add">here</a>.'), 'warning');			
		}
		
		$package_list = $package->getAllPackages();
		if (empty($package_list)) {
			$todo_content .= $style->returnMessage(_('You need to create a Package <a href="?page=packages&sub=add">here</a>.'), 'warning');
		}		
		
		switch ($db->config('domain_options')) {
			case DOMAIN_OPTION_BOTH:	
			case DOMAIN_OPTION_SUBDOMAIN:
				if (empty($subdomain_list)) {				
					$todo_content .= $style->returnMessage(_('You need to Add subdomains <a href="?page=sub&sub=add">here</a>. Due your current <a href="?page=settings&sub=paths">Subdomain options.</a> Otherwise the Order Form will not work.'), 'warning');					
				}
				break;
			case DOMAIN_OPTION_DOMAIN:
				break;								
		}		
		$todo_content .= $install_check.$conf_check;
		
		if (SERVER_STATUS == 'test') {
			$todo_content .= $style->returnMessage(_('Your Server is in Test Mode, you can manually change <a href="?page=settings&sub=paths">here</a>'), 'warning');
		}
		
		$style->assign('todo', $todo_content);
		$style->assign('notepad', $content_notepad);
		
				
		$content = $style->fetch('admin/home.tpl');
		
		$style->assign('content', $content);		

		
		//Lates commit?
		
		//Temporary code just to see the latest commit
		if (SERVER_STATUS == 'test') {
			$output = array();		
			exec('hg heads', $output);
			$html = '';
			if (isset($output) && is_array($output)) {
				$html .= '<h4>';
				$html .= $output['0'];
				$html .= '<br />';
				$html .= $output['3'];
				$html .= '</h4>';	
			} else {
				$html= 'You can check the latest version here: https://bnpanel.googlecode.com/hg/';
			}
			echo $style->returnMessage($html);
		}
		
		//RSS
		/*		
		require_once(INCLUDES.'rss/rss_fetch.inc');
		$url = "http://thehostingtool.com/forum/syndication.php?fid=2&limit=3";
		$rss = fetch_rss($url);
		$news = $main->sub("<strong>Add the THT RSS Feed!</strong>", '<a href="http://thehostingtool.com/forum/syndication.php?fid=2" target="_blank" class="tooltip" title="Add the THT RSS Feed!"><img src="<URL>themes/icons/feed.png" /></a>');
		foreach ($rss->items as $item) {
			$array['title'] = $item['title'];
			$array['author'] = $item['author'];
			$array['link'] = $item['link'];
			$array['TIME'] = strftime("%D", $item['date_timestamp']);
			$array['SUMMARY'] = $item['summary'];
			$news .= $style->replaceVar('tpl/newsitem.tpl', $array);
		}
		echo "<br />";
		echo $main->table('THT News & Updates', $news);*/
	}
}
