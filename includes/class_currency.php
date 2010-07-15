<?php


if(THT != 1){
	die();
}

class currency {
	
	public function save($array) {		
	}
	
	public function get($id) {		
	}
	
	
	public function loadCurrencySettings() {
        $curr_conf= array ();
        $curr_conf['symbol_prefixed']= $this->conf['curr_symbol_prefixed'];
        $curr_conf['symbol']         = $this->conf['symbol'];
        $curr_conf['decimals']       = $this->conf['decimal_number'];
        $curr_conf['str1']           = $this->conf['decimal_str'];
        $curr_conf['str2']           = $this->conf['thousand_str'];
        $this->curr_conf             = $curr_conf;
    }
      
    function toFloat($number) {
        if(is_float($number))return $number;
        return sprintf("%f", floatval($number));
	}
	
	/*
	 * Integer
	 */
	function toInteger($number) {
		return intval(sprintf("%d", intval($number)));
	}
	/*
	function toCurrency($number, $array= array (), $format= 0, $with_symbol= 1, $strict=0)
    {
        $array = !count($array)?$this->curr_conf:$array;
        $number= str_replace($array['str1'], '.', $number);
        $number= str_replace($array['str2'], '', $number);
        $number= $this->utils->toFloat($number);
        return empty ($format)
                ?(($strict)?null:$number)
                :$this->utils->toCurrency($number, $array, $with_symbol);
    }
    */
    
	/*
	 * Currency format
	 */
	 
	function toCurrency($number, $curr= array (), $with_symbol= 1) {
		global $db;		
		if (isset($number)) {		
			$number = $this->toFloat($number);
			//var_dump($number);
	        if (!isset ($curr['symbol_prefixed']))
				$curr['symbol_prefixed']= 0; // 1 if is a prefix 0 if is a sufix
			if (!isset ($curr['symbol'])) {
				//$curr['symbol']= '$';
				$curr['symbol']= $db->config('currency');
			}
			if (!isset ($curr['decimals']))
				$curr['decimals']= 2;
			if (!isset ($curr['str1']))
				$curr['str1']= '.';
			if (!isset ($curr['str2']))
				$curr['str2']= ',';
			if (empty ($with_symbol))
				return number_format($number, $curr['decimals'], $curr['str1'], $curr['str2']);
			elseif (!empty ($curr['symbol_prefixed'])) 
				return $curr['symbol'] . " " . number_format($number, $curr['decimals'], $curr['str1'], $curr['str2']);
			else
				return number_format($number, $curr['decimals'], $curr['str1'], $curr['str2']) . " " . $curr['symbol'];
		}
	}
}

?>
