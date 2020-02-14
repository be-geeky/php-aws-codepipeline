<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Block\Search;

class Cms extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * @var \Wyomind\Elasticsearch\Helper\Interfaces\SearchInterface
     */
    protected $searchHelper;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    protected $pages;
    protected $configHelper = null;

    /**
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Wyomind\Elasticsearch\Helper\Interfaces\SearchInterface $searchHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
    \Magento\Search\Model\QueryFactory $queryFactory,
        \Wyomind\Elasticsearch\Helper\Interfaces\SearchInterface $searchHelper,
        \Wyomind\Elasticsearch\Helper\Config $configHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        $this->queryFactory = $queryFactory;
        $this->searchHelper = $searchHelper;
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    public function isElasticSearch()
    {
        $store = $this->_storeManager->getStore();
        return $this->configHelper->getEngine($store) == "elasticsearch";
    }

    /**
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    public function getPageCollection()
    {
        try {
            if (!$this->searchHelper->isSearchEnabled('cms')) {
                return false;
            }

            if (null === $this->pages) {
                $store = $this->_storeManager->getStore();
                $query = $this->queryFactory->get();
                $this->pages = $this->searchHelper->getPageCollection($query->getQueryText(), $store->getId());
            }

            if ($limit = $this->getLimit()) {
                $this->pages->getSelect()->limit($limit);
            }

            return $this->pages;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->searchHelper->getLimit('cms');
    }

}
