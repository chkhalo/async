<?php

class Los312_Async_Model_Storage_Default extends Los312_Async_Model_Abstract
{
    public function getCacheKey($identifer)
    {
        $cookie = Mage::app()->getCookie()->get('frontend');
        $key = self::CACHE_PREFIX . $cookie . '_' . $identifer;
        return $key ;
    }   
    
    public function saveBlockHtml($identifer, $html)
    {
        
        $message = '|    saveBlockHtml '.$this->getCacheKey($identifer). '   '.$html;
        Mage::log($message);
        
        $cache = Mage::app()->getCache();
        $cache->save($html, $this->getCacheKey($identifer), array("asyncBlock"), self::CACHE_LIMIT);
        
        //$cache->save($html, self::CACHE_PREFIX.$asyncBlockIdentifer, array("asyncBlock"), self::CACHE_LIMIT);
    }

    public function removeBlockHtml($identifer)
    {
        $cache = Mage::app()->getCache();
        $cache->remove($this->getCacheKey($identifer));
    }
    /**
     * Index action
     */
    public function getBlockHtml($identifer)
    {
        $message = '|    getBlockHtml '.$this->getCacheKey($identifer);
        Mage::log($message);
        $cache = Mage::app()->getCache();
        //$result = $cache->load(self::CACHE_PREFIX . $identifer);
        $result = $cache->load($this->getCacheKey($identifer));
        if ($result !== false) {
                $this->removeBlockHtml($identifer);
                return $result;
        }
        //return $result;
        return 'empty 5';
    }
    
    public function waitBlockHtml($identifer)
    {
        $cache = Mage::app()->getCache();
        /*wait*/
        $time = 0;
        $timelimitConfig = (int) Mage::getStoreConfig('los312_async/los312_async_advanced/remout_ajax_time_limit');
        $timelimit = 1000000 * $timelimitConfig;

        set_time_limit($timelimit);

        do {
            usleep(self::SLEEP_INTERVAL);
            $time += self::SLEEP_INTERVAL;
            /*getBlockHtml($identifer)*/
            $result = $cache->load($this->getCacheKey($identifer));
            if ($result !== false) {
                $this->removeBlockHtml($identifer);
                //$cache->remove(self::CACHE_PREFIX . $identifer);
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
                    //$result = $cache->load(self::CACHE_PREFIX . $identifer);
                            $message = '|    getRemoteRendedBlocks '.$this->getCacheKey($identifer). '   ';
                            Mage::log($message);
                    $result = $cache->load($this->getCacheKey($identifer));
                    if ($result !== false) {
                        $_asyncBlocksHtml[$identifer] = $result;
                        $this->removeBlockHtml($identifer);
                        //$cache->remove(self::CACHE_PREFIX . $identifer);
                    } else {
                        $wait = true;
                    }
                }
            }

        } while (($time < $timelimit)&&$wait);

        $timeTmp = $time/1000000;
        
        $message = '|    End WAIT getRemoteRendedBlocks$timeTmp '.$timeTmp;
        Mage::log($message);

        return $_asyncBlocksHtml;
    }
    
    
    
  

}

