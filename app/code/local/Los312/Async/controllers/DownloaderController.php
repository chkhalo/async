<?php


class Los312_Async_DownloaderController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $paramName = Los312_Async_Model_Abstract::AJAX_DOWNLOAD_BLOCK_IDENTIFER;
        $identifer = Mage::app()->getRequest()->getParam($paramName, false);
        
        $html = 'error';
        if ($identifer) {
           $html = Mage::getModel('los312_async/storage')->getBlockHtml($identifer);            
        }
        Mage::app()->getResponse()->setBody($html)->sendResponse();
        exit;
    }

}