<?php

class Los312_Async_Model_Storage_Default extends Los312_Async_Model_Abstract
{

    /**
     * Index action
     */
    public function getBlockHtml($identifer)
    {
        $cache = Mage::app()->getCache();
        $result = $cache->load(self::CACHE_PREFIX . $identifer);

        $time = 0;
        $timelimitConfig = (int) Mage::getStoreConfig('los312_async/los312_async_advanced/remout_ajax_time_limit');
        $timelimit = 1000000 * $timelimitConfig;

        set_time_limit($timelimit);

        do {
            usleep(self::SLEEP_INTERVAL);
            $time += self::SLEEP_INTERVAL;
            $result = $cache->load(self::CACHE_PREFIX . $identifer);
            if ($result !== false) {
                $cache->remove(self::CACHE_PREFIX . $identifer);
                return $result;
            }
        } while ($time < $timelimit);
        return 'empty';
    }

    public function getRemoteRendedBlocks($blocks)
    {
        $cache = Mage::app()->getCache();
        $_asyncBlocksHtml = array();
        foreach ($blocks as $identifer => $block) {
            $_asyncBlocksHtml[$identifer] = false;
        }
        $time = 0;
        /* sec */
        $timelimitConfig = (int) Mage::getStoreConfig('los312_async/los312_async_advanced/waiting_time_limit');
        //die('$timelimitConfig:'.$timelimitConfig);
        $timelimit = 1000000 * $timelimitConfig;
        //$timelimit = 100000*20;
        //$timelimit = 1000000*5;
        
        $message = '|    Start WAIT  getRemoteRendedBlocks timelimit '.$timelimitConfig;
        Mage::log($message);

        $wait = true;
        do {
            usleep(self::SLEEP_INTERVAL);
            $time += self::SLEEP_INTERVAL;

            $wait = false;
            foreach ($blocks as $identifer => $block) {
                if ($_asyncBlocksHtml[$identifer] === false) {
                    $result = $cache->load(self::CACHE_PREFIX . $identifer);
                    if ($result !== false) {
                        $_asyncBlocksHtml[$identifer] = $result;
                        $cache->remove(self::CACHE_PREFIX . $identifer);
                    } else {
                        $wait = true;
                    }
                }
            }
//            if($time>$timelimit){
//                $wait = false;
//                Mage::log('WAIT abort=================');
//            }
        } while (($time < $timelimit)&&$wait);

        $timeTmp = $time/1000000;
        
        $message = '|    End WAIT getRemoteRendedBlocks$timeTmp '.$timeTmp;
        Mage::log($message);

        return $_asyncBlocksHtml;
    }

}

