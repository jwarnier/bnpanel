<?php

require_once INCLUDES.'nusoap/nusoap.php';

class Hook_Dolibarr_Invoice extends Hook {
	
	function pre_save() {
		echo 'Calling function '.get_class().':: '.__FUNCTION__.' - BEFORE the save invoice is loaded<br />';
	}
	
	function post_save() {
		global $main, $invoice, $extrafield, $user;	
		echo 'Calling function '.get_class().':: '.__FUNCTION__.' - AFTER the save invoice is loaded<br />';
		
		$settings = $this->getSettings();
		
		//If invoice was created
		if (!empty($this->data) && !empty($this->data['id'])) {

			$user_info = $user->getUserById($this->data['uid']);
			
			//1. First we create the dolibarr_societe_id field if it doesn't exists
			
			$extra_field_exists = $extrafield->getExtraFieldByName('dolibarr_societe_id');
				
			if (empty($extra_field_exists)) {
				$params['field_name'] 	= 'dolibarr_societe_id';
				$params['field_type'] 	= 'text';
				$params['model'] 		= 'user';
				$extrafield->save($params);
			}			
			$extra_field_data = $extrafield->getExtraFieldByName('dolibarr_societe_id');			
			$user_exists_in_dolibarr = false;
			
			$societe_id = null;
			
			//2. Check if the 
			//var_dump($this->data, $extra_field_data);
			if ($extra_field_data) {				
				$conditions = array('conditions' => 'model_id  = '.$this->data['uid'].' AND field_id = '.$extra_field_data['id']);
				$my_result = $extrafield->extrafield_values->find('first', $conditions);
				if ($my_result) {					
					$user_exists_in_dolibarr = true;
					$societe_id = $my_result->field_value;
				}
			}			
			
			// Load the create invoice			
			$client = new nusoap_client($settings['dolibarr_url']);
			//$client->soap_defencoding='UTF-8';
			//$client->decodeUTF8(false);
			
			$params['nom'] 				= $user_info['firstname'].' '.$user_info['lastname'];
			$params['adresse'] 			= $user_info['address'];
			$params['cp'] 				= $user_info['zip'];
			$params['tel'] 				= $user_info['phone'];
			$params['email'] 			= $user_info['email'];
			$params['ville'] 			= $user_info['city'];
			$params['country'] 			= $user_info['country'];
			$params['typent_id'] 		= 8; //individual
			$params['tva_intra']    	=  '';
			$params['tva_assuj']   =  0;			
						
			//Is a company!!
			if (!empty($user_info['vatid'])) {
				$params['typent_id'] 	= '3'; //8 individual / 3 medium company*/
				$params['tva_intra'] 	= $user_info['vatid'];			
				$params['tva_assuj']   =  1;
			}
			
			/*
			assujtva_value
			tva_intra			
			typent_id // 8 individual / 3 medium company*/
		
			//$params['cp'] 		= $user_info['cp'];
			//$params['cp'] 		= $user_info['cp'];
									
			$parameters = array('authentication' => $settings['authentication'], 
								'societe_params' => $params);			
			$result = null;
			
			if (!$user_exists_in_dolibarr) {				
				$result = $client->call('createSociete', $parameters, '','');
				
			
				if ($result && isset($result['societe_id'])) {					
			
					$values['model_id'] 	= $this->data['uid'];
					$values['field_id'] 	= $extra_field_data['id'];
					$societe_id = $values['field_value']	= $result['societe_id'];				
					
					//$values['tms']			= $result['societe_id'];
					//Saving the extra field in the BNPanel DB
					$extrafield->extrafield_values->save($values);					
				} else {
					print $client->error_str;
				}
			}
						
			//Creating an invoice in Dolibar			
			if (!empty($societe_id)) {					

				$params = array();
				
				$params['societe_id'] 	= $societe_id;
				$params['amount'] 		= $this->data['amount'];
				$params['remise'] 		= 0;
				$params['price'] 		= $this->data['amount'];
				$params['description'] 	= 'Chamilo';
					
				$parameters = array('authentication' => $settings['authentication'],
														'invoice_params' => $params);
					
				$result = $client->call('createInvoice', $parameters, '','');
				
				
				/*
				echo '<h2>request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
				echo '<h2>response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
				echo '<h2>debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
				*/
				
				
			}
			
			exit;
		}		
		
	}	
	
} 