<?php
/* For licensing terms, see /license.txt */

/**
 * Object model 
 * Inspired from the ActiveRecord in Akelos
 * 
 * The use of update, save, delete functions are encouraged. Try to avoid direct mysql_query to insert data in the DB  
 * 
 * @todo finders methods are working but they are not used yet in the system (due lack of time and testing) 
 * 
 * @author	Julio Montoya <gugli100@gmail.com>	BeezNest 
 */

class model {
	//Table columns id, field, value
	public $columns;	
	//Attributes of the object
	public $attributes;
	//Table name
	public $table_name;	
	public $_modelName;	
	//Current primary key
	public $primary_key;	
	public $_db;		
	public $_newRecord;	
	public $has_many;
	
	public $is_virtual_obj = false;
	
		
	public function __construct() {	
		global $db;
		$this->_db 	= $db;
        $attributes = (array)func_get_args();        
        return $this->init($attributes);
    }
    

    public function init($attributes = array()) {  
    	    
//        $this->_internationalize = is_null($this->_internationalize) && AK_ACTIVE_RECORD_INTERNATIONALIZE_MODELS_BY_DEFAULT ? count($this->getAvailableLocales()) > 1 : $this->_internationalize;
  //      @$this->_instantiateDefaultObserver();
    //    $this->establishConnection();

		if(!empty($this->table_name)){
      //      $this->setTableName($this->table_name);
        }
        
        /*$load_acts = isset($attributes[1]['load_acts']) ? $attributes[1]['load_acts'] : (isset($attributes[0]['load_acts']) ? $attributes[0]['load_acts'] : true);
        $this->act_as = !empty($this->acts_as) ? $this->acts_as : (empty($this->act_as) ? false : $this->act_as);
        if (!empty($this->act_as) && $load_acts) {
            $this->_loadActAsBehaviours();
        }

        if(!empty($this->combined_attributes)){
            foreach ($this->combined_attributes as $combined_attribute){
                $this->addCombinedAttributeConfiguration($combined_attribute);
            }
        }*/

        if(isset($attributes[0]) && is_array($attributes[0]) && count($attributes) === 1){
            $attributes = $attributes[0];
            $this->_newRecord = true;
        }

        // new AkActiveRecord(23); //Returns object with primary key 23
        if(isset($attributes[0]) && count($attributes) === 1 && $attributes[0] > 0){
            $record = $this->find($attributes[0]);
            if(!$record){
                return false;
            }else {
                $this->setAttributes($record->getAttributes(), true);
            }
            // This option is only used internally for loading found objects
        }elseif(isset($attributes[0]) && isset($attributes[1]) && $attributes[0] == 'attributes' && is_array($attributes[1])){
            foreach(array_keys($attributes[1]) as $k){
                $attributes[1][$k] = $this->castAttributeFromDatabase($k, $attributes[1][$k]);
            }

            $avoid_loading_associations = isset($attributes[1]['load_associations']) ? false : !empty($this->disableAutomatedAssociationLoading);
            $this->setAttributes($attributes[1], true);
        }else{
            $this->newRecord($attributes);
        }
        
        if (isset($this->has_many)) {        	
        	foreach($this->has_many as $class_item) {
        		$handler_name = $class_item['table_name'];
        		$handler = new Model();
        		$handler->setColumns($class_item['columns']);   
        		$handler->setTableName($handler_name);
        		$handler->_modelName = $class_item['table_name'];
        		$handler->is_virtual_obj = true;
        		$this->$handler_name = $handler;        		
        	}        	        	
        }
        //empty($avoid_loading_associations) ? $this->loadAssociations() : null;
    }
    
    
    public function setColumns($columns) {
    	$this->columns = $columns;
    	
    }
    
    public function setTableName($tablename) {    	
    	$this->table_name = $tablename;
    }
    
        /**
    * New objects can be instantiated as either empty (pass no construction parameter) or pre-set with attributes but not yet saved
    * (pass an array with key names matching the associated table column names).
    * In both instances, valid attribute keys are determined by the column names of the associated table; hence you can't
    * have attributes that aren't part of the table columns.
    */
    public function newRecord($attributes) {
        $this->_newRecord = true;

        if(empty($attributes)){
            return;
        }

        if(isset($attributes) && !is_array($attributes)){
            $attributes = func_get_args();
        }
        $this->setAttributes($this->attributesFromColumnDefinition(),true);
        $this->setAttributes($attributes);
    }
    
    
    
	
	/**
	 * Prepares a SELECT query 
	 * 
	 */
	
