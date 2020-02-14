<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Observer\Cms;

use Magento\Cms\Model\Page;
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
            $page = $observer->getEvent()->getObject();
            $this->indexer->create()->deleteRow($page->getId());
        }
    }
}
