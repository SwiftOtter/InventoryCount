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

class SwiftOtter_InventoryCount_Model_Resource_Order_Item_Collection extends Mage_Sales_Model_Resource_Order_Item_Collection
{
    const EVENT_OPEN_ORDER_DISQUALIFIER = 'swiftotter_inventory_count_open_order_disqualifier';

    public function getSoldItems($after)
    {
        $this->addFieldToFilter('created_at', array('gteq' => $after));

        $select = $this->getSelect();
        $select
            ->reset($select::COLUMNS)
            ->columns(array(
                'product_id',
                'COUNT(item_id) AS affected_items',
                'SUM(qty_ordered) AS qty_ordered'
            ))
            ->group('product_id');

        Mage::log("Select: " . (string)$select);

        Mage::log("Items affected: " . $this->count());

        return $this;
    }

    /**
     * @param Varien_Db_Select $subquery
     * @return int
     */
    public function updateQuantityValues(Varien_Db_Select $subquery)
    {
        $write = Mage::getModel('core/resource')->getConnection('core_write');

        $select = $write->select();

        $select
            ->join(array('values' => new Zend_Db_Expr(sprintf('(%s)', $subquery->assemble()))), 'stock_item.product_id = values.product_id', array())
            ->columns(
                array(
                    'qty' => new Zend_Db_Expr('`stock_item`.qty_on_hand - `values`.qty')
                )
            );

        $update = $write->updateFromSelect($select, array('stock_item' => $this->getTable('cataloginventory/stock_item')));

        Mage::log((string)$update);

        $rowsAffected = 0;
        $result = null;

        if ($write) {
            try {
                $write->beginTransaction();

                $result = $write->query($update);

                $write->commit();
            } catch (Exception $ex) {
                Mage::log($ex);
                var_dump($ex);
                $write->rollBack();
            }
        }

        if ($result) {
            $rowsAffected = $result->rowCount();
        }

        return $rowsAffected;
    }

    public function getOpenOrders($productIds)
    {
        $select = $this->getSelect();
        $select->reset($select::COLUMNS);

        $this->join(array(
                'order' => 'sales/order'
            ),
            'entity_id = order_id',
            array(
                'order_id' => 'entity_id',
                'order_created_at' => 'created_at',
                'customer_name' => new Zend_Db_Expr('CONCAT(`order`.customer_firstname, " ", `order`.customer_lastname)'),
                'increment_id',
                'status',
            )
        );

        $this->addFieldToFilter('main_table.product_id', array('in' => $productIds));

        $states = array(
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_HOLDED,
            Mage_Sales_Model_Order::STATE_COMPLETE
        );

        $transport = new Varien_Object();
        $transport->setValues($states);
        Mage::dispatchEvent(self::EVENT_OPEN_ORDER_DISQUALIFIER, array('transport' => $transport));

        $this->addFieldToFilter('order.state', array('nin' => $transport->getValues()));

        if (is_array($transport->getCriteria())) {
            foreach ($transport->getCriteria() as $column => $qualifier) {
                $this->addFieldToFilter($column, $qualifier);
            }
        }

        $select->columns(
            array(
                'product_id',
                'qty' => '(SUM(main_table.qty_ordered) - SUM(main_table.qty_shipped) - SUM(main_table.qty_canceled) - SUM(main_table.qty_refunded))'
            )
        );

        $select->having(new Zend_Db_Expr('(SUM(main_table.qty_ordered) - SUM(main_table.qty_shipped) - SUM(main_table.qty_canceled) - SUM(main_table.qty_refunded)) > 0'));
        $select->group('order.entity_id');

        return $this;
    }

    public function getSelectCountSql()
    {
        $sql = parent::getSelectCountSql()->assemble();
        $select = $this->getConnection()->select();
        $select->from(
            array('counted' => new Zend_Db_Expr(sprintf('(%s)', $sql))), new Zend_Db_Expr('COUNT(*)')
        );

        return $select;
    }


    public function getOpenOrderItemsQuery($productIds)
    {
        $select = $this->getSelect();
        $this->join(array(
                'order' => 'sales/order'
            ),
            'entity_id = order_id',
            array()
        );

        $this->addFieldToFilter('main_table.product_id', array('in' => $productIds));

        /**
         * The filter for order states may be unnecessary, as we need to work with all orders.
         */
        $states = array(
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_HOLDED,
            Mage_Sales_Model_Order::STATE_COMPLETE
        );

        $transport = new Varien_Object();
        $transport->setValues($states);
        Mage::dispatchEvent(self::EVENT_OPEN_ORDER_DISQUALIFIER, array('transport' => $transport));

        $this->addFieldToFilter('order.state', array('nin' => $transport->getValues()));

        if (is_array($transport->getCriteria())) {
            foreach ($transport->getCriteria() as $column => $qualifier) {
                $this->addFieldToFilter($column, $qualifier);
            }
        }

        $select->reset($select::COLUMNS);
        $select->columns(
            array(
                'input_product_id' => 'product_id',
                'input_qty' => '(SUM(main_table.qty_ordered) - SUM(main_table.qty_shipped) - SUM(main_table.qty_canceled) - SUM(main_table.qty_refunded))'
            )
        );

        $select->having(new Zend_Db_Expr('(SUM(main_table.qty_ordered) - SUM(main_table.qty_shipped) - SUM(main_table.qty_canceled) - SUM(main_table.qty_refunded)) > 0'));
        $select->group('main_table.product_id');

        $output = clone $select;
        $output->reset();

        $output->from(array('product' => $this->getTable('catalog/product')));
        $output->reset($output::COLUMNS);

        $output->joinLeft(array(
            'main_table' => new Zend_Db_Expr('(' . (string)$select . ')')
            ),
            'main_table.input_product_id = product.entity_id',
            array()
        );

        $output->columns(array(
            'product_id' => 'entity_id',
            'qty' => new Zend_Db_Expr('IF (`main_table`.`input_qty` IS NULL, 0, `main_table`.`input_qty`)')
        ));

        $output->where($select->getAdapter()->quoteInto('product.entity_id IN (?)', $productIds));

//        echo (string)$output;

        return $output;
    }

}