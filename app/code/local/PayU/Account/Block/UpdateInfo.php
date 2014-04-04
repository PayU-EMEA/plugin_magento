<?php 

/**
 *	ver. 1.9.0
 *	PayU Update Info Block
 *
 *	@copyright  Copyright (c) 2011-2014 PayU
 *	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
 */

class PayU_Account_Block_UpdateInfo extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
   
        /** @var array Get latest version of the plugin */
        $latestVersion = Mage::getModel('payu_account/config')->getLatestVersion();
        
        /** @var array Get latest version info of the plugin */
        $latestVersionInfo = Mage::getModel('payu_account/config')->getLatestVersionInfo();

        if(!Mage::getModel('payu_account/config')->isLatestVersionInstalled()){
        
        	$html = "<div style='text-align:center;padding:5px;margin:5px 0px 5px 0px;border:1px #e58080 solid;background-color:#f6d3d3;box-shadow:2px 2px 6px #ccc;color:#ae1111;'>".Mage::helper('payu_account')->__('CAUTION: PayU plugin is not up to date.')."</div>";
	        $html .= Mage::helper('payu_account')->__('You are currently using version').":<br><b>";
	        $html .= Mage::getModel('payu_account/config')->getPluginVersion()." ".Mage::helper('payu_account')->__('for')." magento ".Mage::getModel('payu_account/config')->getMinimumMageVersion()."+</b><hr>";
	        
	        $html .= Mage::helper('payu_account')->__('The latest version of PayU plugin is');
	        $html .= " <b>".$latestVersion['version']."</b>";
	        
	        $html .= "<div style='text-align:center;padding:5px;margin:5px 0px 5px 0px;border:1px #c0dbea solid;background-color:#f4f9fb;box-shadow:2px 2px 6px #ccc;color:#1f516e;'><a href=\"http://".$latestVersionInfo['repository']."\" target=\"_blank\">".$latestVersionInfo['repository']."</a></div>";
	        
        }else{
        
        	$html = "<div style='text-align:center;padding:5px;margin:5px 0px 5px 0px;border:1px #97cf7b solid;background-color:#dbf2d0;box-shadow:2px 2px 6px #ccc;color:#3c8a15;'>".Mage::helper('payu_account')->__('OK: PayU plugin is up to date.')."</div>";
        	$html .= Mage::helper('payu_account')->__('You are currently using version').":<br><b>";
        	$html .= Mage::getModel('payu_account/config')->getPluginVersion()." ".Mage::helper('payu_account')->__('for')." magento ".Mage::getModel('payu_account/config')->getMinimumMageVersion()."+</b><hr>";
        
        }         
        $html .= Mage::helper('payu_account')->__('Documents attached to this implementation').":<br>";

        if(isset($latestVersion['docs']['guides']))
        {
            foreach($latestVersion['docs']['guides'] as $doc){

                $html .= $this->getLayout()->createBlock('adminhtml/widget_button')
                            ->setType('button')
                            ->setClass('scalable')
                            ->setLabel($doc['name'])
                            ->setOnClick("window.open('".$doc['url']."')")
                            ->toHtml();
                $html .= "<br>";

            }
        }

        if(count($latestVersion['docs']['websites']) > 0){
        	$html .= Mage::helper('payu_account')->__('More info on').":<br>";
        }

        if(isset($latestVersion['docs']['websites']))
        {
            foreach($latestVersion['docs']['websites'] as $key => $website){


                $html .= $this->getLayout()->createBlock('adminhtml/widget_button')
                            ->setType('button')
                            ->setClass('scalable')
                            ->setLabel($website['name'])
                            ->setOnClick("window.open('".$website['url']."')")
                            ->toHtml();
                $html .= "<br>";

            }
        }

        $html .= $latestVersion['description'];

        return $html;
    }
}
