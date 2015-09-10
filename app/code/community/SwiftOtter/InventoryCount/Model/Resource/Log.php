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



class SwiftOtter_InventoryCount_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('SwiftOtter_InventoryCount/Log', 'id');
    }

    public function getLatestEntry(Mage_Core_Model_Abstract $object = null)
    {


        if (!$object) {
            $object = Mage::getModel($this->_resourceModel . '/' . $this->_mainTable);
        }

        $read = $this->_getReadAdapter();
        if ($read) {
            $select = $read->select();

            $select->from($this->getMainTable(), array('*', 'MAX(created_at)'))
                ->limit(1);

            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $object;
    }

}