	public function find() {
        $args = func_get_args();        
        $options = $this->_extractOptionsFromArgs($args);        
        list($fetch,$options) = $this->_extractConditionsFromArgs($args,$options);         
        $this->_sanitizeConditionsVariables($options);    
                  
        switch ($fetch) {
            case 'first':
                return $this->_findInitial($options);
            case 'all':
                return $this->_findEvery($options);
            default:
                return $this->_findFromIds($args, $options);
        }
        return false;
    }
    
	public function &_findInitial($options) {
        // TODO: virtual_limit is a hack
        // actually we fetch_all and return only the first row
        $options = array_merge($options, array((!empty($options['include']) ?'virtual_limit':'limit')=>1));

        $result =& $this->_findEvery($options);

        if(!empty($result) && is_array($result)){
            $_result =& $result[0];
        }else{
            $_result = false;
            // if we return an empty array instead of false we need to change this->exists()!
            //$_result = array();
        }
        return  $_result;

    }
     public function &_findEvery($options) {
     	
        if((!empty($options['include']) && $this->hasAssociations())){
            $result =& $this->findWithAssociations($options);
        } else {
            $sql = $this->constructFinderSql($options);
            
                                    
            if (isset($options['wrap'])) {
                $sql = str_replace('{query}',$sql,$options['wrap']);
            }
            if(!empty($options['bind']) && is_array($options['bind']) && strstr($sql,'?')){
                $sql = array_merge(array($sql),$options['bind']);
            }
            if (!empty($options['returns']) && $options['returns']!='default') {            	
                $options['returns'] = in_array($options['returns'],array('simulated','default','array'))?$options['returns']:'default';
                $simulation_class = !empty($options['simulation_class']) && class_exists($options['simulation_class'])?$options['simulation_class']:'AkActiveRecordMock';
                $result =& $this->findBySql($sql,null,null,null,$options['returns'],$simulation_class);                
            } else {            	
                $result =& $this->findBySql($sql);
            }
        }

        if(!empty($result) && is_array($result)){
            $_result =& $result;
        }else{
            $_result = false;
        }
        return  $_result;

    }

    public function &_findFromIds($ids, $options) {    	
        $expects_array = is_array($ids[0]);
        $ids = array_map(array($this, 'quotedId'),array_unique($expects_array ? (isset($ids[1]) ? array_merge($ids[0],$ids) : $ids[0]) : $ids));
        $num_ids = count($ids);
        
        //at this point $options['conditions'] can't be an array
        
        $conditions = !empty($options['conditions'])? $options['conditions']:'';        
        switch ($num_ids) {
            case 0 :
                trigger_error($this->t('Couldn\'t find %object_name without an ID%conditions',array('%object_name'=>$this->getModelName(),'%conditions'=>$conditions)), E_USER_ERROR);
                break;
            case 1 :            
                $table_name = !empty($options['include']) && $this->hasAssociations() ? '__owner' : $this->getTableName();               
                if (!preg_match('/SELECT .* FROM/is', $conditions)) {
                    $options['conditions'] = $table_name.'.'.$this->getPrimaryKey().' = '.$ids[0].(empty($conditions)?'':' AND '.$conditions);
                } else {                	
                    if (false!==($pos=stripos($conditions,' WHERE '))) {
                        $before_where = substr($conditions,0, $pos);
                        $after_where = substr($conditions, $pos+7);
                        $options['conditions'] = $before_where.' WHERE ('.$table_name.'.'.$this->getPrimaryKey().' = '.$ids[0].') AND ('.$after_where.')';
                    } else {
                        $options['conditions'].=' WHERE '.$table_name.'.'.$this->getPrimaryKey().' = '.$ids[0];
                    }
                }                			
                $result =& $this->_findEvery($options);
                if (!$expects_array && $result !== false){
                    return $result[0];
                }
                return  $result;
                break;

            default:
            
                $without_conditions = empty($options['conditions']) ? true : false;
               // var_dump($this->castAttributesForDatabase($this->getPrimaryKey(), $ids));
                $ids_condition = $this->getPrimaryKey().' IN ('.join(', ', $this->castAttributesForDatabase($this->getPrimaryKey(), $ids)).')';
                
                
                if (!preg_match('/SELECT .* FROM/is', $conditions)) {                	
                    $options['conditions'] = $ids_condition.(empty($conditions)?'':' AND '.$conditions);
                } else {
                    if (false!==($pos=stripos($conditions,' WHERE '))) {
                        $before_where = substr($conditions,0, $pos);
                        $after_where = substr($conditions, $pos+7);
                        $options['conditions'] = $before_where.' WHERE ('.$ids_condition.') AND ('.$after_where.')';
                    } else {
                        $options['conditions'].=' WHERE '.$ids_condition;
                    }
                }
				
                $result =& $this->_findEvery($options);
                if(is_array($result) && ($num_ids==1 && count($result) != $num_ids && $without_conditions)){
                    $result = false;
                }
                return $result;
                break;
        }
    }
    
