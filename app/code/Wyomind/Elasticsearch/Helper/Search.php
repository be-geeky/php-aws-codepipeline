<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper;

use Wyomind\Elasticsearch\Helper\Interfaces\SearchInterface;
use Wyomind\Elasticsearch\Model\AdapterInterface;
use Magento\Catalog\Model\Category as CategoryObject;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Cms\Model\ResourceModel\Page\Collection as PageCollection;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Request\Builder as RequestBuilder;

class Search extends Config implements SearchInterface
{

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $searchRequestName;

    /**
     * @param AdapterInterface $adapter
     * @param RequestBuilder $requestBuilder
     * @param ObjectManagerInterface $objectManager
     * @param Context $context
     * @param string $searchRequestName
     */
    public function __construct(
        AdapterInterface $adapter,
        RequestBuilder $requestBuilder,
        ObjectManagerInterface $objectManager,
        Context $context,
        $searchRequestName = ''
    ) {
    
        $this->adapter = $adapter;
        $this->requestBuilder = $requestBuilder;
        $this->objectManager = $objectManager;
        $this->searchRequestName = $searchRequestName;
        parent::__construct($context);
    }
    
    public function getAdapter() {
        return $this->adapter;
    }

    /**
     * @return CategoryCollection
     */
    protected function createCategoryCollection()
    {
        return $this->objectManager->create(CategoryCollection::class);
    }

    /**
     * @return PageCollection
     */
    protected function createPageCollection()
    {
        return $this->objectManager->create(PageCollection::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryCollection(
        $q,
        $store = null
    ) {
    
        $response = $this->search($q, 'category', $store);
        $categoryIds = array_map(function ($data) {
            return $data['_id'];
        }, $response['hits']['hits']);

        $collection = $this->createCategoryCollection()
                ->addIsActiveFilter()
                ->addIdFilter($categoryIds)
                ->setStoreId($store)
                ->addUrlRewriteToResult()
                ->addAttributeToSelect('*');

        if (!empty($categoryIds)) {
            $collection->getSelect()
                    ->order(new \Zend_Db_Expr(sprintf('FIELD(e.entity_id, %s)', implode(', ', $categoryIds))));
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageCollection(
        $q,
        $store = null
    ) {
    
        $response = $this->search($q, 'cms', $store);

        $pageIds = array_map(function ($data) {
            return $data['_id'];
        }, $response['hits']['hits']);

        $collection = $this->createPageCollection()
                ->addFieldToFilter('page_id', ['in' => $pageIds])
                ->addFieldToFilter('is_active', '1')
                ->addStoreFilter($store);

        if (!empty($pageIds)) {
            $collection->getSelect()
                    ->order(new \Zend_Db_Expr(sprintf('FIELD(main_table.page_id, %s)', implode(', ', $pageIds))));
        }

        return $collection;
    }

    /**
     * @param string $q
     * @param string $type
     * @param mixed $store
     * @return array
     */
    protected function search(
        $q,
        $type,
        $store = null
    ) {
    
        $this->requestBuilder->bindDimension('scope', $store);
        $this->requestBuilder->bind('search_term', $q);
        $this->requestBuilder->setRequestName($this->searchRequestName);
        $request = $this->requestBuilder->create();

        return $this->adapter->request($request, $type);
    }
}
