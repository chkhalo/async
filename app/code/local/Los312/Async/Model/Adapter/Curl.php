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
        //die('$timelimitConfig:'.$timelimitConfig);
        $timelimit = 1000000*$timelimitConfig;
        $timelimit = 100000*20;
        
        //$timelimit = 1000000*5;
        Mage::log('WAIT start=================');
        $wait = true;
        do {
            usleep(100000);
            $time += 100000 ;
            Mage::log('time:'.$time);
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
            if($time>$timelimit){
                $wait = false;
                Mage::log('WAIT abort=================');
            }
            
        } while ($wait);
        
         Mage::log('WAIT close=================');

        return $_asyncBlocksHtml;

    }

    public function multipleThreadsRequest($blocks)
    {
        $mh = curl_multi_init();
        $curl_array = array();
        foreach ($blocks as $identifer => $block) {
            $url = Mage::helper('core/url')->getCurrentUrl();
            $url = Mage::helper('core/url')->addRequestParam($url, array('async_block_identifer' => $block->getCacheKey()));
           // Mage::log('multipleThreadsRequest::url '.$url);

            $curl_array[$identifer] = curl_init($url);
            curl_setopt($curl_array[$identifer], CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($curl_array[$identifer], CURLOPT_TIMEOUT, 1);
            curl_setopt($curl_array[$identifer], CURLOPT_TIMEOUT_MS, 300);
            curl_multi_add_handle($mh, $curl_array[$identifer]);
        }
        $running = NULL;
        //curl_multi_exec($mh, $running);
        //return;
        $time = 0;
        Mage::log('CURL start=================');
        //curl_multi_exec($mh, $running);
        do {
            $time += 100000 ;
            Mage::log('time:'.$time);
            usleep(100000);
            curl_multi_exec($mh, $running);
        } while ($running > 0);
//
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
