<?php


class Los312_Async_DownloaderController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $message = '|AJAX| Start DownloaderControlle';
        Mage::log($message);
        $paramName = Los312_Async_Model_Abstract::AJAX_DOWNLOAD_BLOCK_IDENTIFER;
        $identifer = Mage::app()->getRequest()->getParam($paramName, false);
        
        $html = 'error';
        if ($identifer) {
           //$html = Mage::getModel('los312_async/storage')->getBlockHtml($identifer);
           //$html = Mage::getModel('los312_async/storage')->getStorage()->getBlockHtml($identifer);
           $html = Mage::getModel('los312_async/storage')->getStorage()->waitBlockHtml($identifer);
        }
        $message = '|AJAX| End DownloaderControlle';
        Mage::log($message);         
        Mage::app()->getResponse()->setBody($html)->sendResponse();
       
        exit;
    }

}