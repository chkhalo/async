<?php

class Los312_Async_Model_Storage  extends Los312_Async_Model_Abstract
{
    public function getBlockHtml($identifer){
        
        $message = '|------------Start WAIT  ajax';
        Mage::log($message);
        
        $html = $this->getStorage()->getBlockHtml($identifer);
        
        $message = '|    End WAIT  getRemoteRendedBlocks timelimit ';
        Mage::log($message);        
        
        return $html;
    }
}
