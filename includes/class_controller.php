<?php 
class Controller {
	
	var $navtitle; 
	var $navlist;
	var $data;
	var $content = '';
	
	function __construct() {
	
	}
	
	function get_submenu() {
		global $style, $main;
		
		$sidebar_link_link 	= "tpl/menu/submenu_link.tpl";
		$sidebar_link 		= "tpl/menu/submenu_main.tpl";
		
		$admin_navigation = $main->getAdminNavigation();
		$admin_nave_item = false;
		
		if (isset($admin_navigation[$main->getvar['page']]) && !empty($admin_navigation[$main->getvar['page']])) {
			$admin_nave_item = $admin_navigation[$main->getvar['page']];
		}
		
		$subsidebar = '';
		
		if (isset($this->navtitle) && $this->navtitle) {
						
			if (isset($this->navlist)) {
				$array3 = array();
				$array3['LINKS'] = null;
				foreach($this->navlist as $key => $value) {
		
					$array2['IMGURL'] = $value[1];
					$array2['LINK'] = "?page=".$admin_nave_item['link']."&sub=".$value[2];
					$array2['VISUAL'] = $value[0];
		
					if (isset($main->getvar['sub']) && $value[2] == $main->getvar['sub']) {
						$array2['ACTIVE'] 	= 'active';
					} else {
						$array2['ACTIVE'] 	= '';
					}
					$array3['LINKS'] .= $style->replaceVar($sidebar_link_link, $array2);
				}
				$subsidebar = $style->replaceVar($sidebar_link, $array3);				
			}
		}
		return $this->data['SUBMENU'] = $subsidebar;
	}
	
	function replaceVar($tpl, $data = array()) {
		global $style;
		$this->content .= $style->replaceVar($tpl, $data);
	}
	
} 