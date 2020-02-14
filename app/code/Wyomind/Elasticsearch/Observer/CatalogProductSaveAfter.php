<?php

namespace Wyomind\Elasticsearch\Observer;

use Wyomind\Elasticsearch\Helper\Config as Config;

class CatalogProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{

    protected $_categoryProductIndexer = null;
    protected $_productCategoryIndexer = null;
    protected $_priceIndexer = null;
    protected $isElasticsearchEnabled = false;

    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Price $priceIndexer,
        \Magento\Catalog\Model\Indexer\Category\Product $categoryProductIndexer,
        \Magento\Catalog\Model\Indexer\Product\Category $productCategoryIndexer,
        Config $configHelper
    ) {
    
        $this->isElasticsearchEnabled = $configHelper->getEngine() == "elasticsearch";
        $this->_categoryProductIndexer = $categoryProductIndexer;
        $this->_productCategoryIndexer = $productCategoryIndexer;
        $this->_priceIndexer = $priceIndexer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->isElasticsearchEnabled) {
            $product = $observer->getProduct();
            $productId = $product->getId();
            $this->_categoryProductIndexer->executeRow($productId);
            $this->_productCategoryIndexer->executeRow($productId);
            $this->_priceIndexer->executeRow($productId);
        }
    }
}
