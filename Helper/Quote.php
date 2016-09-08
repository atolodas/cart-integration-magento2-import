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
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote as MageQuote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Tax\Helper\Data as Tax;

class Quote extends \Shopgate\Base\Helper\Quote
{
    /**
     * Assigns Shopgate cart customer to quote
     */
    protected function setCustomer()
    {
        parent::setCustomer();

        if ($this->sgBase->isGuest()) {
            $this->quote->setCheckoutMethod(QuoteManagement::METHOD_GUEST);
        }

        $this->coreRegistry->register(
            'rule_data',
            new DataObject(
                [
                    'store_id'          => $this->storeManager->getStore()->getId(),
                    'website_id'        => $this->storeManager->getWebsite()->getId(),
                    'customer_group_id' => $this->quote->getCustomerGroupId()
                ]
            ),
            true
        );
    }

    /**
     * Assigns shipping method to the quote
     */
    protected function setShipping()
    {
        $this->quote->getShippingAddress()
            ->setShippingMethod('shopgate_fix')
            ->setCollectShippingRates(true);
    }

    /**
     * Assigns shipping method to the quote
     */
    protected function setPayment()
    {
        $this->quote->getPayment()->importData(['method' => 'shopgate']);
        $this->quote->getPayment()->setParentTransactionId($this->sgBase->getPaymentTransactionNumber());
    }
}
