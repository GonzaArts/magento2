<?php

namespace Aplazame\Payment\Model\BusinessModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Quote\Model\Quote;

class Checkout
{
    public static function createFromQuote(Quote $quote)
    {
        $checkout = new self();
        $checkout->toc = true;
        $checkout->merchant = [
            'notification_url' => self::getUrlBuilder()->getUrl(
                'aplazame/api/index',
                [
                    '_query' => [
                        'path' => '/confirm/',
                    ],
                    '_nosid' => true,
                    '_secure' => true,
                ]
            ),
        ];
        $checkout->order = Order::crateFromQuote($quote);
        $checkout->customer = Customer::createFromQuote($quote);
        $checkout->billing = Address::createFromAddress($quote->getBillingAddress());
        $checkout->shipping = ShippingInfo::createFromQuote($quote);
        $checkout->meta = [
            'module' => [
                'name' => 'aplazame:magento',
                'version' => self::getModuleVersion(),
            ],
            'version' => self::getMagentoVersion(),
        ];

        return $checkout;
    }

    /**
     * @return string
     */
    private static function getMagentoVersion()
    {
        $objectManager   = ObjectManager::getInstance();
        /** @var ProductMetadataInterface $productMetadata */
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');

        return $productMetadata->getVersion();
    }

    /**
     * @return string
     */
    private static function getModuleVersion()
    {
        $objectManager   = ObjectManager::getInstance();
        $moduleInfo =  $objectManager->get('Magento\Framework\Module\ModuleList')->getOne('Aplazame_Payment');

        return $moduleInfo['setup_version'];
    }

    /**
     * @return \Magento\Framework\Url
     */
    private static function getUrlBuilder()
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->get('Magento\Framework\Url');
    }
}
