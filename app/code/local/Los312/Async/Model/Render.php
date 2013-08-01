<?php

class Los312_Async_Model_Render  extends Los312_Async_Model_Abstract
{
    
    
    public function renderBlock($asyncBlockIdentifer)
    {
        ignore_user_abort();
        $remoutTimeLimit = (int)Mage::getStoreConfig('los312_async/los312_async_advanced/remout_time_limit');
        set_time_limit($remoutTimeLimit);
        $blocks = Mage::app()->getLayout()->getAllBlocks();
        if ($asyncBlockIdentifer) {
            foreach ($blocks as $name => $block) {
                if ($block->getAsync() && $block->getCacheKey() == $asyncBlockIdentifer) {

                    $message = '--------Start render remout block '.$asyncBlockIdentifer;
                    Mage::log($message);                    
                    
                    $block->setAsyncRender(true);
                    $block->setAsyncRenderFlag();
                    $html = $block->toHtml();
                   // Mage::log('Los312_Async_Model_Render::renderBlock '.$html);
                    $block->setAsyncRender(false);
                    $block->setAsyncRenderFlag(false);
                    $cache = Mage::app()->getCache();
                    $cache->save($html, self::CACHE_PREFIX.$asyncBlockIdentifer, array("asyncBlock"), self::CACHE_LIMIT);
                    Mage::app()->getResponse()->setBody($html)->sendResponse();
                    
                    $message = '--------End render remout block '.$asyncBlockIdentifer;
                    Mage::log($message);                    
                    exit;
                }
            }
        }
        exit;
    }
}
