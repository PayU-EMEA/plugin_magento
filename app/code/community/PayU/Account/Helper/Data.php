<?php

/**
*	ver. 1.9.0
*	PayU Data Helper Model
*	
*	@copyright  Copyright (c) 2011-2014 PayU
*	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
*/

class PayU_Account_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Converts the Magento float values (for instance 9.9900) to PayU accepted Currency format (999)
	 * 
	 * @param  string
	 * @return int
	 */
	public function toAmount($val){
        $multiplied = $val*100;
        $round = (int)round($multiplied);
		return $round;
	}
	
	/**
	 * Change locale of given string and separator
	 * @param string $string
	 * @param string $s
	 */
	
	public function localize($string, $s = "/"){
		
    	$lang = explode("_",Mage::app()->getLocale()->getLocaleCode());
    	
    	if(!in_array($lang[0],array("pl","en")))
    		$lang[0] = "en";
    		
    	$from = array("/pl/","/en/",$s."pl".$s,$s."en".$s,"_en.","_pl.");
    	$to = array("/".$lang[0]."/","/".$lang[0]."/",$s.$lang[0].$s,$s.$lang[0].$s,"_".$lang[0].".","_".$lang[0].".");
    	
    	return str_replace($from, $to, $string);
    
    }
    
    public function isFirstVersionNewer($build1,$build2){
    	$arr1 = explode(".",$build1);
    	$arr2 = explode(".",$build2);
    	
    	$b1 = "";
    	$b2 = "";
    	
    	foreach($arr1 as $key => $b){
    		$b1 .= str_pad($arr1[$key], 4,"0",STR_PAD_LEFT);
    		$b2 .= str_pad($arr2[$key], 4,"0",STR_PAD_LEFT);
    	}
    	
    	return ((int)$b1 > (int)$b2);
    }
    
    /*
    public function localize($string, $s = "/"){
    	$lang = explode("_",Mage::app()->getLocale()->getLocaleCode());
    	if(!in_array($lang[0],array("pl","en")))
    		$lang[0] = "en";
    	
    	_pl. _pl_ /pl/
    		
    	$patt = array('[_./]pl[_./]','[_./]en[_./]');
    	$repl = array('[_./]'.$lang[0].'[_./]','[_./]'.$lang[0].'[_./]');
    	return preg_replace($patt,$repl,$string);
    }*/
	
}