<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper\Interfaces;

use Magento\Eav\Model\Entity\Attribute as EntityAttribute;

interface IndexerInterface
{
    /**
     * @param EntityAttribute $attribute
     * @param $value
     * @param mixed $store
     * @return mixed
     */
    public function formatAttributeValue(EntityAttribute $attribute, $value, $store = null);

    /**
     * Returns searchable attribute codes available for given entity
     *
     * @param string $entity
     * @param mixed $store
     * @return array
     */
    public function getEntitySearchableAttributes($entity, $store = null);

    /**
     * @param mixed $store
     * @return array
     */
    public function getExcludedPageIds($store = null);

    /**
     * @param mixed $store
     * @return string
     */
    public function getLanguage($store = null);

    /**
     * @param EntityAttribute $attribute
     * @return bool
     */
    public function isAttributeIndexable(EntityAttribute $attribute);

    /**
     * @param EntityAttribute $attribute
     * @return bool
     */
    public function isAttributeUsingOptions(EntityAttribute $attribute);

    /**
     * @param string $entity
     * @param mixed $store
     * @return string
     */
    public function isIndexationEnabled($entity, $store = null);

    /**
     * @param mixed $store
     * @return bool
     */
    public function isIndexOutOfStockProducts($store = null);

    /**
     * @param mixed $store
     * @return bool
     */
    public function isManageStock($store = null);
}
