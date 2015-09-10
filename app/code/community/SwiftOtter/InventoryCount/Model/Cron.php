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

class SwiftOtter_InventoryCount_Model_Cron
{
    public function reindexUpdatedProducts($preventLogEntry = false)
    {
        Mage::log("Beginning InventoryCount Cron");
        
        if (!$this->_cpuLoadIsGood()) {
            return false;
        }

        Mage::helper('SwiftOtter_InventoryCount')->reindexAllDifferences($preventLogEntry);
        Mage::helper('SwiftOtter_InventoryCount')->reindexNecessary($preventLogEntry);
    }

    protected function _cpuLoadIsGood()
    {
        $loads = sys_getloadavg();
        return array_sum($loads) / count($loads) < 60;
    }
}