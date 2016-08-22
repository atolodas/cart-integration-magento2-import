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

use Magento\Store\Model\StoreManagerInterface;
use Shopgate\Base\Api\Config\SgCoreInterface;
use Shopgate\Import\Api\ImportInterface;
use Shopgate\Import\Helper\Customer\Setter as CustomerSetter;
use ShopgateCustomer;

class Import implements ImportInterface
{

    /** @var CustomerSetter */
    private $customerSetter;
    /** @var SgCoreInterface */
    private $config;
    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param CustomerSetter        $customerSetter
     * @param SgCoreInterface       $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerSetter $customerSetter,
        SgCoreInterface $config,
        StoreManagerInterface $storeManager
    ) {
        $this->customerSetter = $customerSetter;
        $this->config         = $config;
        $this->storeManager   = $storeManager;
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
}
