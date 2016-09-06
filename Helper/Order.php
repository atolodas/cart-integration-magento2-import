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

namespace Shopgate\Import\Helper;

use Magento\Framework\Registry;
use Magento\Quote\Api\CartManagementInterface;
use Shopgate\Base\Model\Shopgate\Extended\Base;
use Shopgate\Base\Model\Utility\SgLoggerInterface;
use Shopgate\Import\Helper\Order\Utility;

class Order
{

    /** @var Utility */
    private $utility;
    /** @var Base */
    private $order;
    /** @var SgLoggerInterface */
    private $log;
    /** @var Quote */
    private $quote;
    /** @var array */
    private $quoteMethods;
    /** @var CartManagementInterface */
    private $quoteManagement;
    /** @var Registry */
    private $registry;

    /**
     * Order constructor.
     *
     * @param Utility                 $utility
     * @param Base                    $order
     * @param SgLoggerInterface       $log
     * @param Quote                   $quote
     * @param CartManagementInterface $quoteManagement
     * @param Registry                $registry
     * @param array                   $quoteMethods
     */
    public function __construct(
        Utility $utility,
        Base $order,
        SgLoggerInterface $log,
        Quote $quote,
        CartManagementInterface $quoteManagement,
        Registry $registry,
        array $quoteMethods = []
    ) {
        $this->utility         = $utility;
        $this->order           = $order;
        $this->log             = $log;
        $this->quote           = $quote;
        $this->quoteMethods    = $quoteMethods;
        $this->quoteManagement = $quoteManagement;
        $this->registry        = $registry;
    }

    /**
     * @return \Magento\Sales\Model\Order
     *
     * @throws \Exception
     * @throws \ShopgateLibraryException
     */
    public function addOrder()
    {
        $orderNumber = $this->order->getOrderNumber();
        $this->log->debug('## Start to add new Order');
        $this->log->debug('## Order-Number: ' . $orderNumber);

        $this->utility->checkOrderAlreadyExists($orderNumber);
        $this->log->debug('# Add shopgate order to Registry');
        $this->registry->register('shopgate_order', $this->order);
        $this->quote->load($this->quoteMethods);

        $mageQuote = $this->quote->load($this->quoteMethods);

        return $this->quoteManagement->submit($mageQuote);
        // return $this->quoteManagement->placeOrder($this->quote->getMageQuote()->getEntityId());
    }
}
