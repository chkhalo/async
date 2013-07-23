<?php

class Los312_Async_Model_Render
{
    
    
    public function renderBlock($asyncBlockIdentifer)
    {
       // Mage::log('Los312_Async_Model_Render::renderBlock');

        ignore_user_abort();
        $remoutTimeLimit = (int)Mage::getStoreConfig('los312_async/los312_async_advanced/remout_time_limit');
        set_time_limit($remoutTimeLimit);
        $blocks = Mage::app()->getLayout()->getAllBlocks();
        if ($asyncBlockIdentifer) {
            foreach ($blocks as $name => $block) {
                if ($block->getAsync() && $block->getCacheKey() == $asyncBlockIdentifer) {
                    //Mage::log('Los312_Async_Model_Render::renderBlock '.$asyncBlockIdentifer);
                    $block->setAsyncRender(true);
                    $block->setAsyncRenderFlag();
                    $html = $block->toHtml();
                    //Mage::log('Los312_Async_Model_Render::renderBlock '.$html);
                    $block->setAsyncRender(false);
                    $block->setAsyncRenderFlag(false);
                    $cache = Mage::app()->getCache();
                    $cache->save($html, Los312_Async_Model_Observer::CACHE_PREFIX.$asyncBlockIdentifer, array("asyncBlock"), Los312_Async_Model_Observer::CACHE_LIMIT);
                    Mage::app()->getResponse()->setBody($html)->sendResponse();
                    exit;
                }
            }
        }
        exit;
    }
}
