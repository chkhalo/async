<?php
class Los312_Async_Model_Adapter_Curl
{
    /*interfeise*/
    public function sendBlocksToRemoteRender($blocks)
    {
        $this->multipleThreadsRequest($blocks);
    }

    
    
    public function getRemoteRendedBlocks($blocks)
    {
        $cache = Mage::app()->getCache();
        $_asyncBlocksHtml = array();
        foreach ($blocks as $identifer => $block) {
            $_asyncBlocksHtml[$identifer] = false;
        }
        $time = 0;
        $timelimitConfig = (int)Mage::getStoreConfig('los312_async/los312_async_advanced/waiting_time_limit');
        $timelimit = 10000*100*$timelimitConfig;
        $wait = true;
        do {
            usleep(10000);
            //$time += 10000;
            $wait = false;
            foreach ($blocks as $identifer => $block) {
                if($_asyncBlocksHtml[$identifer]===false){
                    $result = $cache->load(Los312_Async_Model_Observer::CACHE_PREFIX.$identifer);
                    if($result!==false){
                        $_asyncBlocksHtml[$identifer]=$result;
                        $cache->remove(Los312_Async_Model_Observer::CACHE_PREFIX.$identifer);
                    } else {
                       $wait = true;
                    }
                }
            }
            if($time>=$timelimit){
               // $wait = false;
            }
            
            
            
        } while ($wait);
        return $_asyncBlocksHtml;

    }

    public function multipleThreadsRequest($blocks)
    {
        $mh = curl_multi_init();
        $curl_array = array();
        foreach ($blocks as $identifer => $block) {
            $url = Mage::helper('core/url')->getCurrentUrl();
            $url = Mage::helper('core/url')->addRequestParam($url, array('async_block_identifer' => $block->getCacheKey()));
            Mage::log('multipleThreadsRequest::url '.$url);

            $curl_array[$identifer] = curl_init($url);
            curl_setopt($curl_array[$identifer], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$identifer], CURLOPT_TIMEOUT, 1);
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
        /*res don't use*/
        Mage::log('CURL CLOSE=================');
        return $res;
    }
}
