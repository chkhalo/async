<?php

abstract class Los312_Async_Model_Abstract
{
    const CACHE_LIMIT = 30;
    const CACHE_PREFIX = 'los312_async_';
    const SLEEP_INTERVAL = 100000;
    const AJAX_DOWNLOAD_BLOCK_IDENTIFER = 'ajax_download_block_identifer';
    
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
