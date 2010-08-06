<?php
/* For licensing terms, see /license.txt */

/**
 * Object model 
 * Inspired in the ActiveRecord in Akelos
 * @author	Julio Montoya <gugli100@gmail.com>	Beeznest 
 */

class model {
	//Table columns id, field, value
	public $columns;
	
	//Attributes of the object
	public $attributes;
	//Table name
	public $table_name;
	
	//Current primary key
	public $primary_key;
	
	/**
	 * Prepares a SELECT query 
	 * 
	 */
	public function find() {
		
	}

	/**
	 * Prepares an  INSERT query to the database
	 * @param	array	list of attributes	to add
	 * @param	bool	clean a token
	 * @return	mixed	inserted id or false if error 
	 */
	public function save($attributes, $clean_token = true) {
		global $main, $db;
		//echo $this->getTableName();
		//var_dump($clean_token);
	//	var_dump($main->checkToken($clean_token));
		
		if ($main->checkToken($clean_token)) {		
			$new_attributes = $this->filterParams($attributes, $this->getColumns());
			$sql = 'INSERT INTO '.$this->getTableName().' '.
					'('.join(', ',array_keys($new_attributes)).') '.
					'VALUES ('.join(',',array_values($new_attributes)).')';
			//echo $sql; '<br />';
			$db->query($sql);
			return $db->insert_id();
		}		
	    return false;
	}
	
	/**
	 * Builds an update query to hit the DB
	 * @param	array	list of attributes to update
	 * @param	bool	clean a token
	 */
	public function update($attributes, $clean_token = true) {
		global $main, $db;
		if ($main->checkToken($clean_token)) {		
			//Remove the primary key id
			unset($attributes['id']);		
			$sql = 'UPDATE '.$this->getTableName().' '.
	        		'SET '.join(', ', $this->getAvailableAttributesQuoted($attributes)) .' '.
	        		"WHERE id ='".$this->getPrimaryKey()."'";
	       	$db->query($sql); 
		}
	}
	
	/**
	 * Builds a delete query to shoot the DB
	 * @param	bool	clean a token
	 */
	public function delete($clean_token = true) {
		global $main, $db;
		if ($main->checkToken($clean_token)) {	
			$sql = 'DELETE FROM '.$this->getTableName().' '.        		
	        		"WHERE id ='".$this->getPrimaryKey()."'";
	       	$db->query($sql);
		}
	}

	
	/**
	 * Gets the current table name
	 */
	public function getTableName() {			
		return "`<PRE>".$this->table_name."`";		
	}
	
	/**
	 * Gets the current primary key
	 */
	public function getPrimaryKey() {
		if(!isset($this->primary_key)){
            $this->setPrimaryKey();
        }
        return $this->primary_key;
	}
	
	/**
	 * Sets a primary key
	 */
	public function setPrimaryKey($id) {
		$this->primary_key = intval($id);
	}
	
	/**
	 * Get columns
	 */
	public function getColumns() {
		return $this->columns;
	}
	
	public function getAvailableAttributesQuoted($attributes) {		
        return $this->getAttributesQuoted($attributes);
    }
    
    public function getAttributesQuoted($attributes_array) {
        $set = array();
        $attributes_array = $this->getSanitizedConditionsArray($attributes_array);                
        foreach (array_diff($attributes_array,array('')) as $k=>$v){
            $set[$k] = $k.'='.$v;
        }
        return $set;
    }
    
    public function getSanitizedConditionsArray($conditions_array) {
    	$result = array();    	
        foreach ($conditions_array as $k=>$v){
           	$k = str_replace(':','',$k); // Used for Oracle type bindings            
            if($this->hasColumn($k)){
                $v = $this->castAttributeForDatabase($k, $v);
                $result[$k] = $v;
            }
        }        
        return $result;    	
    }    
    /**
     * Checks if a column name exists
     * @param	string	column name
     */
    public function hasColumn($column_name) {
    	if(in_array($column_name, $this->getColumns())) {
    		return true;
    	}
    	return false; 
    }
    
    /**
     * Strips the variable
     */
    public function castAttributeForDatabase($k, $v) {
    	global $db;
    	//this function should check the datatype not a priority right now    	
    	return "'".$db->strip($v)."'";    	
    }
    
    /**
     * Set defaults 
     */
    public function setDefaults() {
    	$columns = $this->getColumns();
		$default_list = array();
		foreach($columns as $column) {
			$default_list[$column] = '';
		}
		return $default_list;		
	}
	
	/**
	 * Filter params, remove empty attributes and strips the value
	 */	
	public function filterParams($params, $attributes) {
		global $db;		
		$filtered_params = array();
		foreach($params as $key=>$param) {
			if (in_array($key, $attributes)) {
				$filtered_params[$key]= "'".$db->strip($param)."'";
			}
		}
		//Only accept values with something there		
		$filtered_params = array_diff($filtered_params, array(''));
		return $filtered_params;
	}	
}