        /**
    * You can use this method for casting multiple attributes of the same time at once.
    *
    * You can pass an array of values or an array of Active Records that might be the response of a finder.
    */
    public function castAttributesForDatabase($column_name, $values, $add_quotes = true) {
    	global $main;
    	return $values;
    	/*
        $casted_values = array();
        $values = !empty($values[0]) && is_object($values[0]) && method_exists($values[0], 'collect') && method_exists($values[0], 'getPrimaryKey') ?
        $values[0]->collect($values, $values[0]->getPrimaryKey(), $column_name) : $main->toArray($values);
        if(!empty($values)){
            $casted_values = array();
            foreach ($values as $value){
                $casted_values[] = $this->castAttributeForDatabase($column_name, $value, $add_quotes);
            }
        }*/
        return $casted_values;
    }
    
    
    /**
    * Works like find_all, but requires a complete SQL string. Examples:
    * $Post->findBySql("SELECT p.*, c.author FROM posts p, comments c WHERE p.id = c.post_id");
    * $Post->findBySql(array("SELECT * FROM posts WHERE author = ? AND created_on > ?", $author_id, $start_date));
    */
    public function &findBySql($sql, $limit = null, $offset = null, $bindings = null, $returns = 'default', $simulation_class = 'AkActiveRecordMock')
    {
        if ($limit || $offset){
            //Ak::deprecateWarning("You're calling AR::findBySql with \$limit or \$offset parameters. This has been deprecated.");
            $this->_db->addLimitAndOffset($sql, array('limit'=>$limit,'offset'=>$offset));
        }
        $objects = array();            
                    
        $records = $this->_db->select ($sql,'selecting');  
              
        foreach ($records as $record){
            if ($returns == 'default') {
                $objects[] =& $this->instantiate($this->getOnlyAvailableAttributes($record), false);
            } else if ($returns == 'simulated') {
                $objects[] = $this->_castAttributesFromDatabase($this->getOnlyAvailableAttributes($record),$this);
            } else if ($returns == 'array') {
                $objects[] = $this->_castAttributesFromDatabase($this->getOnlyAvailableAttributes($record),$this);
            }
        }
        if ($returns == 'simulated') {
            $false = false;
            $objects = $this->_generateStdClasses($simulation_class,$objects,$this->getType(),$false,$false,array('__owner'=>array('pk'=>$this->getPrimaryKey(),'class'=>$this->getType())));
        }
		
        return $objects;
    }
    
       
    public function constructFinderSql($options, $select_from_prefix = 'default') {  
    	  	
        $sql = isset($options['select_prefix']) ? $options['select_prefix'] : ($select_from_prefix == 'default' ? 'SELECT '.(!empty($options['joins'])?$this->getTableName().'.':'') .'* FROM '.$this->getTableName() : $select_from_prefix);        
        $sql .= !empty($options['joins']) ? ' '.$options['joins'] : '';
		
        $this->addConditions($sql, isset($options['conditions']) ? $options['conditions'] : array());

        // Create an alias for order
        if(empty($options['order']) && !empty($options['sort'])){
            $options['order'] = $options['sort'];
        }

        $sql .= !empty($options['group']) ? ' GROUP BY '.$options['group'] : '';
        $sql .= !empty($options['order']) ? ' ORDER BY '.$options['order'] : '';
        
        $this->_db->addLimitAndOffset($sql,$options);
		
        return $sql;
    }
    
