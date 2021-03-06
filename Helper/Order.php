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

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order as MageOrder;
use Magento\Sales\Model\OrderNotifier;
use Magento\Sales\Model\OrderRepository;
use Shopgate\Base\Api\Config\CoreInterface;
use Shopgate\Base\Api\OrderRepositoryInterface;
use Shopgate\Base\Model\Shopgate;
use Shopgate\Base\Model\Shopgate\Extended\Base;
use Shopgate\Base\Model\Utility\SgLoggerInterface;
use Shopgate\Import\Helper\Order\Shipping;
use Shopgate\Import\Helper\Order\Utility;
use Shopgate\Import\Model\Service\Import as ImportService;

class Order
{

    /** @var Utility */
    private $utility;
    /** @var Base */
    private $sgOrder;
    /** @var SgLoggerInterface */
    private $log;
    /** @var Quote */
    private $quote;
    /** @var array */
    private $quoteMethods;
    /** @var CartManagementInterface | QuoteManagement */
    private $quoteManagement;
    /** @var OrderRepository */
    private $orderRepository;
    /** @var MageOrder */
    private $mageOrder;
    /** @var OrderRepositoryInterface */
    private $sgOrderRepository;
    /** @var CoreInterface */
    private $config;
    /** @var OrderNotifier */
    private $orderNotifier;
    /** @var Shopgate\Order */
    private $localSgOrder;
    /** @var QuoteRepository */
    private $quoteRepository;
    /** @var Shipping */
    private $shippingHelper;
    /** @var ManagerInterface */
    private $eventManager;

    /**
     * @param Utility                  $utility
     * @param Base                     $order
     * @param SgLoggerInterface        $log
     * @param Quote                    $quote
     * @param CartManagementInterface  $quoteManagement
     * @param OrderRepository          $orderRepository
     * @param MageOrder                $mageOrder
     * @param OrderRepositoryInterface $sgOrderRepository
     * @param CoreInterface            $config
     * @param OrderNotifier            $orderNotifier
     * @param QuoteRepository          $quoteRepository
     * @param Shopgate\Order           $localSgOrder
     * @param Shipping                 $shippingHelper
     * @param ManagerInterface         $eventManager
     * @param array                    $quoteMethods
     */
    public function __construct(
        Utility $utility,
        Base $order,
        SgLoggerInterface $log,
        Quote $quote,
        CartManagementInterface $quoteManagement,
        OrderRepository $orderRepository,
        MageOrder $mageOrder,
        OrderRepositoryInterface $sgOrderRepository,
        CoreInterface $config,
        OrderNotifier $orderNotifier,
        QuoteRepository $quoteRepository,
        Shopgate\Order $localSgOrder,
        Shipping $shippingHelper,
        ManagerInterface $eventManager,
        array $quoteMethods = []
    ) {
        $this->utility           = $utility;
        $this->sgOrder           = $order;
        $this->log               = $log;
        $this->quote             = $quote;
        $this->quoteMethods      = $quoteMethods;
        $this->quoteManagement   = $quoteManagement;
        $this->orderRepository   = $orderRepository;
        $this->mageOrder         = $mageOrder;
        $this->sgOrderRepository = $sgOrderRepository;
        $this->config            = $config;
        $this->orderNotifier     = $orderNotifier;
        $this->quoteRepository   = $quoteRepository;
        $this->localSgOrder      = $localSgOrder;
        $this->shippingHelper    = $shippingHelper;
        $this->eventManager      = $eventManager;
    }

    /**
     * @param array $methods
     *
     * @return MageOrder
     */
    public function loadMethods(array $methods)
    {
        foreach ($methods as $rawMethod) {
            $method = 'set' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($rawMethod);
            $this->log->debug('Starting method ' . $method);
            $this->{$method}();
            $this->log->debug('Finished method ' . $method);
        }

        return $this->mageOrder;
    }

