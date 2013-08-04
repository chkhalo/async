<?php

abstract class Los312_Async_Model_Abstract
{
    const CACHE_LIMIT = 300;
    const CACHE_PREFIX = 'los312_async_';
    //const SLEEP_INTERVAL = 100000;
    const SLEEP_INTERVAL = 400000;
    const AJAX_DOWNLOAD_BLOCK_IDENTIFER = 'ajax_download_block_identifer';
    
    
    const XML_PATH_LOS312_ASINC_ACTIVE = 'los312_async/los312_async_enable/active';
    
    const XML_PATH_LOS312_ALLOW_IPS  = 'los312_async/los312_async_enable/allow_ips';
    
    const XML_PATH_LOS312_ALLOW_AJAX = 'los312_async/los312_async_advanced/ajax_active';
    

    const COEFFICIENT_MICRO_SECONDS = 1000000;
    
    protected $_adapter = null;
    protected $_storage = null;
    
    public function getAdapter()
    {
        $modelName = Mage::getStoreConfig('los312_async/los312_async_advanced/adapter');
        $this->_adapter = Mage::getModel($modelName);        
        return $this->_adapter;
    } 
    
    public function getStorage()
    {
        $modelName = Mage::getStoreConfig('los312_async/los312_async_advanced/storage');
        $this->_adapter = Mage::getModel($modelName);        
        return $this->_adapter;
    } 
    

}