     /**
    * Adds a sanitized version of $conditions to the $sql string. Note that the passed $sql string is changed.
    */
    public function addConditions(&$sql, $conditions = null, $table_alias = null)
    {
        if (empty($sql)) {
            $concat = '';
        }
        //if (is_string($conditions) && (stristr($conditions,' WHERE ') || stristr($conditions,'SELECT'))) {
        
        if (is_string($conditions) && (preg_match('/^SELECT.*?WHERE/is',trim($conditions)))) {// || stristr($conditions,'SELECT'))) {
            $concat = '';
            $sql = $conditions;
            $conditions = '';
        } else {
            $concat = 'WHERE';
        }
        
        $concat = empty($sql) ? '' : ' WHERE ';
        if (stristr($sql,' WHERE ')) $concat = ' AND ';
        
        //if (empty($conditions) && $this->_getDatabaseType() == 'sqlite') $conditions = '1';  // sqlite HACK
        if (empty($conditions)) $conditions = '1';  // sqlite HACK

        //if($this->getInheritanceColumn() !== false && $this->descendsFromActiveRecord($this)){
        if (1) {
            //$type_condition = $this->typeCondition($table_alias);
            $type_condition = '';
            if (empty($sql)) {
                $sql .= !empty($type_condition) ? $concat.$type_condition : '';
                $concat = ' AND ';
                if (!empty($conditions)) {
                    $conditions = '('.$conditions.')';
                }
            } else {            	
                if (($wherePos=stripos($sql,'WHERE'))!==false) {
                    if (!empty($type_condition)) {
                        $oldConditions = trim(substr($sql,$wherePos+5));
                        $sql = substr($sql,0,$wherePos).' WHERE '.$type_condition.' AND ('.$oldConditions.')';
                        $concat = ' AND ';
                    }
                    if (!empty($conditions)) {
                        $conditions = '('.$conditions.')';
                    }
                } else {
                    if (!empty($type_condition)) {
                        $sql = $sql.' WHERE '.$type_condition.'';
                        $concat = ' AND ';
                    }
                    if (!empty($conditions)) {
                        $conditions = '('.$conditions.')';
                    }
                }

            }
        }

        if(!empty($conditions)) {
            $sql  .= $concat.$conditions;
            $concat = ' AND ';

        }
        return $sql;
    }
    
    public function typeCondition($table_alias = null)
    {
   //     $inheritance_column = $this->getInheritanceColumn();
        $type_condition = array();
        $table_name = $this->getTableName();
        $available_types = array_merge(array($this->getModelName()));
        foreach ($available_types as $subclass){
            $type_condition[] = ' '.($table_alias != null ? $table_alias : $table_name).'.'.$inheritance_column.' = \''.AkInflector::humanize(AkInflector::underscore($subclass)).'\' ';
        }
        return empty($type_condition) ? '' : '('.join('OR',$type_condition).') ';
    }
        
    public function quotedId($id = false)
    {
        return $this->castAttributeForDatabase($this->getPrimaryKey(), $id ? $id : $this->getId());
    }
    
    
    public function _extractOptionsFromArgs(&$args) {
        $last_arg = count($args)-1;
        //return isset($args[$last_arg]) && is_array($args[$last_arg]) && $this->_isOptionsHash($args[$last_arg]) ? array_pop($args) : array();
        return isset($args[$last_arg]) && is_array($args[$last_arg]) ? array_pop($args) : array();
    }
    
    
     public function _extractConditionsFromArgs($args, $options)
    {
        if(empty($args)){
            $fetch = 'all';
        } else {
            $fetch = $args[0];
        }
        $num_args = count($args);

        // deprecated: acts like findFirstBySQL
        if ($num_args === 1 && !is_numeric($args[0]) && is_string($args[0]) && $args[0] != 'all' && $args[0] != 'first'){
            //  $Users->find("last_name = 'Williams'");    => find('first',"last_name = 'Williams'");
            Ak::deprecateWarning(array("AR::find('%sql') is ambiguous and therefore deprecated, use AR::find('first',%sql) instead", '%sql'=>$args[0]));
            $options = array('conditions'=> $args[0]);
            return array('first',$options);
        } //end

        // set fetch_mode to 'all' if none is given
        if (!is_numeric($fetch) && !is_array($fetch) && $fetch != 'all' && $fetch != 'first') {
            array_unshift($args, 'all');
            $num_args = count($args);
        }
        if ($num_args > 1) {
            if (is_string($args[1])){
                //  $Users->find(:fetch_mode,"first_name = ?",'Tim');
                $fetch = array_shift($args);
                $options = array_merge($options, array('conditions'=>$args));   //TODO: merge_conditions
            }elseif (is_array($args[1])) {
                //  $Users->find(:fetch_mode,array('first_name = ?,'Tim'));
                $fetch = array_shift($args);
                $options = array_merge($options, array('conditions'=>$args[0]));   //TODO: merge_conditions
            }
        }

        return array($fetch,$options);
    }
    
    
	public function _sanitizeConditionsVariables(&$options)
    {
        if(!empty($options['conditions']) && is_array($options['conditions'])){
            if (isset($options['conditions'][0]) && strstr($options['conditions'][0], '?') && count($options['conditions']) > 1){
                //array('conditions' => array("name=?",$name))
                $pattern = array_shift($options['conditions']);
                $options['bind'] = array_values($options['conditions']);
                $options['conditions'] = $pattern;
            }elseif (isset($options['conditions'][0])){
                //array('conditions' => array("user_name = :user_name", ':user_name' => 'hilario')
                $pattern = array_shift($options['conditions']);
                $options['conditions'] = str_replace(array_keys($options['conditions']), array_values($this->getSanitizedConditionsArray($options['conditions'])),$pattern);
            }else{
                //array('conditions' => array('user_name'=>'Hilario'))
                $options['conditions'] = join(' AND ',(array)$this->getAttributesQuoted($options['conditions']));
            }
        }
        //$this->_sanitizeConditionsCollections($options);
    }
    
