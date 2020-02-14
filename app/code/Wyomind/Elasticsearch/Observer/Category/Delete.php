<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Observer\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Delete extends AbstractObserver implements ObserverInterface
{

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        
        if ($this->isElasticsearchEnabled) {
            $category = $observer->getEvent()->getCategory();
            $this->indexer->deleteRow($category->getId());
        }
    }
}
