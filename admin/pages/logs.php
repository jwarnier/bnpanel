<?php
/* For licensing terms, see /license.txt */

//Check if called by script
if(THT != 1){die();}
define("PAGE", "Logs");
class page {	
	public function content() { # Displays the page 
		global $style, $db, $main;		
		echo "<div class=\"subborder\">";
		
		echo "<form id=\"filter\" name=\"filter\" method=\"post\" action=\"\">";
		
		echo '<select size="1" name="show">';
		
		echo "<option value=\"all\">ALL</option>";
		echo "<option value=\"Registered\">Registered</option>";
		echo "<option value=\"Package created\">Package created</option>";		
		echo "<option value=\"Approved\">Approved</option>";
		echo "<option value=\"Declined\">Declined</option>";
		echo "<option value=\"Suspended\">Suspended</option>";
		echo "<option value=\"Unsuspended\">Unsuspended</option>";
		echo "<option value=\"Cancelled\">Cancelled</option>";
		echo "<option value=\"Terminated\">Terminated</option>";
		echo "<option value=\"cPanel password\">Control Panel password change</option>";
		echo "<option value=\"Login\">Client Logins (Success/Fail)</option>";
		echo "<option value=\"Login successful\">Client Logins (Success)</option>";
		echo "<option value=\"Login failed\">Client Logins (Fail)</option>";
		echo "<option value=\"STAFF\">Staff Logins (Success/Fail)</option>";
		echo "<option value=\"STAFF LOGIN SUCCESSFUL\">Staff Logins (Success)</option>";
		echo "<option value=\"STAFF LOGIN FAILED\">Staff Logins (Fail)</option></select>";
		
		echo "<input type=\"submit\" name=\"filter\" id=\"filter\" value=\"Filter Log\" />";
		
		echo "</form>";
				
		echo "<table width=\"100%\" cellspacing=\"2\" cellpadding=\"2\" border=\"1\" style=\"border-collapse: collapse\" bordercolor=\"#000000\"><tr bgcolor=\"#EEEEEE\">";
		echo "<td width=\"75\" align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">DATE</td>";
		echo "<td width=\"60\" align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">TIME</td>";
		echo "<td width=\"75\" align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">USERNAME</td>"; 
		echo "<td align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">MESSAGE</td></tr>";
		
		$l = intval($main->getvar['l']);		
		$p = intval($main->getvar['p']);		
		$show_values = array('all','Approved','Unsuspended','Registered', 'Package created','Approved', 'Declined',
				'Suspended', 'Cancelled', 'Terminated','cPanel password', 'Login','Login successful', 'Login failed','STAFF', 'STAFF LOGIN SUCCESSFUL','STAFF LOGIN FAILED');
		
		if (!in_array($main->postvar['show'], $show_values)) {
			$show = "all";
		}
		
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
		
		if (!($l)) {
			$l = 50;
		}
		if (!($p)) {
			$p = 0;
		}
		if ($show != all) {
			$show = $db->strip($show);
			$query = $db->query("SELECT * FROM `<PRE>logs` WHERE `message` LIKE '$show%'");
		}
		else {
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
		}
		else{
			$last = $db->num_rows($query);
		}
		if ($db->num_rows($query) == 0) {
			echo "No logs found.";
		} else {
			if ($show != all) {
				$query2 = $db->query("SELECT * FROM `<PRE>logs` WHERE `message` LIKE '$show%' ORDER BY `id` DESC LIMIT $p, $l");
			} else {
				$query2 = $db->query("SELECT * FROM `<PRE>logs` ORDER BY `id` DESC LIMIT $p, $l");
			}
			while($data = $db->fetch_array($query2)) {
				$array['USER'] = $data['loguser'];
				$array['DATE'] = strftime("%m/%d/%Y", $data['logtime']);
				$array['TIME'] = strftime("%T", $data['logtime']);
				$array['MESSAGE'] = $data['message'];
			echo $style->replaceVar("tpl/adminlogs.tpl", $array);
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