     /**
    * Finder methods must instantiate through this method to work with the single-table inheritance model and
    * eager loading associations.
    * that makes it possible to create objects of different types from the same table.
    */
    public function &instantiate($record, $set_as_new = true, $call_after_instantiate = true)
    {
       /*$inheritance_column = $this->getInheritanceColumn();
        if(!empty($record[$inheritance_column])){
            $inheritance_column = $record[$inheritance_column];
            $inheritance_model_name = AkInflector::camelize($inheritance_column);
            @require_once(AkInflector::toModelFilename($inheritance_model_name));
            if(!class_exists($inheritance_model_name)){
                trigger_error($this->t("The single-table inheritance mechanism failed to locate the subclass: '%class_name'. ".
                "This error is raised because the column '%column' is reserved for storing the class in case of inheritance. ".
                "Please rename this column if you didn't intend it to be used for storing the inheritance class ".
                "or overwrite #{self.to_s}.inheritance_column to use another column for that information.",
                array('%class_name'=>$inheritance_model_name, '%column'=>$this->getInheritanceColumn())),E_USER_ERROR);
            }
        }*/
		
        $model_name = isset($inheritance_model_name) ? $inheritance_model_name : $this->getModelName();
    
    	if (class_exists($model_name)) {
	        $object = new $model_name();
	        $object->setAttributes($record);				
	        $object->_newRecord = $set_as_new;
    	} else {    		
			if ($this->is_virtual_obj) {
				$this->setAttributes($record);
				$this->_newRecord = $set_as_new;
				return $this;
			}
    	}
    	
        /*if ($call_after_instantiate) {
            $object->afterInstantiate();
            $object->notifyObservers('afterInstantiate');
        }*/
        //(AK_CLI && AK_ENVIRONMENT == 'development') ? $object ->toString() : null;		
        return $object;
    }
    
    
        /**
    * Allows you to set all the attributes at once by passing in an array with
    * keys matching the attribute names (which again matches the column names).
    * Sensitive attributes can be protected from this form of mass-assignment by
    * using the $this->setProtectedAttributes method. Or you can alternatively
    * specify which attributes can be accessed in with the $this->setAccessibleAttributes method.
    * Then all the attributes not included in that won?t be allowed to be mass-assigned.
    */
    public function setAttributes($attributes, $override_attribute_protection = false)
    {
        //$this->parseAkelosArgs($attributes);
      /*  if(!$override_attribute_protection){
            $attributes = $this->removeAttributesProtectedFromMassAssignment($attributes);
        }*/
        if(!empty($attributes) && is_array($attributes)){        	
            foreach ($attributes as $k=>$v){            	
                $this->setAttribute($k, $v);
            }
        }
    }
    
    
      /**
                         Setting Attributes
    ====================================================================
    See also: Getting Attributes, Model Attributes, Toggling Attributes, Counting Attributes.
    */
    public function setAttribute($attribute, $value, $inspect_for_callback_child_method = false, $compose_after_set = true)
    //public function setAttribute($attribute, $value, $inspect_for_callback_child_method = AK_ACTIVE_RECORD_ENABLE_CALLBACK_SETTERS, $compose_after_set = true)
    {
        if($attribute[0] == '_'){
            return false;
        }
       /* if($this->isFrozen()){
            return false;
        }*/
        
        /*if($inspect_for_callback_child_method === true && method_exists($this,'set'.AkInflector::camelize($attribute))){
            
            $watchdog[$attribute] = @$watchdog[$attribute]+1;
            if($watchdog[$attribute] == 5000){
                if((!defined('AK_ACTIVE_RECORD_PROTECT_SET_RECURSION')) || defined('AK_ACTIVE_RECORD_PROTECT_SET_RECURSION') && AK_ACTIVE_RECORD_PROTECT_SET_RECURSION){
                    trigger_error(Ak::t('You are calling recursively AkActiveRecord::setAttribute by placing parent::setAttribute() or  parent::set() on your model "%method" method. In order to avoid this, set the 3rd paramenter of parent::setAttribute to FALSE. If this was the behaviour you expected, please define the constant AK_ACTIVE_RECORD_PROTECT_SET_RECURSION and set it to false',array('%method'=>'set'.AkInflector::camelize($attribute))),E_USER_ERROR);
                    return false;
                }
            }
            $this->{$attribute.'_before_type_cast'} = $value;
            return $this->{'set'.AkInflector::camelize($attribute)}($value);
        } */       
        
        if($this->hasAttribute($attribute)){        
            //$this->{$attribute.'_before_type_cast'} = $value;
            $this->$attribute = $value;
            
           /* if($compose_after_set && !empty($this->_combinedAttributes) && !$this->requiredForCombination($attribute)){
                $combined_attributes = $this->_getCombinedAttributesWhereThisAttributeIsUsed($attribute);
                foreach ($combined_attributes as $combined_attribute){
                    $this->composeCombinedAttribute($combined_attribute);
                }
            }
            if ($compose_after_set && $this->isCombinedAttribute($attribute)){
                $this->decomposeCombinedAttribute($attribute);
            }*/
        }elseif(substr($attribute,-12) == 'confirmation' && $this->hasAttribute(substr($attribute,0,-13))){
            $this->$attribute = $value;
        }

        /*if($this->_internationalize){
            if(is_array($value)){
                $this->setAttributeLocales($attribute, $value);
            }elseif(is_string($inspect_for_callback_child_method)){
                $this->setAttributeByLocale($attribute, $value, $inspect_for_callback_child_method);
            }else{
                $this->_groupInternationalizedAttribute($attribute, $value);
            }
        }*/
        return true;
    }
    
     
    /**
    * Returns true if given attribute exists for this Model.
    *
    * @param string $attribute
    * @return boolean
    */
    public function hasAttribute($attribute)
    {
        empty($this->columns) ? $this->getColumns() : $this->columns; // HINT: only used by HasAndBelongsToMany joinObjects, if the table is not present yet!        
        //return isset($this->columns[$attribute]) || (!empty($this->combinedAttributes) && $this->isCombinedAttribute($attribute));
        $columns = array_flip($this->columns);
        return isset($columns[$attribute]);
    }
    
