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

use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\Customer as DataCustomer;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Magento\Directory\Model\CountryFactory;
use Magento\Tax\Model\ResourceModel\TaxClass\Collection as TaxClassCollection;
use Shopgate\Base\Helper\Shopgate\Customer as CustomerHelper;
use ShopgateCustomer;

class Utility extends \Shopgate\Base\Helper\Customer\Utility
{
    /** @var AddressFactory */
    private $addressFactory;
    /** @var CustomerHelper */
    private $customerHelper;

    /**
     * @param GroupCollection    $customerGroupCollection
     * @param TaxClassCollection $taxCollection
     * @param CountryFactory     $countryFactory
     * @param AddressFactory     $addressFactory
     * @param CustomerHelper     $customer
     */
    public function __construct(
        GroupCollection $customerGroupCollection,
        TaxClassCollection $taxCollection,
        CountryFactory $countryFactory,
        AddressFactory $addressFactory,
        CustomerHelper $customer
    ) {
        $this->addressFactory = $addressFactory;
        $this->customerHelper = $customer;
        parent::__construct($customerGroupCollection, $taxCollection, $countryFactory);
    }

    /**
     * @param Customer | DataCustomer $magentoCustomer
     * @param ShopgateCustomer        $customer
     *
     * @throws \Exception
     */
    public function setBasicData($magentoCustomer, $customer)
    {
        $customFields = $this->customerHelper->getCustomFields($customer);
        $magentoCustomer->setConfirmation(null);
        $magentoCustomer->setFirstname($customer->getFirstName());
        $magentoCustomer->setLastname($customer->getLastName());
        $magentoCustomer->setGender($this->getMagentoGender($customer->getGender()));
        $magentoCustomer->setDob($customer->getBirthday());
        $magentoCustomer->addData($customFields);
        $magentoCustomer->save();
    }

    /**
     * @param Customer | DataCustomer $magentoCustomer
     * @param ShopgateCustomer        $customer
     *
     * @throws \Exception
     */
    public function setAddressData($magentoCustomer, $customer)
    {
        foreach ($customer->getAddresses() as $shopgateAddress) {
            /** @var Address $magentoAddress */
            $magentoAddress = $this->addressFactory->create();
            $magentoAddress->setCustomerId($magentoCustomer->getId());
            $data = $this->customerHelper->createAddressData($customer, $shopgateAddress);
            $magentoAddress->addData($data);
            $magentoAddress->save();

            if ($shopgateAddress->getIsDeliveryAddress() && !$magentoCustomer->getDefaultShipping()) {
                $magentoCustomer->setDefaultShipping($magentoAddress->getId());
                $magentoCustomer->save();
            }

            if ($shopgateAddress->getIsInvoiceAddress() && !$magentoCustomer->getDefaultBilling()) {
                $magentoCustomer->setDefaultBilling($magentoAddress->getId());
                $magentoCustomer->save();
            }
        }
    }
}
