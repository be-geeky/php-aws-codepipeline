<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Observer\Category;

use Wyomind\Elasticsearch\Model\Indexer\Category as CategoryIndexer;
use Wyomind\Elasticsearch\Helper\Config as Config;

abstract class AbstractObserver
{
    /**
     * @var CategoryIndexer
     */
    protected $indexer;
    protected $isElasticsearchEnabled = false;

    /**
     * @param CategoryIndexer $categoryIndexer
     */
    public function __construct(CategoryIndexer $categoryIndexer, Config $configHelper)
    {
        $this->indexer = $categoryIndexer;
        $this->isElasticsearchEnabled = $configHelper->getEngine() == "elasticsearch";
        
    }
}
