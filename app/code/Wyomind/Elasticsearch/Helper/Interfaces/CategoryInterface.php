<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper\Interfaces;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;

interface CategoryInterface
{
    /**
     * Retrieve all categories of given store with path names
     *
     * @param mixed $storeId
     * @return CategoryCollection
     */
    public function getCategoriesWithPathNames($storeId);

    /**
     * Return given category path name using specified separator
     *
     * @param Category $category
     * @param string $separator
     * @return string
     */
    public function getCategoryPathName(Category $category, $separator = ' > ');
}
