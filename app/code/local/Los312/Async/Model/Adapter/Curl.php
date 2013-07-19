<?php
class Los312_Async_Model_Adapter_Curl
{
    public function sendBlocksToRemoteRender($blocks)
    {

         Mage::log('Start of  getRemoteRendedBlocks');
        Varien_Profiler::enable();
        Varien_Profiler::start('sendBlocksToRemoteRender');

            $this->multiple_threads_request($blocks);

        Varien_Profiler::stop('sendBlocksToRemoteRender');
        $time = Varien_Profiler::fetch('sendBlocksToRemoteRender', 'sum');
	Mage::log('RemoteRender time::' . $time);
	Varien_Profiler::disable();


    }

    public function getRemoteRendedBlocks($blocks)
    {
        Varien_Profiler::enable();
        Varien_Profiler::start('getRemoteRendedBlocks');
        $cache = Mage::app()->getCache();
        $_asyncBlocksHtml = array();
        foreach ($blocks as $identifer => $block) {
            $_asyncBlocksHtml[$identifer] = false;
        }

        $wait = true;

        do {
            usleep(10000);
            $wait = false;
            foreach ($blocks as $identifer => $block) {
                if($_asyncBlocksHtml[$identifer]===false){
                    $result = $cache->load($identifer);
                    if($result!==false){
                        $_asyncBlocksHtml[$identifer]=$result;
                        $cache->remove($identifer);
                    } else {
                       $wait = true;
                    }

                }

            }
        } while ($wait);
        Varien_Profiler::stop('getRemoteRendedBlocks');
        $time = Varien_Profiler::fetch('getRemoteRendedBlocks', 'sum');
        $asyncBlockId = Mage::app()->getRequest()->getParam('async_block_id');
        if ($asyncBlockId) {

        } else {
            Mage::log('Waiting remoute blocks time::' . $time);
        }


        Varien_Profiler::disable();


        return $_asyncBlocksHtml;

    }

    public function blockRender()
    {

        ignore_user_abort();
        set_time_limit(300);
        $asyncBlockId = Mage::app()->getRequest()->getParam('async_block_id');
        $blocks = Mage::app()->getLayout()->getAllBlocks();
        if ($asyncBlockId) {
            foreach ($blocks as $name => $block) {
                if ($block->getAsync() && $block->getCacheKey() == $asyncBlockId) {
                    $block->setAsyncRender(true);
                    $block->setAsyncRenderFlag();
                    $html = $block->toHtml();
                    $cache = Mage::app()->getCache();
                    $cache->save($html, $asyncBlockId, array("asyncBlock"), 10);
                    Mage::app()->getResponse()->setBody($html)->sendResponse();
                    exit;
                }
            }
            exit;
        }
    }

        public function multiple_threads_request($blocks)
    {
        $mh = curl_multi_init();
        $curl_array = array();
        foreach ($blocks as $identifer => $block) {

            $url = Mage::helper('core/url')->getCurrentUrl();
            $url = Mage::helper('core/url')->addRequestParam($url, array('async_block_id' => $block->getCacheKey()));

            $curl_array[$identifer] = curl_init($url);
            curl_setopt($curl_array[$identifer], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$identifer], CURLOPT_TIMEOUT, 1);
            //curl_setopt($curl_array[$identifer], CURLOPT_TIMEOUT, 10);
            curl_multi_add_handle($mh, $curl_array[$identifer]);
        }
        $running = NULL;
        do {
            usleep(10000);
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $res = array();
        foreach ($blocks as $identifer => $block) {
            $res[$identifer] = curl_multi_getcontent($curl_array[$identifer]);
        }
        foreach ($blocks as $identifer => $block) {
            curl_multi_remove_handle($mh, $curl_array[$identifer]);
        }
        curl_multi_close($mh);
        return $res;
    }
}