    public function getOnlyAvailableAttributes($attributes) {    	
        $table_name = $this->getTableName();
        $ret_attributes = array();
        if(!empty($attributes) && is_array($attributes)){
            $available_attributes = $this->getAvailableAttributes();            
            $keys = array_keys($attributes);            
            
            $size = sizeOf($keys);
            for ($i=0; $i < $size; $i++){
                $k = str_replace($table_name.'.','',$keys[$i]);
                //var_dump($k);             
                //if(isset($available_attributes[$k]['name'][$k])){
                if(isset($available_attributes[$k])){
                    $ret_attributes[$available_attributes[$k]] = $attributes[$keys[$i]];                    
                }
            }
        }
        
        return $ret_attributes;
    }
    
     /**
                          Model Attributes
     ====================================================================
     See also: Getting Attributes, Setting Attributes.
     */

    public function getAvailableAttributes()
    {
        //return array_merge($this->getColumns(), $this->getAvailableCombinedAttributes()); 
        
        return $this->getColumns();
    }

    public function getAttributeCaption($attribute)
    {
        return $this->t(AkInflector::humanize($attribute));
    }

    /**
     * This function is useful in case you need to know if attributes have been assigned to an object.
     */
    public function hasAttributesDefined()
    {
        $attributes = join('',$this->getAttributes());
        return empty($attributes);
    }
    
    
    function getModelName() {
        if (!isset($this->_modelName)){
            if(!$this->setModelName()){
                trigger_error(Ak::t('Unable to fetch current model name'),E_USER_NOTICE);
            }
        }        
        return $this->_modelName;
    }  
    

