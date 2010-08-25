<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){die();}

class page {
	
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
		global $db,$main, $style, $page;	
				
		//$current_version = rtrim($this->curl_get_content('http://thehostingtool.com/updates/version.txt')); #Clears the end whitespace. ARGHHH
		$current_version = '1.3';
		
		$running_version 	= $main->cleanwip($db->config('version'));
		$install_check 		= $main->checkDir(LINK ."../install/");
		$conf_check 		= $main->checkFilePermission(LINK ."conf.inc.php");
		
		if($current_version == $running_version){
			$updatemsg = "<span style='color:green'>Up-To-Date</span>";
			$upgrademsg = "";
		}
		elseif($current_version > $running_version){
			$updatemsg = "<span style='color:red'>Upgrade Avaliable</span>";
		    $upgrademsg = "<div class='warn'><img src='../themes/icons/error.png' alt='' /> There is a new version v$current_version avaliable! Please download and upgrade!</div>";
		}
		elseif($current_version < $running_version){
			$updatemsg = "<span style='color:green'>Dev Area Mode</span>";
			$upgrademsg = "";
		}
		else{
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
		$stats_box = $style->replaceVar('tpl/dashboard/stats.tpl', $stats);
		
		$cron ='<a href="'.$db->config('url').'includes/cron.php" target="_blank">Run cron here</a>';
		
		$content = '<strong>Welcome to your Admin Dashboard!</strong><br />Welcome to the dashboard of your Admin Control Panel. In this area you can do the tasks that you need to complete such as manage servers, create packages, manage users.<br />
					Here, you can also change the look and feel of your BNPanel Installation. If you require any help, be sure to ask at the <a href="http://www.beeznest.com" title="BNPanel Community is the official stop for BNPanel Support, Modules, Developer Center and more! Visit our growing community now!" class="tooltip">BNPanel Community</a>' .
					'<br />'.$stats_box.$cron.'<br /></div></div>';	
		
		if($_POST) {
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
		$content_notepad  = $style->replaceVar('tpl/notepad.tpl', $array);
		
		$subdomain_list = $main->getSubDomains();
		
		switch($db->config('domain_options')) {
			case DOMAIN_OPTION_BOTH:	
			case DOMAIN_OPTION_SUBDOMAIN:
			if (empty($subdomain_list)) {				
				$todo_content = $style->returnMessage('You need to Add subdomains <a href="?page=sub&sub=add">here</a>. Due your current <a href="?page=settings&sub=paths">Subdomain options.</a> The Order Form will not work.', 'warning');					
			}
			break;
			case DOMAIN_OPTION_DOMAIN:
			break;								
		}		
		$todo_content .= $install_check.$conf_check;
		
		echo $content;		
		if (!empty($todo_content)) {
			echo $main->table('Admin TODO List', $todo_content, 'auto', 'auto');
		}		
		
		echo '<br />';
		echo $main->table('Admin Notepad', $content_notepad, 'auto', 'auto');
		
		//Temporaly code just to see the lastest commit
		if (SERVER_STATUS == 'test') {
			$output = array();		
			exec('hg heads', $output);
			if (isset($output)) {
				echo '<h3>';
				echo $output['0'];
				echo '<br />';
				echo $output['3'];
				echo '</h3>';	
			} else {
				echo 'Seems that there is not a hg repo ... ';
			}
		}
		
		/*
		
		require_once(LINK.'rss/rss_fetch.inc');
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