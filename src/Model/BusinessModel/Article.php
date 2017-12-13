<?php

namespace Aplazame\Payment\Model\BusinessModel;

use Aplazame\Serializer\Decimal;
use Magento\Quote\Model\Quote\Item;

class Article
{
    public static function crateFromQuoteItem(Item $item)
    {
        $product = $item->getProduct();

        $discounts = $product->getPrice() - $product->getFinalPrice();

        $aArticle = new self();
        $aArticle->id = $product->getId();
        $aArticle->name = $item->getName();
        $aArticle->url = $product->getProductUrl();
        $aArticle->quantity = intval($item->getQtyOrdered());
        $aArticle->price = Decimal::fromFloat($item->getPrice() + $discounts);
        $aArticle->description = substr($product->getDescription(), 0, 255);
        $aArticle->tax_rate = Decimal::fromFloat($item->getTaxPercent());
        $aArticle->discount = Decimal::fromFloat($item->getDiscountAmount());

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface');
        $aArticle->image_url = self::getImageProduct($productRepository->getById($item->getProductId()));

        return $aArticle;
    }

    public static function getImageProduct(\Magento\Catalog\Model\Product $product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Helper\Image $imageHelper */
        $imageHelper = $objectManager->get(\Magento\Catalog\Helper\Image::class);

        return $imageHelper->init($product, 'product_base_image')->getUrl();
    }
}
