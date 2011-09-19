<?php
/* For licensing terms, see /license.txt */

class page extends Controller {
	
	public $navtitle;
	public $navlist = array();
							
	public function __construct() {
		$this->navtitle = "Look and Feel Sub Menu";
		$this->navlist[] = array("Theme Chooser", "layout.png", "tchoose");
	//	$this->navlist[] = array("Theme Uploader", "layout_add.png", "tupload"); Disabling for security reasons
        $this->navlist[] = array("jQuery UI Theme", "palette.png", "ui-theme");
//		$this->navlist[] = array("CSS Editor", "css.png", "cssedit");
//		$this->navlist[] = array("TPL Editor", "xhtml.png", "tpledit");
		$this->navlist[] = array("NavBar Editor", "link_edit.png", "navedit");
	}
	
	public function description() {
		return "<strong>Look and Feel Administration</strong><br />
		Welcome to the Look and Feel Administration Area. This is where you really make your BNPanel installation your very own. You can change and edit your theme, upload a new one, and even customize your navbar.<br />
		To get started, choose a link from the sidebar's SubMenu.<br />";			
	}
	
	private function checkWritable($file) { # Used for the Css & Tpl Editors - Thanks Jimmie!
		if(!is_writable($file)){
			return false;
		}
		else{
			return true;
		}
	}
	
	
	public function content() { # Displays the page
		global $main, $style, $db;
		if($_POST && $main->checkToken()) {
			foreach($main->postvar as $key => $value) {
				if($value == "" && !$n) {
					$main->errors("Please fill in all the fields!");
					$n++;
				}
			}
			if(!$n) {
				foreach($main->postvar as $key => $value) {
					$db->updateConfig($key, $value);
				}
				$main->errors("Settings Updated!");
				$main->generateToken();
				//Regenerate the config array
				$db->getSystemConfigList(true);		
				$main->done();
			}
		}
		switch ($main->get_variable('sub')) {	
			default:		
			case "tchoose": #theme chooser
				$folder = INCLUDES ."../themes/";
				if ($handle = opendir($folder)) { # Open the folder
					while (false !== ($file = readdir($handle))) { # Read the files
						if($file != "." && $file != ".." && $file != ".svn" && $file != "icons" && $file != "index.html" && $file != "flags") { # Check aren't these names
							$values[] = array($file, $file);
						}
					}
				}
				closedir($handle); #Close the folder
				$array['THEME'] = $main->dropDown("theme", $values, $db->config("theme"));
				$this->replaceVar("tpl/settings/themesettings.tpl", $array);
				break;
			case "tupload": # Theme Uploader
				$this->content .= "Here you can upload a theme of your choice to the installer. Please be sure that the theme is in .zip format.";
				$this->content .= '<div class="warning">This feature is disable for security reasons</div>';				
				//include INCLUDES."upload.php";
				//echo '<ERRORS>'; 
				break;
			case "cssedit": #CSS Editor - Thanks Jimmie & Kevin!
				$this->content .= '<div class="warning">This feature is disable for security reasons</div>';	
				/*
			  	$url = $db->config('url')."themes/".$db->config('theme')."/images/";
			  	$filetochange = INCLUDES."../themes/".$db->config('theme')."/style.css";
			  	$filetochangeOpen = file_get_contents($filetochange);
			  	$filetochangeStripped = str_replace("<IMG>", "&lt;IMG&gt;", $filetochangeOpen);
			  	$css['CSSCONTENT'] = $filetochangeStripped;
                if($this->checkWritable($filetochange)) {
                    $css['DISABLED'] = '';
                    $css['READONLY'] = '';
                    $css['CODEPRESS'] = 'codepress ';
                    $css['NOTICE'] = '';
                }
                else {
                    $css['DISABLED'] = ' disabled="disabled"';
                    $css['READONLY'] = ' readonly="readonly"';
                    $css['CODEPRESS'] = '';
                    $css['NOTICE'] = $style->notice(false, "In order to make changes to this file, please CHMOD it to 666.");
                }

			  	$this->replaceVar('tpl/cssedit.tpl', $css);
			  	*/
			  	
				break;
			case "tpledit": #TPL Editor -  Thanks Jimmie & Kevin!
				$this->content .= '<div class="warning">This feature is disable for security reasons</div>';
				# Quite simple redir-tor
		      	//$this->replaceVar('tpl/tpledit.tpl');
				break;
			case "navedit"; # Navbar Editor - Not Yet Finished
                $this->replaceVar("tpl/navedit/top.tpl");
                $query = $db->query("SELECT * FROM `<pre>navbar` ORDER BY `order` ASC");
                $array['LINKS'] = '';
                while($data = $db->fetch_array($query)) {
                        $array2['ID'] = $data['id'];
                        $array2['NAME'] = $data['visual'];
                        $array2['ICON'] = $data['icon'];
                        $array2['LINK'] = $data['link'];
                        $array['LINKS'] .= $style->replaceVar("tpl/navedit/linkbox.tpl", $array2);
                }
                $this->replaceVar("tpl/navedit/links.tpl", $array);
                $this->replaceVar("tpl/navedit/bottom.tpl", array('NOTICE' => $style->notice(true,
                            "Changes have been made! Please <a href=\"javascript:window.location.reload();\">refresh</a> to see them in action!")));
				break;

			case "editheader":
				$this->content .= '<div class="warning">This feature is disable for security reasons</div>';
		      /*# anyway. Something is crashing
		      unset($css);
		      unset($conheader);
		      unset($filetochange);
		      unset($filetochangeOpen);
		      unset($slash);
              $filetochange = LINK."../themes/".$db->config('theme')."/header.tpl";
              $contheader = str_replace("<APP TITLE>", "&lt;BNP TITLE&gt;", file_get_contents($filetochange));
              $contheader = str_replace("<JAVASCRIPT>", "&lt;JAVASCRIPT&gt;", $contheader);
              $contheader = str_replace("<CSS>", "&lt;CSS&gt;", $contheader);
              $contheader = str_replace("<IMG>", "&lt;IMG&gt;", $contheader);
              $contheader = str_replace("%INFO%", "&#37;INFO%", $contheader);
              $contheader = str_replace("<MENU>", "&lt;MENU&gt;", $contheader);
              $contheader = str_replace("<ICONDIR>", "&lt;ICONDIR&gt;", $contheader); #Alrighty, what to do next(y)?
              $css['CSSCONTENT'] = $contheader;
              if(!$this->checkWritable($filetochange)) {
                $css['READONLY'] = ' readonly="readonly"';
                $css['DISABLED'] = ' disabled="disabled"';
                $css['NOTICE'] = $style->notice(false, "In order to make changes to this file, please CHMOD it to 666.");
              }
              else {
                $css['READONLY'] = '';
                $css['DISABLED'] = '';
                $css['NOTICE'] = '';
              }
              $this->replaceVar('tpl/headedit.tpl', $css);*/
		   		break;

			case "editfooter":
				$this->content .= '<div class="warning">This feature is disable for security reasons</div>';
			/*
              $filetochange = LINK."../themes/".$db->config('theme')."/footer.tpl";
              $contheader = str_replace("<PAGEGEN>", "&lt;PAGEGEN&gt;", file_get_contents($filetochange));
              $contheader = str_replace("<COPYRIGHT>", "&lt;COPYRIGHT&gt;", $contheader); #Alrighty, what to do next(y)?
              $css['CSSCONTENT'] = $contheader;
              $css['EDITED'] = "Editing your footer template!";
              $css['BUTTON'] = $this->checkWritable($filetochange);
              if(!$this->checkWritable($filetochange)) {
                $css['READONLY'] = ' readonly="readonly"';
                $css['DISABLED'] = ' disabled="disabled"';
                $css['NOTICE'] = $style->notice(false, "In order to make changes to this file, please CHMOD it to 666.");
              }
              else {
                $css['READONLY'] = '';
                $css['DISABLED'] = '';
                $css['NOTICE'] = '';
              }
              $this->replaceVar('tpl/footedit.tpl', $css);*/
		  		break;
			case "ui-theme":
				$folder = INCLUDES ."./css/";
				if ($handle = opendir($folder)) { # Open the folder
					while (false !== ($file = readdir($handle))) { # Read the files
						if($file != "." && $file != ".." && $file != ".svn") { # Check aren't these names or a svn folder
							$values[] = array($file, $file);
						}
					}
				}
				closedir($handle); #Close the folder
				$array['THEME'] = $main->dropDown("ui-theme", $values, $db->config("ui-theme"));
				$array['NOTICE'] = $style->notice(true, "jQuery UI Theme Changed!");
                $this->replaceVar('tpl/ui-theme/chooser.tpl', $array);
                break;
			}		
		}	
	}