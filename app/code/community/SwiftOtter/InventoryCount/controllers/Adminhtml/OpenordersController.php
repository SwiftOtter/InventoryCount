<?php
/**
 *
 *
 * @author Joseph Maxwell
 * @copyright Swift Otter Studios, 3/14/13
 * @package default
 **/

class SwiftOtter_InventoryCount_Adminhtml_OpenordersController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $product = Mage::getModel('catalog/product')->load($productId);

        if ($product->getId()) {
            Mage::register('current_product_id', $this->getRequest()->getParam('product_id'));
            Mage::register('current_product', $product);


            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function reindexAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $product = Mage::getModel('catalog/product')->load($productId);

        if ($product->getId()) {
            Mage::register('current_product_id', $this->getRequest()->getParam('product_id'));
            Mage::register('current_product', $product);

            $orderItemsIndexed = Mage::helper('SwiftOtter_InventoryCount')->reindexSpecific(array($productId));

            if ($orderItemsIndexed > 0) {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s order items were evaluated in the reindex.', $orderItemsIndexed));
            } else {
                Mage::getSingleton('adminhtml/session')->addError($this->__('No order items were evaluated in the reindex.'));
            }

            $this->_redirect('*/openorders/index', array('product_id' => $productId));
        }
    }

    public function reindexallAction()
    {
        $orderItemsIndexed = Mage::helper('SwiftOtter_InventoryCount')->reindexAllDifferences();

        if ($orderItemsIndexed > 0) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s order items were evaluated in the reindex.', $orderItemsIndexed));
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No order items were evaluated in the reindex.'));
        }

        $this->_redirect('*/dashboard');
    }
}