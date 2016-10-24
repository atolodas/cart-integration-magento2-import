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

    public function setUp()
    {
        $objectManager         = Bootstrap::getObjectManager();
        $this->importClass     = $objectManager->create('Shopgate\Import\Model\Service\Import');
        $this->orderClass      = $objectManager->create('Shopgate\Import\Helper\Order');
        $this->orderRepository = $objectManager->create('Magento\Sales\Api\OrderRepositoryInterface');
    }

    /**
     * @param \ShopgateOrder $order
     *
     * @covers ::addOrder
     *
     * @dataProvider simpleOrderProvider
     * @throws \ShopgateLibraryException
     */
    public function testSimpleOrderImport(\ShopgateOrder $order)
    {
        $result = $this->importClass->addOrder($order);
        $this->assertNotEmpty($result);
        $this->orderHolder[] = $result;
    }

    /**
     * @return array
     */
    public function simpleOrderProvider()
    {
        return [
            'simple order ' => [
                new \ShopgateOrder(
                    [
                        'order_number'             => rand(1000000000, 9999999999),
                        'is_paid'                  => 0,
                        'payment_infos'            =>
                            [
                                'shopgate_payment_name' => 'Vorkasse (Eigene Abwicklung)',
                                'purpose'               => 'SG1501511499',
                            ],
                        'external_customer_number' => null,
                        'external_customer_id'     => null,
                        'mail'                     => 'felice5392@googlemail.com',
                        'shipping_group'           => 'DHL',
                        'shipping_type'            => 'MANUAL',
                        'shipping_infos'           => [
                            'name'         => 'DHL Deutschland',
                            'display_name' => 'DHL Pakte gogreen',
                            'description'  => '',
                            'amount'       => '4.90',
                            'weight'       => 0,
                            'api_response' => null,
                        ],
                        'custom_fields'            => [
                            [
                                'label'               => 'Test Custom Field',
                                'internal_field_name' => 'test_field',
                                'value'               => 'test field value',
                            ],
                        ],
                        'payment_method'           => 'PREPAY',
                        'payment_group'            => 'PREPAY',
                        'amount_items'             => '139.95',
                        'amount_shipping'          => '4.90',
                        'amount_shop_payment'      => '5.00',
                        'payment_tax_percent'      => '19.00',
                        'amount_shopgate_payment'  => '0.00',
                        'amount_complete'          => '149.85',
                        'currency'                 => 'EUR',
                        'invoice_address'          => [
                            'is_invoice_address'  => true,
                            'is_delivery_address' => false,
                            'first_name'          => 'Bank',
                            'last_name'           => 'Payment',
                            'gender'              => 'f',
                            'birthday'            => null,
                            'company'             => null,
                            'street_1'            => 'Zevener Straße 8',
                            'street_2'            => null,
                            'zipcode'             => '27404',
                            'city'                => 'Frankenbostel',
                            'country'             => 'DE',
                            'state'               => null,
                            'phone'               => null,
                            'mobile'              => null,
                            'mail'                => null,
                            'custom_fields'       => [
                                [
                                    'label'               => 'Is house?',
                                    'internal_field_name' => 'is_house',
                                    'value'               => 0,
                                ]
                            ],
                        ],
                        'delivery_address'         => [
                            'id'                  => null,
                            'is_invoice_address'  => false,
                            'is_delivery_address' => true,
                            'first_name'          => 'Bank',
                            'last_name'           => 'Payment',
                            'gender'              => 'f',
                            'birthday'            => null,
                            'company'             => null,
                            'street_1'            => 'Zevener Straße 8',
                            'street_2'            => null,
                            'zipcode'             => '27404',
                            'city'                => 'Frankenbostel',
                            'country'             => 'DE',
                            'state'               => null,
                            'phone'               => null,
                            'mobile'              => null,
                            'mail'                => null,
                            'custom_fields'       => [
                                [
                                    'label'               => 'Is house?',
                                    'internal_field_name' => 'is_house',
                                    'value'               => 1,
                                ]
                            ],
                        ],
                        'external_coupons'         =>
                            [
                            ],
                        'shopgate_coupons'         =>
                            [],
                        'items'                    =>
                            [
                                [
                                    'quantity'            => 1,
                                    'internal_order_info' => '{"product_id":2, "item_type":"simple"}',
                                    'options'             => [],
                                    'inputs'              => [],
                                    'attributes'          => [],
                                ]
                            ]
                    ]
                )
            ]
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
