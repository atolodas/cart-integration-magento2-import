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

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Directory\Model\Region;
use ShopgateAddress;

class Utility extends \Shopgate\Base\Helper\Customer\Utility
{

    /**
     * @param AddressInterface $magentoAddress
     * @param ShopgateAddress  $shopgateAddress
     *
     * @return AddressInterface
     */
    public function convertToMagentoAddress($magentoAddress, $shopgateAddress)
    {
        $magentoAddress->setFirstname($shopgateAddress->getFirstName());
        $magentoAddress->setLastname($shopgateAddress->getLastName());
        $magentoAddress->setCompany($shopgateAddress->getCompany());
        $magentoAddress->setStreet($shopgateAddress->getStreet1());
        $magentoAddress->setCity($shopgateAddress->getCity());
        $magentoAddress->setPostcode($shopgateAddress->getZipcode());
        $magentoAddress->setCountryId($shopgateAddress->getCountry());

        if ($phoneNumber = $shopgateAddress->getPhone()) {
            $magentoAddress->setTelephone($phoneNumber);
        } elseif ($phoneNumber = $shopgateAddress->getMobile()) {
            $magentoAddress->setTelephone($phoneNumber);
        }

        if ($shopgateAddress->getState()) {
            /** @var Region $regionItem */
            $regionItem = $this->countryFactory
                ->create()
                ->getRegionCollection()
                ->addCountryFilter($shopgateAddress->getCountry())
                ->addRegionCodeFilter($shopgateAddress->getState())
                ->getFirstItem();
            if ($regionItem->getId()) {
                $magentoAddress->setRegion($regionItem->getId());
            }
        }

        return $magentoAddress;
    }
}
