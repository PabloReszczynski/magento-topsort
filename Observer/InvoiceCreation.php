<?php
/**
 * Topsort Magento Extension
 *
 * @copyright Copyright (c) Topsort 2022 - All Rights Reserved
 * @author Kyrylo Kostiukov <kyrylo.kostiukov@bimproject.net>
 * @license OSL-3.0
 */
namespace Topsort\Integration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Topsort\Integration\Model\Api;

class InvoiceCreation implements ObserverInterface
{
    /**
     * @var Api
     */
    private $topsortApi;

    function __construct(
        Api $topsortApi
    )
    {
        $this->topsortApi = $topsortApi;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getData('invoice');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');

        $items = [];

        foreach ($invoice->getItems() as $invoiceItem) {
            $items[] = [
                'id' => $invoiceItem->getProductId(),
                'sku' => $invoiceItem->getSku(),
                'quantity' => $invoiceItem->getQty(),
                'price' => $invoiceItem->getPrice()
            ];
        }


        $this->topsortApi->trackPurchase(
            $order->getIncrementId(),
            $order->getQuoteId(),
            $items
        );
    }
}
