<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Block\Search;

class Category extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * @var \Wyomind\Elasticsearch\Helper\Interfaces\CategoryInterface
     */
    protected $categoryHelper;

    /**
     * @var \Wyomind\Elasticsearch\Helper\Interfaces\SearchInterface
     */
    protected $searchHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $categories;
    protected $configHelper = null;

    /**
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Wyomind\Elasticsearch\Helper\Interfaces\CategoryInterface $categoryHelper
     * @param \Wyomind\Elasticsearch\Helper\Interfaces\SearchInterface $searchHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
    \Magento\Search\Model\QueryFactory $queryFactory,
        \Wyomind\Elasticsearch\Helper\Interfaces\CategoryInterface $categoryHelper,
        \Wyomind\Elasticsearch\Helper\Config $configHelper,
        \Wyomind\Elasticsearch\Helper\Interfaces\SearchInterface $searchHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        $this->queryFactory = $queryFactory;
        $this->categoryHelper = $categoryHelper;
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
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection()
    {
        try {
            if (!$this->searchHelper->isSearchEnabled('category')) {
                return false;
            }

            if (null === $this->categories) {
                $store = $this->_storeManager->getStore();
                $query = $this->queryFactory->get();
                $this->categories = $this->searchHelper->getCategoryCollection($query->getQueryText(), $store->getId());
            }

            if ($limit = $this->getLimit()) {
                $this->categories->getSelect()->limit($limit);
            }

            return $this->categories;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param string $separator
     * @return string
     */
    public function getCategoryPathName(\Magento\Catalog\Model\Category $category,
        $separator = ' > ')
    {
        if ($this->searchHelper->getShowCategoryPath()) {
            return $this->categoryHelper->getCategoryPathName($category, $separator);
        }

        return $category->getName();
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->searchHelper->getLimit('category');
    }

}
