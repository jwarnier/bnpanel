<?php
/* For licensing terms, see /license.txt */

class extrafield extends model {
	
	public $columns 	= array('id', 'model','field_type', 'field_name', 'field_display_text', 'field_default_value', 'field_order', 'field_visible', 'field_changeable', 'field_filter', 'tms');
	public $table_name = 'extrafields';
	public $_modelName = 'extrafield';
	
	public $has_many	= array('field_options'	=> array('table_name'	=> 'extrafield_options', 
												   		'columns'	=> 	array('id', 'field_id', 'option_value','option_display_text')),
								'field_values'	=> array('table_name'	=> 'extrafield_values',
													   'columns'	=> 	array('id', 'model_id', 'field_id','field_value')),

						);
	
	
	public function create($params) { 
		$id = $this->save($params);
		return $id;
	}
	
	public function edit($id, $params) {		
		$this->setId($id);		
		$this->update($params);
	}
	
	public function delete() {
		parent::delete();
	}	
	
	
	public function getExtraFieldByName($name) {
		global $db;		
		$name = $db->strip($name);
		$sql = "SELECT * FROM ".$this->getTableName()." WHERE field_name = '".$name."'";
		$result = $db->query($sql);
		$data = array();		
		if ($db->num_rows($result) > 0) {
			$data = $db->fetch_array($result, 'ASSOC');	
		}		
		return $data;
	}
	public function getExtraField($id) {
		global $db;
		$id = intval($id);
		$sql = "SELECT * FROM ".$this->getTableName()." WHERE id = ".$id;
		$result = $db->query($sql);
		$data = array();		
		if ($db->num_rows($result) > 0) {
			$data = $db->fetch_array($result, 'ASSOC');	
		}		
		return $data;
	}	
}