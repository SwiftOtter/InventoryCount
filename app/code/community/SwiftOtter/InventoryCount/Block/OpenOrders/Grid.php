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


class SwiftOtter_InventoryCount_Block_OpenOrders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('open_order_grid')
             ->setDefaultSort('increment_id')
             ->setDefaultDir('desc')
             ->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $productId = Mage::registry('current_product_id');

        if ($productId) {
            $collection = Mage::getResourceModel('SwiftOtter_InventoryCount/Order_Item_Collection')->getOpenOrders(array($productId));
            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'	=> $this->__('ID'),
            'type'      => 'number',
            'align'		=> 'center',
            'index'		=> 'increment_id',
        ));

        $this->addColumn('customer_name', array(
            'header'	=> $this->__('Customer Name'),
            'align'		=> 'center',
            'index'		=> 'customer_name',
        ));

        $this->addColumn('qty', array(
            'header'	=> $this->__('Ordered Qty'),
            'align'		=> 'center',
            'type'      => 'number',
            'index'		=> 'qty'
        ));

        $this->addColumn('order_created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'order_created_at',
            'type' => 'datetime',
            'width' => '200px',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '120px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));



        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '100px',
                'type'      => 'action',
                'getter'     => 'getOrderId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('View Order'),
                        'url'     => array(
                            'base'=>'*/sales_order/view',
                        ),
                        'field'   => 'order_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
            ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/sales_order/view', array ('order_id' => $row->getOrderId()));
    }
}