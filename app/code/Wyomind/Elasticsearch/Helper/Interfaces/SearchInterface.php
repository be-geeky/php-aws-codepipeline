<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper\Interfaces;

use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Cms\Model\ResourceModel\Page\Collection as PageCollection;

interface SearchInterface
{
    /**
     * @param string $q
     * @param mixed $store
     * @return CategoryCollection
     */
    public function getCategoryCollection($q, $store = null);

    /**
     * @param string $entity
     * @param mixed $store
     * @return int
     */
    public function getLimit($entity, $store = null);

    /**
     * @param string $q
     * @param mixed $store
     * @return PageCollection
     */
    public function getPageCollection($q, $store = null);

    /**
     * Indicates whether category path is displayed in search results or not
     *
     * @param mixed $store
     * @return bool
     */
    public function getShowCategoryPath($store = null);

    /**
     * Checks if search results are enabled for given entity and store
     *
     * @param string $entity
     * @param mixed $store
     * @return bool
     */
    public function isSearchEnabled($entity, $store = null);
}
