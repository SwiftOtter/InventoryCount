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

class SwiftOtter_InventoryCount_Helper_Data extends SwiftOtter_Base_Helper_Data
{
    /**
     * @return SwiftOtter_InventoryCount_Model_Log
     */
    public function getLatestDate()
    {
        return Mage::getResourceModel('SwiftOtter_InventoryCount/Log')->getLatestEntry();
    }

    public function reindexAllDifferences($preventLogEntry = false)
    {
        $readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $readAdapter->select();
        $select->from($readAdapter->getTableName('cataloginventory_stock_item'), 'product_id')
            ->where('qty <> qty_on_hand');

        $productIds = $readAdapter->fetchCol($select);

        Mage::log($productIds);
        $orderItemsIndexed = $this->_updateOrderQuantities($productIds);

        Mage::log("Items Indexed: " . $orderItemsIndexed);

        if (!$preventLogEntry) {
            $this->_log(1, count($productIds), $orderItemsIndexed);
        }

        return $orderItemsIndexed;
    }

    public function reindexNecessary($preventLogEntry = false)
    {
        $latest = Mage::helper('SwiftOtter_InventoryCount')->getLatestDate();
        Mage::log("Latest: ");
        Mage::log($latest->getData());

        $products = $this->_getAffectedProducts($latest->getCreatedAt());

        Mage::log($products);

        $orderItemsIndexed = $this->_updateOrderQuantities($products);

        Mage::log("Items Indexed: " . $orderItemsIndexed);

        if (!$preventLogEntry) {
            $this->_log(1, count($products), $orderItemsIndexed);
        }
    }

    public function reindexSpecific($productIds, $preventLogEntry = false)
    {
        Mage::log($productIds);
        $orderItemsIndexed = $this->_updateOrderQuantities($productIds);

        Mage::log("Items Indexed: " . $orderItemsIndexed);

        if (!$preventLogEntry) {
            $this->_log(1, count($productIds), $orderItemsIndexed);
        }

        return $orderItemsIndexed;
    }

    protected function _log($status, $productsIndexed, $orderItemsIndexed)
    {
        $log = Mage::getModel('SwiftOtter_InventoryCount/Log');

        $currentTime = Varien_Date::now();
        $log->setCreatedAt($currentTime);
        $log->setStatus($status);
        $log->setProductsIndexed($productsIndexed);
        $log->setOrderItemsIndexed($orderItemsIndexed);

        $log->save();
    }

    protected function _updateOrderQuantities($productIds)
    {
        $query = Mage::getResourceModel('SwiftOtter_InventoryCount/Order_Item_Collection')->getOpenOrderItemsQuery($productIds);
        return Mage::getResourceModel('SwiftOtter_InventoryCount/Order_Item_Collection')->updateQuantityValues($query);
    }

    protected function _getAffectedProducts($from)
    {
        $output = array();
        $orderItems = Mage::getResourceModel('SwiftOtter_InventoryCount/Order_Item_Collection')
            ->getSoldItems($from);

        /** @var Mage_Core_Model_Abstract $item */
        foreach ($orderItems as $item)
        {
            $output[] = $item->getProductId();
        }

        return $output;
    }
}