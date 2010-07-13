<?php
/* For licensing terms, see /license.txt */

class page{
	public function content(){
		global $db;
		if($db->config('alerts')){
			$array['ALERTS'] = $db->config('alerts');
			global $style;
			echo $style->replaceVar('tpl/cannouncements.tpl', $array);
		} #closes if
		else{
			echo 'No Announcements Available';
		}
	}
}
?>
