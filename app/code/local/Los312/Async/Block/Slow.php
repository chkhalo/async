<?php

class Los312_Async_Block_Slow extends Mage_Core_Block_Template
{
    public function getLoading($sec){
        sleep($sec);
        return $sec;
    }
}