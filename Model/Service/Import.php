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

namespace Shopgate\Import\Model\Service;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Shopgate\Base\Api\Config\SgCoreInterface;
use Shopgate\Base\Api\OrderRepositoryInterface;
use Shopgate\Base\Model\Shopgate\Extended\Base;
use Shopgate\Import\Api\ImportInterface;
use Shopgate\Import\Helper\Customer\Setter as CustomerSetter;
use Shopgate\Import\Helper\Order as OrderSetter;
use ShopgateCustomer;

class Import implements ImportInterface
{

    /** @var CustomerSetter */
    private $customerSetter;
    /** @var SgCoreInterface */
    private $config;
    /** @var StoreManagerInterface */
    private $storeManager;
    /** @var Base */
    private $order;
    /** @var OrderSetter */
    private $orderSetter;
    /** @var ResourceConnection */
    private $resourceConnection;
    /** @var array */
    private $addOrderMethods;
    /** @var array */
    private $updateOrderMethods;
    /** @var OrderRepositoryInterface */
    private $sgOrderRepository;

    /**
     * @param CustomerSetter           $customerSetter
     * @param OrderSetter              $orderSetter
     * @param SgCoreInterface          $config
     * @param StoreManagerInterface    $storeManager
     * @param Base                     $order
     * @param ResourceConnection       $resourceConnection
     * @param OrderRepositoryInterface $sgOrderRepository
     * @param array                    $addOrderMethods    - methods loaded via DI.xml
     * @param array                    $updateOrderMethods - methods loaded via DI.xml
     */
    public function __construct(
        CustomerSetter $customerSetter,
        OrderSetter $orderSetter,
        SgCoreInterface $config,
        StoreManagerInterface $storeManager,
        Base $order,
        ResourceConnection $resourceConnection,
        OrderRepositoryInterface $sgOrderRepository,
        $addOrderMethods = [],
        $updateOrderMethods = []
    ) {
        $this->customerSetter     = $customerSetter;
        $this->config             = $config;
        $this->storeManager       = $storeManager;
        $this->order              = $order;
        $this->orderSetter        = $orderSetter;
        $this->addOrderMethods    = $addOrderMethods;
        $this->resourceConnection = $resourceConnection;
        $this->updateOrderMethods = $updateOrderMethods;
        $this->sgOrderRepository  = $sgOrderRepository;
    }

    /**
     * @inheritdoc
     */
    public function registerCustomer($action, $shopNumber, $user, $pass, $traceId, $userData)
    {
        $this->storeManager->setCurrentStore($this->config->getStoreId($shopNumber));
        $sgCustomer = new ShopgateCustomer($userData);

        if (isset($userData['addresses']) && is_array($userData['addresses'])) {
            $addresses = [];
            foreach ($userData['addresses'] as $address) {
                $addresses[] = new \ShopgateAddress($address);
            }
            $sgCustomer->setAddresses($addresses);
        }

        $this->registerCustomerRaw($user, $pass, $sgCustomer);
    }

    /**
     * @inheritdoc
     */
    public function registerCustomerRaw($user, $pass, ShopgateCustomer $customer)
    {
        $this->customerSetter->registerCustomer($user, $pass, $customer);
    }

    /**
     * @inheritdoc
     */
    public function addOrder($order)
    {
        $this->order->loadArray($order->toArray());

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            $mageOrder = $this->orderSetter->loadMethods($this->addOrderMethods);
            $this->sgOrderRepository->createAndSave($mageOrder->getId());
            $connection->commit();
        } catch (\ShopgateLibraryException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \ShopgateLibraryException(
                \ShopgateLibraryException::UNKNOWN_ERROR_CODE,
                "{$e->getMessage()}\n{$e->getTraceAsString()}",
                true
            );
        }

        return [
            'external_order_id'     => $mageOrder->getId(),
            'external_order_number' => $mageOrder->getIncrementId()
        ];
    }

    /**
     * @inheritdoc
     */
    public function updateOrder($order)
    {
        $this->order->loadArray($order->toArray());

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            $mageOrder = $this->orderSetter->loadMethods($this->updateOrderMethods);
            $connection->commit();
        } catch (\ShopgateLibraryException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \ShopgateLibraryException(
                \ShopgateLibraryException::UNKNOWN_ERROR_CODE,
                "{$e->getMessage()}\n{$e->getTraceAsString()}",
                true
            );
        }

        return [
            'external_order_id'     => $mageOrder->getId(),
            'external_order_number' => $mageOrder->getIncrementId()
        ];
    }
}
