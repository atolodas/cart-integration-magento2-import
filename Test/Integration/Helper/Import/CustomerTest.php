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

namespace Shopgate\Import\Test\Integration\Helper;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopePool;
use Shopgate\Base\Test\Bootstrap;
use Shopgate\Base\Test\Integration\Db\ConfigManager;
use ShopgateAddress;
use ShopgateCustomer;
use ShopgateOrderCustomField;

/**
 * @coversDefaultClass Shopgate\Export\Helper\Customer
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerFactory */
    protected $customerFactory;
    /** @var ConfigManager */
    protected $cfgManager;
    /** @var \Magento\Customer\Model\Customer[] */
    protected $customers;
    /** @var \Shopgate\Import\Model\Service\Import */
    private $importClass;
    /** @var  ScopePool */
    protected $scopePool;

    /**
     * Load object manager for initialization
     */
    public function setUp()
    {
        $objectManager         = Bootstrap::getObjectManager();
        $this->cfgManager      = new ConfigManager;
        $this->importClass     = $objectManager->create('Shopgate\Import\Model\Service\Import');
        $this->customerFactory = $objectManager->create('Magento\Customer\Model\CustomerFactory');
        $this->scopePool       = $objectManager->create('Magento\Framework\App\Config\ScopePool');
    }

    /**
     * Test that we can create a customer with addresses
     *
     * @covers ::registerCustomer
     * @covers Shopgate\Import\Helper\Import::registerCustomer
     */
    public function testRegisterCustomer()
    {
        /** @var ShopgateCustomer $shopgateInputCustomer */
        $shopgateInputCustomer = $this->createShopgateCustomer();

        $shopgateOutputCustomer = $this->importClass->registerCustomer(
            'register_customer',
            '123456',
            'example@me.com',
            '123456kill',
            false,
            $shopgateInputCustomer->toArray()
        );

        $this->assertTrue(is_object($shopgateOutputCustomer));
    }

    /**
     * @return ShopgateCustomer
     */
    private function createShopgateCustomer()
    {
        $customer = new ShopgateCustomer();

        /**
         * global data
         */
        $customer->setFirstname('Max');
        $customer->setLastname('Mustermann');
        $customer->setGender('Male');
        $customer->setBirthday('2000-02-02');

        /**
         * custom field
         */
        $customField = new ShopgateOrderCustomField();
        $customField->setLabel('Custom one');
        $customField->setInternalFieldName('custom_one');
        $customField->setValue('custom value');

        $customer->setCustomFields(
            [$customField]
        );

        /**
         * address
         */
        $address = new ShopgateAddress();
        $address->setFirstName('Sam');
        $address->setLastName('Mustermann');
        $address->setCity('Hackpfüffel');
        $address->setCountry('DE');
        $address->setState('SA');
        $address->setZipcode('123456');
        $address->setStreet1('Am Stadion 4');
        $address->setMobile('123456789');

        $customer->setAddresses(
            [$address]
        );

        /**
         * custom address field
         */
        $customAddressField = new ShopgateOrderCustomField();
        $customAddressField->setLabel('Custom address');
        $customAddressField->setInternalFieldName('custom_address');
        $customAddressField->setValue('custom address value');

        $address->setCustomFields(
            [$customAddressField]
        );

        return $customer;
    }
}
