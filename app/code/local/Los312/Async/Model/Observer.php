<?php

class Los312_Async_Model_Observer  extends Los312_Async_Model_Abstract
{
    

    //protected $_asyncBlocksHtml = array();
    protected $_asyncBlockList = array();  
    

    /* Render one block if the remoute request */
    public function asyncBlockRender($observer)
    {
        $asyncBlockIdentifer = Mage::app()->getRequest()->getParam('async_block_identifer', false);
        /*If request async*/
        if ($asyncBlockIdentifer) {
                    $message = '|REMOUTE| Start in observer';
        Mage::log($message);
            Mage::getModel('los312_async/render')->renderBlock($asyncBlockIdentifer);            
        }        

    }   

    /*Send list of blocks to async render*/
    public function sendBlocksToAsyncRendering($observer)
    {        
        /*Check if request already async*/       
        if (Mage::app()->getRequest()->getParam('async_block_identifer', false)) {
            return false;
        }     
        $message = '|Start sendBlocksToAsyncRendering';
        Mage::log($message);
        /*Generate list of blocks to async render*/
        $blocks = Mage::app()->getLayout()->getAllBlocks();
        foreach ($blocks as $name => $block) {
            //Mage::log('Blocks::'.$block->getNameInLayout().' key:'.$block->getCacheKey());
            if ($block->getAsync()) {
                /*Check if block is cached*/
                $cacheKey = $block->getCacheKey();
                $cacheData = Mage::app()->loadCache($cacheKey);
                if (!$cacheData) {
                    $this->_asyncBlockList[$cacheKey] = $block;
                }                
            }
        }
        
        /*Send list of blocks to async render*/
        if (!empty($this->_asyncBlockList)) {
            //Mage::log('sendBlocksToRemoteRender');
            $this->getAdapter()->sendBlocksToRemoteRender($this->_asyncBlockList); 
            
        }
        $message = '|End sendBlocksToAsyncRendering';
        Mage::log($message);
    }
    
//    public function receiveBlocksFromAsyncRendering($blocks)
//    {
//        $renderedBlocks = $this->getAdapter()->getRemoteRendedBlocks($this->_asyncBlockList);
//        return $renderedBlocks;
//    }
    

    public function replaceAsyncBlockPlaceholdersToHtml($observer)
    {
        /*If request already async*/
        if (Mage::app()->getRequest()->getParam('async_block_identifer', false)) {
            return false;
        }
        /*If there aren't async blocks*/
        if (empty($this->_asyncBlockList)) {
            return false;
        }
        $response = Mage::app()->getResponse();
        $body = $response->getBody();
        /*Wait */
        $message = '|Start getRemoteRendedBlocks';
        $renderedBlocks = $this->getStorage()->getRemoteRendedBlocks($this->_asyncBlockList);
        $message = '|End getRemoteRendedBlocks';
        
        $message = '|Start insert block to body';
        Mage::log($message);
        //$downloadByAjax = false;
//
//        print_r($renderedBlocks);
//        die;
//        
        foreach ($this->_asyncBlockList as $identifer => $block) { 
            
            if (!empty($renderedBlocks[$identifer])) {                
                $html = $renderedBlocks[$identifer];
            } else {
                $html = '<div id="async_download_'.$identifer.'" class="async_download" >';
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
                
                $html .= $block->toHtml().$downloader->toHtml();
                $html .='</div>';  
                
            }
            
            $body = str_replace('{{' . $identifer . '}}', $html, $body);
        }
        $message = '|End insert block to body';
        Mage::log($message);
        $response->setBody($body);
    }
}

