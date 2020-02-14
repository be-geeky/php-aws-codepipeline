<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Block\Search;

class DoYouMean extends \Magento\Framework\View\Element\Template
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
    protected $searchDataHelper = null;
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
        \Magento\Search\Helper\Data $searchDataHelper,
        array $data = []
    )
    {
        $this->queryFactory = $queryFactory;
        $this->searchHelper = $searchHelper;
        $this->searchDataHelper = $searchDataHelper;
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    public function isElasticSearch()
    {
        $store = $this->_storeManager->getStore();
        return $this->configHelper->getEngine($store) == "elasticsearch";
    }

    public function getSuggestion()
    {
        try {
            if (!$this->searchHelper->isSearchEnabled('doyoumean')) {
                return false;
            }
            $query = $this->queryFactory->get();
            return $this->searchHelper->getAdapter()->suggest($query->getQueryText(), "product");
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getQueryUrl($suggestion)
    {
        return $this->searchDataHelper->getResultUrl() . "?q=" . $suggestion;
    }

}
