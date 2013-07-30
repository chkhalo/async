<?php

class Los312_Async_Model_System_Config_Storage
{

    protected $_options;

    public function toOptionArray($isMultiselect = false)
    {

        if (!$this->_options) {
            $options = array();
            $adapters = Mage::getConfig()->getNode('global/async_storages')->asCanonicalArray();
            foreach ($adapters as $adapter) {
                $options[] = array(
                    'value' => $adapter['class'],
                    'label' => $adapter['name']
                );
            }
            $this->_options = $options;
        }
        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, array('value' => '', 'label' => Mage::helper('adminhtml')->__('--Please Select--')));
        }

        return $options;
    }

}

