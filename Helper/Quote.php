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

namespace Shopgate\Import\Helper;

use Magento\Catalog\Model\Product as MageProduct;
use Magento\Framework\Registry;
use Magento\Quote\Model\CouponManagement;
use Magento\Quote\Model\Quote as MageQuote;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as Tax;
use Shopgate\Base\Helper\Product\Utility;
use Shopgate\Base\Helper\Quote\Customer;
use Shopgate\Base\Model\Shopgate\Extended\Base;
use Shopgate\Base\Model\Utility\SgLoggerInterface;

class Quote extends \Shopgate\Base\Helper\Quote
{
    /**
     * @param MageQuote             $quote
     * @param Base                  $base
     * @param SgLoggerInterface     $logger
     * @param Utility               $productHelper
     * @param Tax                   $taxData
     * @param Customer              $quoteCustomer
     * @param Registry              $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param CouponManagement      $couponManagement
     * @param array                 $quoteMethods - di.xml loaded methods
     */
    public function __construct(
        MageQuote $quote,
        Base $base,
        SgLoggerInterface $logger,
        Utility $productHelper,
        Tax $taxData,
        Customer $quoteCustomer,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        CouponManagement $couponManagement,
        array $quoteMethods = []
    ) {
        parent::__construct(
            $quote,
            $base,
            $logger,
            $productHelper,
            $taxData,
            $quoteCustomer,
            $coreRegistry,
            $storeManager,
            $couponManagement,
            $quoteMethods
        );
    }
}
