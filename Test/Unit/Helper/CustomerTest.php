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

namespace Shopgate\Import\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @coversDefaultClass \Shopgate\Import\Helper\Customer
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Load object manager for initialization
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param $expected - expected array to come out
     * @param $params   - params to pass to the tested method
     *
     * @covers ::getMagentoGender
     *
     * @dataProvider magentoGenderProvider
     */
    public function testGetMagentoGender($expected, $params)
    {
        $customerModel = $this->objectManager->getObject(
            'Shopgate\Import\Helper\Customer\Utility'
        );

        $reflection = new \ReflectionClass($customerModel);
        $method     = $reflection->getMethod('getMagentoGender');
        $method->setAccessible(true);

        $actual = $method->invoke($customerModel, $params);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function magentoGenderProvider()
    {
        return
            [
                [
                    'expected' => '3',
                    'param'    => '0'
                ],
                [
                    'expected' => '1',
                    'param'    => 'm'
                ],
                [
                    'expected' => '2',
                    'param'    => 'f'
                ],
                [
                    'expected' => '3',
                    'param'    => 'xyz'
                ]
            ];
    }
}