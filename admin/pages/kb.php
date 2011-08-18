<?php
/* For licensing terms, see /license.txt */

/**
 * @todo change everything here and use the model class
 * 
 */ 

class page {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Knowledge Base Menu";
		$this->navlist[] = array("Categories", "folder_page.png", "cat");
		$this->navlist[] = array("Articles", "page_white_text.png", "art");
	}
	
	public function description() {
		return "<strong>Knowledge Base</strong><br />
		Welcome to the Knowledge Base category. In this section you can manage all your catergories and articles for the knowledge base.";	
	}
	
	public function content() { # Displays the page 
		global $main, $style, $db;
		switch($main->getvar['sub']) {
			case "cat":
			
				if($_POST['add'] && $main->checkToken(false)) {
					foreach($main->postvar as $key => $value) {
						if($value == "" && !$n && $key != "admin") {
							$main->errors("Please fill in all the fields!");
							$n++;
						}
					}
					if(!$n) {
						
						$main->postvar['name'] 			= $db->strip($main->postvar['name']);
						$main->postvar['description'] 	= $db->strip($main->postvar['description']);
						
						$db->query("INSERT INTO <PRE>cats (name, description) 
									VALUES('{$main->postvar['name']}','{$main->postvar['description']}')");
						$main->errors("Category Added!");
					}
				}
				if($_POST['edit'] && $main->checkToken(false)) {
					foreach($main->postvar as $key => $value) {
						if($value == "" && !$n && $key != "admin") {
							$main->errors("Please fill in all the fields!");
							$n++;
						}
					}
					if(!$n) {
						
						$main->postvar['editname'] 			= $db->strip($main->postvar['editname']);
						$main->postvar['editdescription'] 	= $db->strip($main->postvar['editdescription']);
						$main->postvar['id'] 				= intval($main->postvar['id']);
						
						$db->query("UPDATE <PRE>cats SET name = '{$main->postvar['editname']}', `description` = '{$main->postvar['editdescription']}' WHERE id = '{$main->postvar['id']}'");
						$main->errors("Category Edited!");
					}
				}
				if($main->getvar['del'] && $main->checkToken(false)) {
					$main->getvar['del'] = intval($main->getvar['del']);
					$db->query("DELETE FROM <PRE>cats WHERE id = {$main->getvar['del']}");
					$main->errors("Category Deleted!");					
				}
				$array['AJAX'] = "cat";
				$array['SUB'] = "Name";
				$array['SUB2'] = "Description";
				$array['NAME'] = "Category";
				$array['BOXES'] = "";
				$array['CATID'] = "";
				$array['JS'] = "";
				$query = $db->query("SELECT * FROM `<PRE>cats`");
				if($db->num_rows($query)) {
					while($data = $db->fetch_array($query)) {
						$array2['NAME'] = $data['name'];
						$array2['DESCRIPTION'] = $data['description'];
						$array2['ID'] = $data['id'];
						$array2['LINK'] = "cat";
						$array['BOXES'] .= $style->replaceVar("tpl/support/acpbox.tpl", $array2);
					}
				}
				echo $style->replaceVar("tpl/support/acp.tpl", $array);
				break;
				
			case "art":
				$query = $db->query("SELECT * FROM `<PRE>cats`");
				if(!$db->num_rows($query)) {
					echo "You need to add a category before an article!";
					return;
				}
				if($_POST['add'] && $main->checkToken(false)) {
					foreach($main->postvar as $key => $value) {
						if($value == "" && !$n && $key != "admin") {
							$main->errors("Please fill in all the fields!");
							$n++;
						}
					}
					if(!$n) {
						$main->postvar['name'] 			= $db->strip($main->postvar['name']);
						$main->postvar['description'] 	= $db->strip($main->postvar['description']);
						$main->postvar['catid'] 		= intval($main->postvar['catid']);
						
						$db->query("INSERT INTO <PRE>articles (name, content, catid)
								VALUES('{$main->postvar['name']}','{$main->postvar['description']}', '{$main->postvar['catid']}')");
								
						$main->errors("Article Added!");
					}
				}
				if($_POST['edit'] && $main->checkToken(false)) {
					foreach($main->postvar as $key => $value) {
						if($value == "" && !$n && $key != "admin") {
							$main->errors("Please fill in all the fields!");
							$n++;
						}
					}
					if(!$n) {
						$main->postvar['editname'] 			= $db->strip($main->postvar['editname']);
						$main->postvar['editdescription'] 	= $db->strip($main->postvar['editdescription']);
						$main->postvar['id'] 				= intval($main->postvar['id']);
						
						$db->query("UPDATE `<PRE>articles` SET `name` = '{$main->postvar['editname']}', `content` = '{$main->postvar['editdescription']}' WHERE `id` = '{$main->postvar['id']}'");
						$main->errors("Article Edited!");
					}
				}
				if($main->getvar['del'] && $main->checkToken(false)) {
					$main->getvar['del'] = intval($main->getvar['del']);
					$db->query("DELETE FROM `<PRE>articles` WHERE id = '{$main->getvar['del']}'");
					$main->errors("Article Deleted!");					
				}
				$array['AJAX'] = "art";
				$array['SUB'] = "Name";
				$array['SUB2'] = "Content";
				$array['NAME'] = "Article";
				$array['BOXES'] = "";
				$array['JS'] = 'document.getElementById("catid").selectedIndex = result[2];';
				$catsql = $db->query("SELECT * FROM `<PRE>cats`");
				while($cat = $db->fetch_array($catsql)) {
					$values[] = array($cat['name'], $cat['id']);	
				}
				$array3['DROPDOWN'] = $main->dropDown("catid", $values);
				$array['CATID'] = $style->replaceVar("tpl/support/catid.tpl", $array3);
				$query = $db->query("SELECT * FROM `<PRE>articles`");
				if($db->num_rows($query)) {
					while($data = $db->fetch_array($query)) {
						$array2['NAME'] = $data['name'];
						$array2['DESCRIPTION'] = "";
						$array2['ID'] = $data['id'];
						$array2['LINK'] = "art";
						$array['BOXES'] .= $style->replaceVar("tpl/support/acpbox.tpl", $array2);
					}
				}
				echo $style->replaceVar("tpl/support/acp.tpl", $array);
				break;
		}		
	}
}