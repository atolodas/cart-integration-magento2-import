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
namespace Shopgate\Import\Test\Integration\Model\Service;

use Shopgate\Base\Test\Bootstrap;
use Shopgate\Base\Test\Integration\SgDataManager;

/**
 * @coversDefaultClass Shopgate\Import\Model\Service\Import
 */
class ImportOrderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Shopgate\Import\Helper\Order */
    protected $orderClass;
    /** @var \Shopgate\Import\Model\Service\Import */
    protected $importClass;
    /** @var array - list of created orders to clean up */
    protected $orderHolder = [];
    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    protected $orderRepository;
    /** @var SgDataManager */
    protected $dataManager;

    public function setUp()
    {
        $objectManager         = Bootstrap::getObjectManager();
        $this->importClass     = $objectManager->create('Shopgate\Import\Model\Service\Import');
        $this->orderClass      = $objectManager->create('Shopgate\Import\Helper\Order');
        $this->orderRepository = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
    }

    /**
     * Test that all 3 product types get inserted
     * into the order and that order is created
     * correctly
     *
     * @param \ShopgateOrder $order
     *
     * @dataProvider simpleOrderProvider
     * @throws \ShopgateLibraryException
     */
    public function testOrderImport(\ShopgateOrder $order)
    {
        $result = $this->importClass->addOrder($order);
        /** @var \Shopgate\Import\Helper\Order $sgOrder */
        $sgOrder = Bootstrap::getObjectManager()->get('Shopgate\Import\Helper\Order');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $sgOrder->loadMethods([]);

        $this->assertNotEmpty($result);
        $this->assertCount(3, $order->getAllVisibleItems());
        $this->orderHolder[] = $result;
    }

    /**
     * @return array
     */
    public function simpleOrderProvider()
    {
        $dataManager = new SgDataManager();

        return [
            'simple order' => [
                new \ShopgateOrder(
                    [
                        'order_number'        => rand(1000000000, 9999999999),
                        'is_paid'             => 0,
                        'mail'                => 'shopgate@shopgate.com',
                        'amount_shop_payment' => '5.00',
                        'amount_complete'     => '149.85',
                        'shipping_infos'      => [
                            'amount' => '4.90',
                        ],
                        'invoice_address'     => $dataManager->getGermanAddress(),
                        'delivery_address'    => $dataManager->getGermanAddress(false),
                        'external_coupons'    => [],
                        'shopgate_coupons'    => [],
                        'items'               => [
                            $dataManager->getSimpleProduct(),
                            $dataManager->getConfigurableProduct(),
                            $dataManager->getGroupedProduct()
                        ]
                    ]
                )
            ],
        ];
    }

    /**
     * Delete all created orders & quotes
     */
    public function tearDown()
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get('\Magento\Framework\Registry');
        $registry->register('isSecureArea', true, true);
        /** @var \Magento\Quote\Model\QuoteRepository $quoteRepo */
        $quoteRepo = Bootstrap::getObjectManager()->create('Magento\Quote\Model\QuoteRepository');

        foreach ($this->orderHolder as $order) {
            if (isset($order['external_order_id'])) {
                $order = $this->orderRepository->get($order['external_order_id']);
                $quoteRepo->delete($quoteRepo->get($order->getQuoteId()));
                $this->orderRepository->delete($order);
            }
        }
    }
}
