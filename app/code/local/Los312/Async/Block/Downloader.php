<?php

class Los312_Async_Block_Downloader extends Mage_Core_Block_Template
{   
    public function getAsyncUrl(){
        $url = Mage::getUrl('async/downloader');
        return $url;
    }    
}