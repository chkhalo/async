<?php

class Los312_Async_Model_Render  extends Los312_Async_Model_Abstract
{
    
    
    public function renderBlock($identifer)
    {
        ignore_user_abort();
        $remoutTimeLimit = (int)Mage::getStoreConfig('los312_async/los312_async_advanced/remout_time_limit');
        if($remoutTimeLimit>0){
            set_time_limit($remoutTimeLimit);
        }
        
        $blocks = Mage::app()->getLayout()->getAllBlocks();
        if ($identifer) {
            foreach ($blocks as $name => $block) {
                if ($block->getAsync() && $block->getCacheKey() == $identifer) {

                    $message = '|REMOUTE|--------Start render remout block '.$identifer;                    
                    Mage::log($message);       
                    
                    $this->getStorage()->removeBlockHtml($identifer);
                    
                    $block->setAsyncRender(true);
                    $block->setAsyncRenderFlag();
                    $html = $block->toHtml();
                   // Mage::log('Los312_Async_Model_Render::renderBlock '.$html);
                    $block->setAsyncRender(false);
                    $block->setAsyncRenderFlag(false);
                    
                    //$storage = $this->getStorage();
                    
                    //$cache = Mage::app()->getCache();
                    
                    
                   // $html = '999999999999';
                    
                    $this->getStorage()->saveBlockHtml($identifer, $html);
                    
                    //$cache->save($html, self::CACHE_PREFIX.$identifer, array("asyncBlock"), self::CACHE_LIMIT);
                    /*todo delete*/
                    Mage::app()->getResponse()->setBody($html)->sendResponse();
                    
                    $message = '|REMOUTE|--------End render remout block '.$identifer;
                    Mage::log($message);                    
                    exit;
                }
            }
        }
        exit;
    }
}
