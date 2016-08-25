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

namespace Shopgate\Import\Helper\Customer;

use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Data\Customer as DataCustomer;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use ShopgateCustomer;
use ShopgateLibraryException;

class Setter
{
    /** @var StoreManagerInterface */
    protected $storeManager;
    /** @var CustomerFactory */
    protected $customerFactory;
    /** @var Utility */
    protected $utility;
    /** @var AddressFactory */
    protected $addressFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory       $customerFactory
     * @param AddressFactory        $addressFactory
     * @param Utility               $utility
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        Utility $utility
    ) {
        $this->storeManager    = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->addressFactory  = $addressFactory;
        $this->utility         = $utility;
    }

    /**
     * @param string           $user
     * @param string           $pass
     * @param ShopgateCustomer $customer
     *
     * @throws ShopgateLibraryException
     * @throws \Exception
     */
    public function registerCustomer($user, $pass, ShopgateCustomer $customer)
    {
        try {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            /** @var Customer | DataCustomer $magentoCustomer */
            $magentoCustomer = $this->customerFactory->create();
            $magentoCustomer->setWebsiteId($websiteId);
            $magentoCustomer->setEmail($user);
            $magentoCustomer->setPassword($pass);

            $this->utility->setBasicData($magentoCustomer, $customer);
            $this->utility->setAddressData($magentoCustomer, $customer);
        } catch (AlreadyExistsException $e) {
            throw new ShopgateLibraryException(ShopgateLibraryException::REGISTER_USER_ALREADY_EXISTS);
        } catch (LocalizedException $e) {
            throw new ShopgateLibraryException(ShopgateLibraryException::UNKNOWN_ERROR_CODE, $e->getMessage(), true);
        }
    }
}
