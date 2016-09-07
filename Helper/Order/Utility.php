<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain
 * unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

namespace Shopgate\Import\Helper\Order;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as StatusCollectionFactory;
use Shopgate\Base\Model\Shopgate\OrderFactory;
use ShopgateLibraryException;

class Utility
{
    /** @var OrderFactory */
    protected $sgOrderFactory;
    /** @var StatusCollectionFactory */
    private $statusCollectionFactory;

    /**
     * Utility constructor.
     *
     * @param OrderFactory            $sgOrderFactory
     * @param StatusCollectionFactory $statusCollectionFactory
     */
    public function __construct(
        OrderFactory $sgOrderFactory,
        StatusCollectionFactory $statusCollectionFactory
    ) {
        $this->sgOrderFactory          = $sgOrderFactory;
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * @param $orderNumber
     *
     * @throws ShopgateLibraryException
     */
    public function checkOrderAlreadyExists($orderNumber)
    {
        $sgOrder = $this->sgOrderFactory->create()->load($orderNumber, 'shopgate_order_number');
        if ($sgOrder->getId() !== null) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DUPLICATE_ORDER,
                'orderId: ' . $orderNumber, true
            );
        }
    }

    /**
     * Returns the state for the given status
     *
     * @param string $status
     *
     * @return string
     */
    public function getStateForStatus($status)
    {
        $statusCollection = $this->statusCollectionFactory->create();
        $statusCollection->joinStates();
        $statusCollection->getSelect()->where('state_table.status=?', $status);
        $statusCollection->getSelect()->where('state_table.is_default=?', 1);
        $state = $statusCollection->getFirstItem();

        return $state->getData('state');
    }
}