    /**
     * Creates the order then we can continue loading on $this->mageOrder
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \ShopgateLibraryException
     */
    protected function setStartAdd()
    {
        $orderNumber = $this->sgOrder->getOrderNumber();
        $this->log->debug('## Order-Number: ' . $orderNumber);

        $this->sgOrderRepository->checkOrderExists($orderNumber, true);

        $quote = $this->quote->load($this->quoteMethods);
        $quote->setData('totals_collected_flag', false);

        $this->eventManager->dispatch('checkout_submit_before', ['quote' => $quote]);
        $this->mageOrder = $this->quoteManagement->submit($quote);
        $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $this->mageOrder, 'quote' => $quote]);
    }

    /**
     * Executes after order is fully loaded
     */
    protected function setEndAdd()
    {
        $this->orderRepository->save($this->mageOrder);
    }

    /**
     * Updates the order
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \ShopgateLibraryException
     */
    protected function setStartUpdate()
    {
        $orderNumber = $this->sgOrder->getOrderNumber();
        $this->log->debug('## Order-Number: ' . $orderNumber);
        $this->localSgOrder = $this->sgOrderRepository->checkOrderExists($orderNumber);
        if (!$this->localSgOrder->getShopgateOrderId()) {
            throw new \ShopgateLibraryException(\ShopgateLibraryException::PLUGIN_ORDER_NOT_FOUND);
        }

        $this->mageOrder = $this->orderRepository->get($this->localSgOrder->getOrderId());
    }

    /**
     * Executes after order is fully loaded and updated
     */
    public function setEndUpdate()
    {
        $this->mageOrder->addStatusHistoryComment(__('[SHOPGATE] Order updated by Shopgate.'), false)
                        ->setIsCustomerNotified(false);

        $this->orderRepository->save($this->mageOrder);
        $this->sgOrderRepository->update($this->localSgOrder);
    }

    /**
     * Checks if payment should be updated for order
     */
    protected function setUpdatePayment()
    {
        if ((bool) $this->sgOrder->getUpdatePayment()) {
            $this->log->debug('# Payment requires an update');
            $this->setOrderPayment();
        }
    }

    /**
     * Checks if shipments should be updated for order
     */
    protected function setOrderShipment()
    {
        if ((bool) $this->sgOrder->getUpdateShipping()
            && !$this->sgOrder->getIsShippingCompleted()
            && $this->mageOrder->canShip()
            && !$this->localSgOrder->getIsShippingBlocked()
        ) {
            $this->log->debug('# Shipping requires an update');
            $this->shippingHelper->update($this->mageOrder);
        }
    }

    /**
     * Set correct order status by payment
     */
    protected function setOrderState()
    {
        $orderStatus = $this->mageOrder->getPayment()->getMethodInstance()->getConfigData('order_status');
        $orderState  = $this->utility->getStateForStatus($orderStatus);
        $this->mageOrder->setState($orderState)->setStatus($orderStatus);
    }

    /**
     * Set order status history entries
     */
    protected function setOrderStatusHistory()
    {
        $this->mageOrder->addStatusHistoryComment(
            __('[SHOPGATE] Order added by Shopgate # %1', $this->sgOrder->getOrderNumber()),
            false
        )->setIsCustomerNotified(false);
    }

    /**
     * Manipulate payments according to payment method
     *
     * TODO: once we have factories, move it there
     */
    protected function setOrderPayment()
    {
        if ($this->sgOrder->getIsPaid() && $this->mageOrder->getBaseTotalDue()) {
            $this->mageOrder->getPayment()->setShouldCloseParentTransaction(true);
            $this->mageOrder->getPayment()->registerCaptureNotification($this->sgOrder->getAmountComplete());
            $this->mageOrder->addStatusHistoryComment(__('[SHOPGATE] Payment received.'), false)
                            ->setIsCustomerNotified(false);
        }
    }

    /**
     * Set shipping description from config
     */
    protected function setShippingDescription()
    {
        $shippingInfos = $this->sgOrder->getShippingInfos();
        $shippingTitle = $shippingInfos->getDisplayName();

        $this->mageOrder->setShippingDescription($shippingTitle);

    }

    /**
     * Send order notification if activated in config
     */
    protected function setOrderNotification()
    {
        $this->mageOrder->setEmailSent(0);
        if ($this->config->getConfigByPath(ImportService::PATH_SEND_NEW_ORDER_MAIL)->getValue()) {
            $this->log->debug('# Notified customer about new order');
            $this->orderNotifier->notify($this->mageOrder);
        }
    }

    /**
     * Adds custom fields to magento order & its address fields
     */
    protected function setCustomFields()
    {
        $this->mageOrder->addData($this->sgOrder->customFieldsToArray());
        $billing = $this->mageOrder->getBillingAddress();
        if ($billing) {
            $billing->addData($this->sgOrder->getInvoiceAddress()->customFieldsToArray());
        }
        $shipping = $this->mageOrder->getShippingAddress();
        if ($shipping) {
            $shipping->addData($this->sgOrder->getDeliveryAddress()->customFieldsToArray());
        }
    }
}
