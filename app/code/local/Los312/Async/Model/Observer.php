<?php

class Los312_Async_Model_Observer
{

    protected $_asyncBlocksHtml = array();
    protected $_asyncBlockList = array();
    protected $_adapter = null;

    public function getAdapter()
    {

        $this->_adapter = Mage::getModel(Mage::getConfig('los312_async/los312_async_advanced/adapter'));
        return $this->_adapter;
    }

    /* Render one block for curl request */

    public function asyncBlockRender($observer)
    {

        $this->getAdapter()->blockRender();
    }

    public function asyncRenderBlocks($observer)
    {

        $asyncBlockId = Mage::app()->getRequest()->getParam('async_block_id');
        if ($asyncBlockId) {
            return false;
        }
        $blocks = Mage::app()->getLayout()->getAllBlocks();

        foreach ($blocks as $name => $block) {
            if ($block->getAsync()) {
                $cacheKey = $block->getCacheKey();
                Mage::log('!  Async Block generate key:' . $name . ' class: ' . get_class($block) . ' $cacheKey: ' . $cacheKey);
                $this->_asyncBlockList[$block->getCacheKey()] = $block;
            }
        }
        if (!empty($this->_asyncBlockList)) {

            $this->getAdapter()->sendBlocksToRemoteRender($this->_asyncBlockList);
            //$this -> sendBlocksToRemoteRender();
        }
    }

    public function asyncInsertBlocks($observer)
    {

        $asyncBlockId = Mage::app()->getRequest()->getParam('async_block_id');
        if ($asyncBlockId) {
            return false;
        }
        if (empty($this->_asyncBlockList)) {
            return false;
        }

        Varien_Profiler::enable();
        Varien_Profiler::start('asyncInsertBlocks');


        $response = Mage::app()->getResponse();
        $body = $response->getBody();



        //$blocksHtml = $this->getRemoteRendedBlocks();
        $blocksHtml = $this->getAdapter()->getRemoteRendedBlocks($this->_asyncBlockList);

        foreach ($this->_asyncBlockList as $identifer => $block) {
            Mage::log('!  Async Block Insert by Key:' . $identifer);
            $html = '';
            if (!empty($blocksHtml[$identifer])) {
                $html = $blocksHtml[$identifer];
            }
            $body = str_replace('{{' . $identifer . '}}', $html, $body);
        }

        Varien_Profiler::stop('asyncInsertBlocks');
        $time = Varien_Profiler::fetch('asyncInsertBlocks', 'sum');
        Mage::log('All time of asyncInsertBlocks time::' . $time);
        Varien_Profiler::disable();

        $response->setBody($body);
    }

    public function asyncProfilerStop()
    {
        $timerName = 'Loading page';
        Varien_Profiler::stop($timerName);

        $time = Varien_Profiler::fetch($timerName, 'sum');

        $asyncBlockId = Mage::app()->getRequest()->getParam('async_block_id');
        if (!$asyncBlockId) {
            Mage::log($timerName . '::time::' . $time);
            Mage::log('=========================================End of page async================================================');
        } else {
            Mage::log('|    ' . $timerName . '::time::' . $time);
        }

        Varien_Profiler::disable();
    }

}

