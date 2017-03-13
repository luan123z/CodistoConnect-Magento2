<?php

/**
 * Codisto Marketplace Sync Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @package   Codisto_Connect
 * @copyright 2016-2017 On Technology Pty. Ltd. (http://codisto.com/)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://codisto.com/connect/
 */

namespace Codisto\Connect\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order;

class CheckoutSubmitObserver implements ObserverInterface
{
    private $codistoHelper;

    public function __construct(
        \Codisto\Connect\Helper\Data $codistoHelper
    ) {
        $this->codistoHelper = $codistoHelper;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $storeId = $order->getStoreId();

        $merchants = $this->codistoHelper->syncMerchantsFromStoreId($storeId);
        if (!empty($merchants)) {
            $syncIds = [];

            foreach ($order->getAllItems() as $item) {
                $syncIds = $this->codistoHelper->addProductToSyncSet($product->getId(), $syncIds);
            }

            if (!empty($syncIds)) {
                $productIds = '';
                if (count($syncIds) == 1) {
                    $productIds = $syncIds[0];
                } else {
                    $productIds = '['.implode(',', $syncIds).']';
                }

                $this->codistoHelper->signal($merchants, 'action=sync&productid='.$productIds, 'update', $syncIds);
            }
        }

        return $this;
    }
}
