<?php

define(SEPARATOR, '/');

//Check if called by script
if(THT != 1){
//	die();
}

function parse_info_file($filename) {
  $info = array();
  $constants = get_defined_constants();

  if (!file_exists($filename)) {
    return $info;
  }

  $data = file_get_contents($filename);
  if (preg_match_all('
    @^\s*                           # Start at the beginning of a line, ignoring leading whitespace
    ((?:
      [^=;\[\]]|                    # Key names cannot contain equal signs, semi-colons or square brackets,
      \[[^\[\]]*\]                  # unless they are balanced and not nested
    )+?)
    \s*=\s*                         # Key/value pairs are separated by equal signs (ignoring white-space)
    (?:
      ("(?:[^"]|(?<=\\\\)")*")|     # Double-quoted string, which may contain slash-escaped quotes/slashes
      (\'(?:[^\']|(?<=\\\\)\')*\')| # Single-quoted string, which may contain slash-escaped quotes/slashes
      ([^\r\n]*?)                   # Non-quoted string
    )\s*$                           # Stop at the next end of a line, ignoring trailing whitespace
    @msx', $data, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
      // Fetch the key and value string
      $i = 0;
      foreach (array('key', 'value1', 'value2', 'value3') as $var) {
        $$var = isset($match[++$i]) ? $match[$i] : '';
      }
      $value = stripslashes(substr($value1, 1, -1)) . stripslashes(substr($value2, 1, -1)) . $value3;

      // Parse array syntax
      $keys = preg_split('/\]?\[/', rtrim($key, ']'));
      $last = array_pop($keys);
      $parent = &$info;

      // Create nested arrays
      foreach ($keys as $key) {
        if ($key == '') {
          $key = count($parent);
        }
        if (!isset($parent[$key]) || !is_array($parent[$key])) {
          $parent[$key] = array();
        }
        $parent = &$parent[$key];
      }

      // Handle PHP constants.
      if (isset($constants[$value])) {
        $value = $constants[$value];
      }

      // Insert actual value
      if ($last == '') {
        $last = count($parent);
      }
      $parent[$last] = $value;
    }
  }
  return $info;
}



//Create the class
class plugin_controller {
	
	var $plugin_path ="../includes/plugins";
	var $plugin_list = array();
	var $plugin_list_type = array('payment'=>'Payments','widget'=>'Widgets');//could be payment, registrars, themes, widgets, etc
	
	
	public function __construct() {	
		//Plugin installed
		//$this->plugin_list = $this->installedPlugins();		
	}
	
	/**
	 * Available plugins not installed
	 */
	public function availablePlugins($types = array()) {
		if (empty($types)) {
			$types = $this->get_plugin_folder_type_list();
		}
		
		if (is_dir($this->plugin_path) && $handle_type = opendir($this->plugin_path)) {
	    	while (FALSE !== ($file = readdir($handle_type))) {  		
	    		 if (!in_array($file, array('.','..'))) {
	    		 	$plugin_type_path = $this->plugin_path.SEPARATOR.$file;
	    		 	
	    		 	if (is_dir($plugin_type_path)) { 
	    		 		if (in_array(basename($plugin_type_path),$types)) {
	    		 			$handle = opendir($plugin_type_path);	    		 			
	    		 			while (FALSE !== ($sub_file = readdir($handle))) {
	    		 				if (!in_array($sub_file, array('.','..'))) {	    		 					
			    		 			$info_file = $plugin_type_path.SEPARATOR.$sub_file.SEPARATOR.$sub_file.'.info';	 			
				    		 		if (file_exists($info_file)) {
					    		 		$info_file = parse_info_file($info_file);	
					    		 				    		 		
					    		 		$info_file['path']= $plugin_type_path.SEPARATOR.$sub_file.SEPARATOR.$sub_file.'.class.php';
					    		 		$plugin_list[$file][strtolower($info_file['name'])] = $info_file;					    		 			
					    		 		//require_once $info_file['path'];					    		 			
		    		 				}
	    		 				}
	    		 			}
	    		 			closedir($handle);
	    		 		}
	    		 	}
	    		 }
	    	}
	    	closedir($handle_type);  	
		}
		//echo '<pre>'; print_r($plugin_list);
		return $plugin_list;
	}
	
