<?php
/**
 * SwiftOtter_Base is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SwiftOtter_Base is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with SwiftOtter_Base. If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright: 2013 (c) SwiftOtter Studios
 *
 * @author Joseph Maxwell
 * @copyright Swift Otter Studios, 7/15/14
 * @package default
 **/


class SwiftOtter_InventoryCount_Block_OpenOrders extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct(){
        parent::__construct();
        $this->_controller = 'OpenOrders';
        $this->_blockGroup = 'SwiftOtter_InventoryCount';

        $headerAdditional = '';
        if ($product = Mage::registry('current_product')) {
            $headerAdditional .= sprintf(' for %s (%s)', $product->getName(), $product->getSku());

            $this->_addButton('back', array(
                'label'     => $this->__('Back to %s', $product->getName()),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/catalog_product/edit', array('id' => $product->getId(), 'tab' => 'product_info_tabs_inventory')) .'\')',
                'class'     => 'back',
            ));

            $this->_addButton('reindex', array(
                'label'     => $this->__('Reindex Open Order Count', $product->getName()),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/openorders/reindex', array('product_id' => $product->getId())) .'\')',
                'class'     => 'add',
            ));
        }

        $this->_headerText = Mage::helper('SwiftOtter_InventoryCount')->__('Open Orders' . $headerAdditional);

        $this->removeButton('add');


    }

    protected function _prepareLayout() {
        $this->setChild('grid', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_Grid', $this->_controller . '.Grid')->setSaveParametersInSession(true));
    }
}