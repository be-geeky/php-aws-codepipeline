<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Observer\Cms;

use Wyomind\Elasticsearch\Model\Indexer\CmsFactory as PageIndexer;
use Wyomind\Elasticsearch\Helper\Config as Config;

abstract class AbstractObserver
{
    /**
     * @var PageIndexer
     */
    protected $indexer;
    protected $isElasticsearchEnabled = false;

    /**
     * @param PageIndexer $pageIndexer
     */
    public function __construct(PageIndexer $pageIndexer, Config $configHelper)
    {
        $this->indexer = $pageIndexer;
        $this->isElasticsearchEnabled = $configHelper->getEngine() == "elasticsearch";
    }
}
