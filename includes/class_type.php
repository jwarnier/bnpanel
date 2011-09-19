<?php
/* For licensing terms, see /license.txt */

//Create the class
class type {
	
	public $classes = array(); # All the classes here when createAll called
	
	public function acpPadd($type) { # Returns the html of a custom form
	
		global $style;
		$type_value = $type;
		if(!isset($this->classes[$type])) {
			$type = $this->createType($type);
		} else {
			$type = $this->classes[$type];	
		}
		$html = '';
		
		if ($type->acpForm) {
	        $html .= $style->javascript();
	        $html .= '<script type="text/javascript">
	        var gi = 0;
	        $(document).ready(function(){
	            //var info = new Array();
	            var info;
	            $("#submitIt").click(function() {
	                $("input").each(function(i) {
	                    if(gi == 0) {
	                        info = this.name + "="  + $("#" + this.id).val();
	                    }
	                    else {
	                        info = info + "," + this.name + "="  + $("#" + this.id).val();
	                    }                                    
	                    gi++;
	                });
	                $("select").each(function(i) {
	                    if(gi == 0) {
	                        info = this.name + "="  + $("#" + this.id).val();
	                    }
	                    else {
	                        info = info + "," + this.name + "="  + $("#" + this.id).val();
	                    }
	                    gi++;
	                });
	                var id = window.name.toString().split("-")[1];
	                window.opener.transfer(id, info);
	                window.close();
	            });
	        });
	        </script>';
	        
			if (count($type->acpForm) > 0 ) { 
				foreach($type->acpForm as $key => $value) {
					$array['NAME'] = $value[0] .":";
					$array['FORM'] = $value[1];
					$html .= $style->replaceVar("tpl/acptypeform.tpl", $array);
				}
			}			
			//Submit button commented when adding a new package
			if ($type_value != 'paid') {
            	$html .= "<button id=\"submitIt\">Submit</button>";
			}
			return $html;
		} else {			
			switch ($type_value) {
				case 'paid':
					echo 'You need to create first new billing cycles: <a href="index.php?page=billing">here</a>';
				break;
				default:
				break;
			}
		}
	}
	
	public function orderForm($type) { # Returns the html of a custom form
		global $style;
		
		if(!isset($this->classes[$type])) {
			$type = $this->createType($type);
		} else {
			$type = $this->classes[$type];	
		}		
		$html = '';
		if (isset($type->orderForm)) {
			foreach($type->orderForm as $key => $value) {
				$array['NAME'] = $value[0] .":";
				$array['FORM'] = $value[1];
				$html .= $style->replaceVar("tpl/acptypeform.tpl", $array);
			} 
			return $html;
		}
	}
	
	public function signupForm($type) { # Returns the html of a custom form
		global $style;
		if(!$this->classes[$type]) {
			$type = $this->createType($type);
		}
		else {
			$type = $this->classes[$type];	
		}
		if($type->acpForm) {
			foreach($type->acpForm as $key => $value) {
				$array['NAME'] = $value[0] .":";
				$array['FORM'] = $value[1];
				$html .= $style->replaceVar("tpl/acptypeform.tpl", $array);
			}
			return $html;
		}
	}
	
	public function createType($type) { # Creates a class and then returns it
		if (!empty($type)) {
			$file = INCLUDES . "types/". $type .".php";
			if(!file_exists($file)) {
				//echo "Type doesn't exist!";
				return false;	
			} else {
				include($file);
				$type = new $type;
				return $type;
			}
		} else {
			return false;
		}
	}
	
	public function createAll() { # Creates all types and returns them
		global $main;
		$files = $main->folderFiles(INCLUDES ."types/");
		foreach($files as $value) {
			$data = explode(".", $value);
			if($data[1] != "svn" and $data[1] == "php") {
				$classes[$data[0]] = $this->createtype($data[0]);
			}
		}
		$this->classes = $classes;
	}
	
	//@todo this should be move or removed to class_package
	public function determineType($id) { # Returns type of a package
		global $db;
		global $main;
		$query = $db->query("SELECT type FROM `<PRE>packages` WHERE `id` = '{$db->strip($id)}'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist!";
			$array['Package ID'] = $id;
			//$main->error($array);
			return false;
		} else {
			$data = $db->fetch_array($query);
			return $data['type'];
		}
	}
	
	/**
	 *  Returns server id of a package
	 * @param	int	package	id
	 * @return 	int	server id
	 */
	public function determineServer($package_id) {
		global $db, $main;
		
		$query = $db->query("SELECT server FROM `<PRE>packages` WHERE `id` = '{$db->strip($package_id)}'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist!";
			$array['Package ID'] = $package_id;
			$main->error($array);
			return;	
		} else {
			$data = $db->fetch_array($query);
			return $data['server'];
		}
	}
	
	/**
	 * Returns the server type of a package
	 * @param	int		server id
	 * @return	string 	server type (ispconfig, whm)
	 */
	public function determineServerType($server_id) { 
		global $db, $main;
		$sql = "SELECT type FROM `<PRE>servers` WHERE `id` = '{$db->strip($server_id)}'";
		$query = $db->query($sql);
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That server doesn't exist!";
			$array['Server ID'] = $server_id;
			$main->error($array);
			return;	
		} else {
			$data = $db->fetch_array($query);
			return $data['type'];
		}
	}
	//@todo this should be move or removed to class_package
	public function determineBackend($id) { # Returns server of a package
		global $db;
		global $main;
		$query = $db->query("SELECT backend FROM `<PRE>packages` WHERE `id` = '{$db->strip($id)}'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That package doesn't exist!";
			$array['Package ID'] = $id;
			$main->error($array);
			return false;	
		} else {
			$data = $db->fetch_array($query);
			return $data['backend'];
		}
	}
	
	public function acpPedit($type, $values) { # Returns the
		global $style;
	
		if (!isset($this->classes[$type])) {
			$type = $this->createType($type);
		} else {
			$type = $this->classes[$type];	
		}
		$html = '';
		if (isset($type->acpForm)) {
			foreach($type->acpForm as $key => $value) {				
				$array['NAME'] 	= $value[0] .":";
				$shit 			= explode("/>", $value[1]);
				$default = '';
				if (!empty($value[2])) {
					$default 		= ' value="'.$values[$value[2]].'" />';
				} 
				$array['FORM'] 	= $shit[0]. $default;
				$html .= $style->replaceVar("tpl/acptypeform.tpl", $array);
			}
			return $html;
		}
	}
	
	public function additional($id) { # Returns the additonal values on a package
		global $db;
		$query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$db->strip($id)}'");
		$data = $db->fetch_array($query);
		$content = explode(",", $data['additional']);
		foreach($content as $key => $value) {
			$inside = explode("=", $value);
			$values[$inside[0]] = $inside[1];
		}
		return $values;
	}
	
	public function userAdditional($id) { # Returns the additional info of a PID
		global $db, $main;
		$query = $db->query("SELECT * FROM `<PRE>orders` WHERE `id` = '{$db->strip($id)}'");
		if($db->num_rows($query) == 0) {
			$array['Error'] = "That user pack doesn't exist!";
			$array['PID'] = $id;
			$main->error($array);
			return;	
		}
		else {
			$query = $db->query("SELECT * FROM `<PRE>orders` WHERE `id` = '{$db->strip($id)}'");
			$data = $db->fetch_array($query);
			$content = explode(",", $data['additional']);
			foreach($content as $key => $value) {
				$inside = explode("=", $value);
				$values[$inside[0]] = $inside[1];
			}
			return $values;
		}
	}
}