	/**
	 * Prepares an  INSERT query to the database
	 * @param	array	list of attributes	to add
	 * @param	bool	clean a token
	 * @return	mixed	inserted id or false if error 
	 */
	public function save($attributes) {
		global $db, $main;
		
		$this->loadHook('pre_'.__FUNCTION__, $attributes);
		
		$new_attributes = $this->filterParams($attributes, $this->getColumns());
		$sql = 'INSERT INTO '.$this->getTableName().' '.
				'('.join(', ',array_keys($new_attributes)).') '.
				'VALUES ('.join(',',array_values($new_attributes)).')';
		//echo $sql; '<br />';
		$db->query($sql);		
		$insert_id = $db->insert_id();
		
		$attributes['id'] = $insert_id ;
		
		$this->loadHook('post_'.__FUNCTION__, $attributes);		
		$main->addlog($sql);
		return $insert_id;
	}
	
	/**
	 * Builds an update query to hit the DB
	 * @param	array	list of attributes to update
	 * @param	bool	clean a token
	 */
	public function update($attributes) {
		global $db;
		//Remove the primary key id
		unset($attributes['id']);		
		$sql =  "UPDATE ".$this->getTableName()." ".
        		"SET ".join(', ', $this->getAvailableAttributesQuoted($attributes)) ." ".
        		"WHERE id ='".$this->getId()."'";
       	$db->query($sql);
       	
       	global $main;
       	$main->addlog($sql);
       	
       	return true;
	}
	
	/**
	 * Builds a delete query to shoot the DB
	 * @param	bool	clean a token
	 */
	public function delete() {
		global $db;		
		$sql = "DELETE FROM ".$this->getTableName()." ".        		
	           "WHERE ".$this->getPrimaryKey()." ='".$this->getId()."'";
	    $db->query($sql);
	    global $main;
       	$main->addlog($sql);       	
	}

	
	/**
	 * Gets the current table name
	 */
	public function getTableName() {			
		return "`<PRE>".$this->table_name."`";		
		//return $this->table_name;
	}
	
	/**
    * Every Active Record class must use "id" as their primary ID. This getter overwrites the native id method, which isn't being used in this context.
    */
    public function getId() {
        $pk=$this->getPrimaryKey();
        if(empty($pk)) {
            debug_print_backtrace();
        }
        return $this->{$pk};
    }
    
    public function setId($value) {
        /*if($this->isFrozen()){
            return false;
        }*/
        $pk = $this->getPrimaryKey();
        $this->$pk = $value;
        return true;
    }
    
    /**
    * Returns the primary key field.
    */
    public function getPrimaryKey() {
        if(!isset($this->_primaryKey)){
            $this->setPrimaryKey();
        }
        return $this->_primaryKey;
    }
    
	
	
	/**
    * Defines the primary key field ? can be overridden in subclasses.
    */
    public function setPrimaryKey($primary_key = 'id') {
        if(!$this->hasColumn($primary_key)) {
        	global $main;
        	$main->addlog('Opps! We could not find primary key column %primary_key on the table %table, for the model %model',array('%primary_key'=>$primary_key,'%table'=>$this->getTableName(), '%model'=>$this->getModelName()));
        } else {
            $this->_primaryKey = $primary_key;
        }
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
	
	public function loadHook($function_name, $data = null) {
		global $main;		
		$my_class_name = get_class($this);	// i.e invoice, addon, etc	
		$classes = $main->loadHookFiles($my_class_name);
		if (isset($classes) && !empty($classes)) { 
			foreach ($classes as $class) {
				if (class_exists($class)) {
					$class_obj = new $class($data);
					if (isset($class_obj)) {
						$class_methods = get_class_methods($class);						
						if (in_array($function_name, $class_methods)) {							
							$class_obj->$function_name();
						}
					}
				}
			}
			
		}
	}	
}
