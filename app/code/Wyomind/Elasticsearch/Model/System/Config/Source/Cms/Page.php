<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\System\Config\Source\Cms;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Page implements ArrayInterface
{

    /**
     * @var PageCollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @param PageCollectionFactory $pageCollectionFactory
     */
    public function __construct(
        PageCollectionFactory $pageCollectionFactory
    ) {
    
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $collection = $this->pageCollectionFactory->create();
        foreach ($collection as $page) {
            /** @var \Magento\Cms\Model\Page $page */
            $options[] = [
                'value' => $page->getId(),
                'label' => $page->getTitle(),
            ];
        }

        return $options;
    }
}