	public function get_plugin_folder_type_list() {
		return array_keys($this->plugin_list_type);
	}
	
	public function get_plugin_name_type_list() {
		return $this->plugin_list_type;
	}
	
	/**
	 * PLugins installed 
	 * @todo show all the plugins
	 */
	public function installedPlugins() {
		global $db;
		$types = $this->get_plugin_folder_type_list();
		$plugin_return_list = array();		
		foreach($types as $type) {
			$sql = "SELECT * FROM `<PRE>plugins` WHERE type='$type'";
			$plugins= $db->query($sql);
			while($data = $db->fetch_array($plugins)) {
				$plugin_return_list[$type][]= $data;
			}
		}
		return $plugin_return_list;		
	}
	
	
	public function installPlugin($plugin) {
		global $db;
		$sql_insert = "INSERT INTO `<PRE>plugins` (name, version, type) VALUES ('{$plugin['name']}','{$plugin['version']}','{$plugin['type']}')";
		$db->query($sql_insert);		
	}
	
	public function load($name) {
		if (!empty($this->plugin_list)) {
			$my_plugin_list = array_keys($this->plugin_list); 
			
			if (in_array($name, $my_plugin_list)) {
				$plugin = $this->plugin_list[$name];
				
				if (isset($plugin)) {
					if (file_exists($plugin['path'])) {						
						require_once $plugin['path'];
					}
				}				
			}
		}
		return false;
	}
	
	public function processPlugins($post_variables){
		
		foreach($post_variables as $key=>$post_var) {
			if(substr($key, 0, 6) == 'plugin' && $post_var == 'on') {
				//$plugin_name_list_to_install[] =
			}			
		}
	}
	
	public function isPluginInstalled($id){
		
	}
	
	public function getPluginInfo($id){
		
	}
	
	public function getPluginIdByName($name) {
		
	}
	
}

//$plugin = new plugin_controller();
//$plugin->load('paypal');
//var_dump($plugin->availablePlugins());


///-----------------------

abstract class Plugin {
	///abstract public function install();
	//abstract public function uninstall();	
}

abstract class PaymentPlugin extends Plugin {
	/*abstract public function install();
	abstract public function uninstall();*/
	abstract public function sendVariables($a,$b);
	abstract public function postBack();
	abstract public function ipn(& $a);	
}

/*
class MoneyOrder extends PaymentPlugin {
	function _construct(){		
	}
	
		
	
	public function draw() {
		echo 'draw Money order';
	}
}

class TwoCheckOut extends PaymentPlugin {
	function _construct(){		
	}
	
	public function draw() {
		echo 'draw TwoCheckOut';
	}
}
*/

/*
$plugin_to_load = 'paypal';

$my_plugin = new $plugin_to_load();
$my_plugin->test();
*/



/*

interface Observer {
	public function update (Observable $subject);
}

abstract class Plugin implements Observer {
	protected $internalData = array();
	
	
	abstract public function install();
	abstract public function uninstall();
	
	public function update (Observable $subject) {
		$this->internalData = $subject->getData();
	}	
}


class MoneyOrder extends Plugin {
	function _construct(){		
	}
	public function install(){}
	public function uninstall(){}
	
	
	public function draw() {
		echo 'draw Money order';
	}
}

class TwoCheckOut extends Plugin {
	function _construct(){		
	}
	public function install(){}
	public function uninstall(){}
	
	public function draw() {
		echo 'draw TwoCheckOut';
	}
}


abstract class Observable {
	private $observers = array();
	public function addObserver(Observer $observer) {
		array_push($this->observers, $observer);
	}	
	public function notifyObservers() {
		for ($i = 0; $i<count($this->observers); $i++ ) {
			$plugin = $this->observers[$i];
			$plugin->update($this);
		}
	}	
}


class DataSource extends Observable {
	private $names;
	function __construct(){
		$this->names  =array();				
	}
	public function addRecord($name) {
		array_push($this->names  , $name);
	}
	public function get_data() {
		return $this->names;
	}
}

$dat = new DataSource();
$new_money = new MoneyOrder();

$dat->addObserver($new_money);
$dat->addRecord('julio');

$new_money->draw();


*/



?>
