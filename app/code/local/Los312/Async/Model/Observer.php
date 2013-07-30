<?php

class Los312_Async_Model_Observer
{
    

    protected $_asyncBlocksHtml = array();
    protected $_asyncBlockList = array();
    protected $_adapter = null;
    
    const CACHE_LIMIT = 300;
    const CACHE_PREFIX = 'los312_async_';
    
    public function getAdapter()
    {
        $modelName = Mage::getStoreConfig('los312_async/los312_async_advanced/adapter');
        $this->_adapter = Mage::getModel($modelName);        
        return $this->_adapter;
    }    
    

    /* Render one block if the remoute request */
    public function asyncBlockRender($observer)
    {
        $asyncBlockIdentifer = Mage::app()->getRequest()->getParam('async_block_identifer');
        /*If request async*/
        if ($asyncBlockIdentifer) {
            Mage::getModel('los312_async/render')->renderBlock($asyncBlockIdentifer);            
        }        

    }
    


    public function sendBlocksToAsyncRendering($observer)
    {
        
        $asyncBlockIdentifer = Mage::app()->getRequest()->getParam('async_block_identifer');
        /*If request already async*/
        if ($asyncBlockIdentifer) {
            return false;
        }
        $blocks = Mage::app()->getLayout()->getAllBlocks();
        foreach ($blocks as $name => $block) {
            //Mage::log('Blocks::'.$block->getNameInLayout().' key:'.$block->getCacheKey());
            if ($block->getAsync()) {
                $cacheKey = $block->getCacheKey();
                //Mage::log('--Async::'.$block->getNameInLayout().' key:'.$cacheKey);
                
                $cacheData = Mage::app()->loadCache($cacheKey);
                if (!$cacheData) {
                    //Mage::log('----Not cached::'.$block->getCacheKey());
                    $this->_asyncBlockList[$cacheKey] = $block;
                }                
            }
        }
        if (!empty($this->_asyncBlockList)) {
            //Mage::log('sendBlocksToRemoteRender');
            $this->getAdapter()->sendBlocksToRemoteRender($this->_asyncBlockList); 
            
        }
    }
    
    public function receiveBlocksFromAsyncRendering($blocks)
    {
        $renderedBlocks = $this->getAdapter()->getRemoteRendedBlocks($this->_asyncBlockList);
        return $renderedBlocks;
    }
    

    public function replaceAsyncBlockPlaceholdersToHtml($observer)
    {
        $asyncBlockIdentifer = Mage::app()->getRequest()->getParam('async_block_identifer');
        /*If request already async*/
        if ($asyncBlockIdentifer) {
            return false;
        }
        /*If there aren't async blocks*/
        if (empty($this->_asyncBlockList)) {
            return false;
        }
        $response = Mage::app()->getResponse();
        $body = $response->getBody();
        /*Wait */
        $renderedBlocks = $this->receiveBlocksFromAsyncRendering($this->_asyncBlockList); 
        
       $downloadByAjax = false;
        foreach ($this->_asyncBlockList as $identifer => $block) { 
          
            if (!empty($renderedBlocks[$identifer])) {                
                $html = $renderedBlocks[$identifer];
            } else {
                $block = Mage::app()->getLayout()->createBlock(
                    'los312_async/placeholder',
                    'placeholder_block',
                    array('template' => 'los312_async/placeholder.phtml')
                );
                $downloader = Mage::app()->getLayout()->createBlock(
                    'los312_async/downloader',
                    'downloader_block',
                    array('template' => 'los312_async/downloader.phtml')
                );  
                
                $downloader->setBlockIdentifer($identifer);
                
                
                $html = $block->toHtml().$downloader->toHtml();
                $url = Los312_Async_Model_Adapter_Curl::$remouteUrls[$identifer];
                $ajaxUrl = Mage::helper('core/url')->addRequestParam($url, array('async_ajax_upload' => '1'));
                $html .= '<a href="'.$ajaxUrl.'">AJAX URL</a>';
                $html = '<div id="async_download_'.$identifer.'" class="async_download" >'.$html.'</div>';
                $downloadByAjax = true;
            }
            $body = str_replace('{{' . $identifer . '}}', $html, $body);
        }
        
        $response->setBody($body);
    }
}

