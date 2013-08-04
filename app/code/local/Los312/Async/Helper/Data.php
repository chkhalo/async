<?php


class Los312_Async_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected static $_asyncBlocks = array();
    protected $_isActive = null;
    protected $_isAllowAjax = null;

    public function isActive()
    {
        if($this->_isActive === null){
           $isActive = (bool)Mage::getStoreConfig(Los312_Async_Model_Abstract::XML_PATH_LOS312_ASINC_ACTIVE);
           if(!$isActive){
               $this->_isActive = false;
               return $this->_isActive;
           }
           if(!$this->isIpAllowed()){
               $this->_isActive = false;
               return $this->_isActive;
           }
           $this->_isActive = true;
        }
        return $this->_isActive;
    }
    
    public function isIpAllowed()
    {
        $allow = true;
        $allowedIps = (string)Mage::getStoreConfig(Los312_Async_Model_Abstract::XML_PATH_LOS312_ALLOW_IPS);
        $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
        if (!empty($allowedIps) && !empty($remoteAddr)) {
          $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
          if (array_search($remoteAddr, $allowedIps) === false && array_search(Mage::helper('core/http')->getHttpHost(), $allowedIps) === false) {
              $allow = false;
          }
        }
        return $allow;
    }     
    
    public function isAllowAjaxDownload(){
        if($this->_isAllowAjax === null){
           $isAllowAjax = (bool)Mage::getStoreConfig(Los312_Async_Model_Abstract::XML_PATH_LOS312_ALLOW_AJAX);
           if(!$isAllowAjax){
               $this->_isAllowAjax = false;
               return $this->_isAllowAjax;
           }
           $this->_isAllowAjax = true;
        }
        return $this->_isAllowAjax;        
    } 
    
}
