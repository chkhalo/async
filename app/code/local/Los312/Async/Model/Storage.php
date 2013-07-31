<?php

class Los312_Async_Model_Storage  extends Los312_Async_Model_Abstract
{
    public function getBlockHtml($identifer){
        $html = $this->getStorage()->getBlockHtml($identifer);
        return $html;
    }
}
