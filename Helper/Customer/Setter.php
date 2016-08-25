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

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Data\Address;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use ShopgateAddress;
use ShopgateCustomer;
use ShopgateLibraryException;

class Setter
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Utility
     */
    protected $utility;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * Setter constructor.
     *
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
     */
    public function registerCustomer($user, $pass, ShopgateCustomer $customer)
    {
        try {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            /** @var Customer $magentoCustomer */
            $magentoCustomer = $this->customerFactory->create();

            $magentoCustomer->setWebsiteId($websiteId);
            $magentoCustomer->setEmail($user);
            $magentoCustomer->setPassword($pass);

            $this->setBasicData($magentoCustomer, $customer);
            $this->addCustomFields($magentoCustomer, $customer);
            $this->setAddressData($magentoCustomer, $customer);
        } catch (AlreadyExistsException $e) {
            throw new ShopgateLibraryException(ShopgateLibraryException::REGISTER_USER_ALREADY_EXISTS);
        } catch (LocalizedException $e) {
            throw new ShopgateLibraryException(ShopgateLibraryException::UNKNOWN_ERROR_CODE, $e->getMessage(), true);
        }
    }

    /**
     * @param Customer         $magentoCustomer
     * @param ShopgateCustomer $customer
     */
    protected function setBasicData($magentoCustomer, $customer)
    {
        $magentoCustomer->setConfirmation(null);
        $magentoCustomer->setFirstname($customer->getFirstName());
        $magentoCustomer->setLastname($customer->getLastName());
        $magentoCustomer->setGender($this->utility->getMagentoGender($customer->getGender()));
        $magentoCustomer->setDob($customer->getBirthday());
        $magentoCustomer->save();
    }

    /**
     * @param Customer         $magentoCustomer
     * @param ShopgateCustomer $customer
     */
    protected function setAddressData($magentoCustomer, $customer)
    {
        foreach ($customer->getAddresses() as $shopgateAddress) {

            /** @var Address $magentoAddress */
            $magentoAddress = $this->addressFactory->create();
            $magentoAddress->setCustomerId($magentoCustomer->getId());
            $magentoAddress = $this->utility->convertToMagentoAddress($magentoAddress, $shopgateAddress);
            $magentoAddress->save();

            if ($shopgateAddress->getIsDeliveryAddress() && !$magentoCustomer->getDefaultShipping()) {
                $magentoCustomer->setDefaultShipping($magentoAddress->getId());
                $magentoCustomer->save();
            }

            if ($shopgateAddress->getIsInvoiceAddress() && !$magentoCustomer->getDefaultBilling()) {
                $magentoCustomer->setDefaultBilling($magentoAddress->getId());
                $magentoCustomer->save();
            }

            $this->addCustomFields($magentoAddress, $shopgateAddress);
        }
    }

    /**
     * @param Address | Customer                 $magentoObject
     * @param ShopgateAddress | ShopgateCustomer $shopgateObject
     */
    protected function addCustomFields($magentoObject, $shopgateObject)
    {
        if (count($shopgateObject->getCustomFields()) > 0) {
            foreach ($shopgateObject->getCustomFields() as $field) {
                $magentoObject->setData($field->getInternalFieldName(), $field->getValue());
            }

            $magentoObject->save();
        }
    }
}
