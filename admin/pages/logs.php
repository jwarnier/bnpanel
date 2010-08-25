<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){die();}
define("PAGE", "Logs");
class page {
	
	public function content() { # Displays the page 
		global $style, $db, $main;		

		$l = intval($main->getvar['l']);		
		$p = intval($main->getvar['p']);	
			
		$show_values = array('all','Approved','Unsuspended','Registered', 'Package created','Approved', 'Declined',
				'Suspended', 'Cancelled', 'Terminated','cPanel password', 'Login','Login successful', 'Login failed','STAFF', 'STAFF LOGIN SUCCESSFUL','STAFF LOGIN FAILED');
			
		if (!$main->postvar['show']) {
			if (in_array($main->getvar['show'], $show_values)) {
				$show = $main->getvar['show'];
			} else {
				$show = "all";	
			}
		} else {
			$show = $main->postvar['show'];
			$p = 0;
		}
		
		if (isset($main->postvar['clean'])) {
			$db->query('TRUNCATE <pre>logs');			
		}
		
		echo '<div class="subborder">';		
		echo '<form id="filter" name="filter" method="post" action="">';
		
		$values = array(
			'all' =>'ALL',
			'Registered' =>'Registered',
			'Package created' =>'Package created',
			'Suspended' =>'Suspended',
			'Unsuspended' =>'Unsuspended',
			'Cancelled' =>'Cancelled',
			'Terminated' =>'Terminated',
			'Password' =>'Password',
			'Login' =>'lient Logins (Success/Fail)',
			'Login successful' =>'Client Logins (Success)',
			'Login failed' =>'Client Logins (Fail)',
			'STAFF' =>'Staff Logins (Success/Fail)',
			'STAFF LOGIN SUCCESSFUL' =>'Staff Logins (Success)',
			'STAFF LOGIN FAILED' =>'Staff Logins (Fail)',			
		);
		
		echo $main->createSelect('show', $values, $show);
				
		echo '<input type="submit" name="filter" id="filter" value="Filter Log" />';
			
		echo '<input type="submit" name="clean" id="clean" value="Clean Logs" />';		
		
		echo "</form>";
				
		echo '<table width="100%" class="content_table" cellspacing="2" cellpadding="2"  ><tr bgcolor="#EEEEEE">';
		echo "<th width=\"75\" align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">Date</td>";
		echo "<th width=\"60\" align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">Time</td>";
		echo "<th width=\"75\" align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">Username</td>"; 
		echo "<th align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">Message</td></tr>";
		
		
		if (!($l)) {
			$l = 50;
		}
		if (!($p)) {
			$p = 0;
		}
		
	
		
		if ($show != 'all') {
			$show  = $db->strip($show);
			$query = $db->query("SELECT * FROM `<PRE>logs` WHERE `message` LIKE '$show%'");
		} else {
			$query = $db->query("SELECT * FROM `<PRE>logs`");
		}
		$pages = intval($db->num_rows($query)/$l);
				
		if ($db->num_rows($query)%$l) {
			$pages++;
		}
		$current = ($p/$l) + 1;
		if (($pages < 1) || ($pages == 0)) {
			$total = 1;
		}
		else {
			$total = $pages;
		}
		$first = $p + 1;
		if (!((($p + $l) / $l) >= $pages) && $pages != 1) {
			$last = $p + $l;
		} else {
			$last = $db->num_rows($query);
		}
		if ($db->num_rows($query) == 0) {
			$style->showMessage("No logs found.");
		} else {
			if ($show != all) {
				$sql = "SELECT * FROM `<PRE>logs` WHERE `message` LIKE '$show%' ORDER BY `id` DESC LIMIT $p, $l";			
			} else {
				$sql = "SELECT * FROM `<PRE>logs` ORDER BY `id` DESC LIMIT $p, $l";
			}
			
			$query2 = $db->query($sql);
			while($data = $db->fetch_array($query2)) {
				$array['USER'] = $data['loguser'];
				$array['DATE'] = strftime("%m/%d/%Y", $data['logtime']);
				$array['TIME'] = strftime("%T", $data['logtime']);
				$array['MESSAGE'] = $data['message'];
			echo $style->replaceVar("tpl/settings/adminlogs.tpl", $array);
			}
		}
		echo "</table></div>";
		echo "<center>";
		
		$url = $db->config('url');
		$url = $url.'admin';
		
		if ($p != 0) {
			$back_page = $p - $l;
			echo("<a href=\"$url?page=logs&show=$show&p=$back_page&l=$l\">BACK</a>    \n");
		}

		for ($i=1; $i <= $pages; $i++) {
			$ppage = $l*($i - 1);
			if ($ppage == $p){
				echo("<b>$i</b>\n");
			} else{
				echo("<a href=\"$url?page=logs&show=$show&p=$ppage&l=$l\">$i</a> \n");
			}
		}

		if (!((($p+$l) / $l) >= $pages) && $pages != 1) {
			$next_page = $p + $l;
			echo("    <a href=\"$url?page=logs&show=$show&p=$next_page&l=$l\">NEXT</a>");
		}
		echo "</center>";
	}
}