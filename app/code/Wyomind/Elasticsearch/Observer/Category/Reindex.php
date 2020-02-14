<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Observer\Category;

class Reindex extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Wyomind\Elasticsearch\Model\Indexer\Product
     */
    public $productIndexer = null;
    
    public function __construct(
        \Wyomind\Elasticsearch\Model\Indexer\Category $categoryIndexer,
        \Wyomind\Elasticsearch\Helper\Config $configHelper,
        \Wyomind\Elasticsearch\Model\Indexer\Product $productIndexer)
    {
        parent::__construct($categoryIndexer,$configHelper);
        $this->productIndexer = $productIndexer;
    }
    
    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->isElasticsearchEnabled) {
            $category = $observer->getEvent()->getCategory();
            $this->indexer->executeRow($category->getId());
            
            $products = $category->getProductCollection();
            $productIds = [];

            foreach ($products as $product) {
                $productIds[] = $product->getId();
            }

            if (!empty($productIds)) {
                $this->productIndexer->setCategoryId($category->getId());
                $this->productIndexer->execute($productIds);
            }
        }
    }
}
