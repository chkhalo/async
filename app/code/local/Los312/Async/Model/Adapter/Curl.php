<?php
class Los312_Async_Model_Adapter_Curl
{
    
    static $remouteUrls = array();
    /*interfeise*/
    public function sendBlocksToRemoteRender($blocks)
    {
        $result = $this->multipleThreadsRequest($blocks);
        return $result;
    }
/*
    public function multipleThreadsRequest2($blocks)
    {
        
        $message = '|    Start Multycurl';
        Mage::log($message);
        
        $mh = curl_multi_init();
        $curl_array = array();
        foreach ($blocks as $identifer => $block) {
            $url = Mage::helper('core/url')->getCurrentUrl();
            $url = Mage::helper('core/url')->addRequestParam($url, array('async_block_identifer' => $block->getCacheKey()));
            self::$remouteUrls[$identifer] = $url;
            
           Mage::log('multipleThreadsRequest::url '.$url);
            
            $curl_array[$identifer] = curl_init($url);
            
            curl_setopt($curl_array[$identifer], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$identifer], CURLOPT_COOKIE, 'frontend='.Mage::app()->getCookie()->get('frontend'));
            
            //curl_setopt($curl_array[$identifer], CURLOPT_TIMEOUT, 1);
            curl_setopt($curl_array[$identifer], CURLOPT_TIMEOUT_MS, 400);
            curl_multi_add_handle($mh, $curl_array[$identifer]);
        }
        $running = NULL;
        $time = 0;
        do {
            $time += 100000 ;
            usleep(100000);
            curl_multi_exec($mh, $running);
        } while ($running > 0);
        
//        curl_multi_exec($mh, $running);
//        curl_multi_exec($mh, $running);
//        curl_multi_exec($mh, $running);
        
        $res = array();
//        foreach ($blocks as $identifer => $block) {
//            Mage::log('curl_multi_getcontent::identifer '.$identifer);
//            $res[$identifer] = curl_multi_getcontent($curl_array[$identifer]);
//        }
//       
        
        foreach ($blocks as $identifer => $block) {
            curl_multi_remove_handle($mh, $curl_array[$identifer]);
        }
        curl_multi_close($mh);

        
        $timeTmp = $time/1000000;        
        $message = '|    End Multycurl $timeTmp'.$timeTmp;
        //die;
        Mage::log($message);
        return $res;
        
    }*/
   /*working*/ 
 public function multipleThreadsRequest($blocks)
    {
        $mh = curl_multi_init();
        $curl_array = array();
        foreach ($blocks as $identifer => $block) {
            $url = Mage::helper('core/url')->getCurrentUrl();
            $url = Mage::helper('core/url')->addRequestParam($url, array('async_block_identifer' => $block->getCacheKey()));
            self::$remouteUrls[$identifer] = $url;
            
           // Mage::log('multipleThreadsRequest::url '.$url);

            $curl_array[$identifer] = curl_init($url);
            curl_setopt($curl_array[$identifer], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$identifer], CURLOPT_TIMEOUT, 100);
            //curl_setopt($curl_array[$identifer], CURLOPT_TIMEOUT_MS, 300);
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
           // Mage::log('time:'.$time);
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
        
//        var_dump($res);
//        die;
        return $res;
    }    
}
