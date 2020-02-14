<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper;

use Wyomind\Elasticsearch\Helper\Interfaces\AdapterInterface;
use Wyomind\Elasticsearch\Helper\Interfaces\ClientInterface;
use Wyomind\Elasticsearch\Helper\Interfaces\QueryInterface;
use Magento\CatalogInventory\Model\Configuration as InventoryConfig;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper implements AdapterInterface, ClientInterface, QueryInterface
{

    const CATEGORIES_ID = "id";
    const CATEGORIES_URL = "url";
    const CATEGORIES_PATH = "path";
    const CMS_ID = "id";
    const PRODUCT_CATEGORIES_PARENT_ID = "categories_parent_ids";
    const PRODUCT_CATEGORIES_ID = "categories_ids";
    const PRODUCT_CATEGORIES = "categories";
    const PRODUCT_SHORTEST_URL = "shortest_url";
    const PRODUCT_LONGEST_URL = "longest_url";
    const PRODUCT_PARENT_IDS = "parent_ids";
    const PRODUCT_PRICES = "prices";
    const PRODUCT_URL = "url";
    const NAME_SUGGESTER = "name_suggester";
    const SKU_SUGGESTER = "sku_suggester";
    const XML_PATH_CATALOG_SEARCH_ENGINE = 'catalog/search/engine';
    const XML_PATH_ELASTICSEARCH_SERVERS = 'catalog/search/elasticsearch/servers';
    const XML_PATH_ELASTICSEARCH_COMPATIBILITY = 'catalog/search/elasticsearch/compatibility';
    const XML_PATH_ELASTICSEARCH_INDEX_PREFIX = 'catalog/search/elasticsearch/index_prefix';
    const XML_PATH_ELASTICSEARCH_CONFIG = 'catalog/search/elasticsearch';
    const XML_PATH_ELASTICSEARCH_INDEX_SETTINGS = 'catalog/search/elasticsearch/index_settings';
    const XML_PATH_ELASTICSEARCH_SAFE_REINDEX = 'catalog/search/elasticsearch/safe_reindex';
    const XML_PATH_ELASTICSEARCH_QUERY_OPERATOR = 'catalog/search/elasticsearch/query_operator';
    const XML_PATH_ELASTICSEARCH_ENABLE_FUZZY_QUERY = 'catalog/search/elasticsearch/enable_fuzzy_query';
    const XML_PATH_ELASTICSEARCH_FUZZY_QUERY_MODE = 'catalog/search/elasticsearch/fuzzy_query_mode';
    const XML_PATH_ELASTICSEARCH_ENABLE_DEBUG_MODE = 'catalog/search/elasticsearch/enable_debug_mode';
    const XML_PATH_ELASTICSEARCH_ENABLE_PRODUCT_WEIGHT = 'catalog/search/elasticsearch/enable_product_weight';
    const XML_PATH_ELASTICSEARCH_VERIFY_HOST = 'catalog/search/elasticsearch/verify_host';
    const XML_PATH_ELASTICSEARCH_ENABLE_PUB_FOLDER = 'catalog/search/elasticsearch/enable_pub_folder';
    const XML_PATH_ELASTICSEARCH_IMAGE_SIZE = 'elasticsearch/types/product/image_size';
    const XML_PATH_ELASTICSEARCH_SHOW_CATEGORY_PATH = 'elasticsearch/types/category/show_path';
    const XML_PATH_ELASTICSEARCH_EXCLUDED_PAGES = 'elasticsearch/types/cms/excluded_pages';

    public function getCompatibility($store = null)
    {
        return $this->getValue(self::XML_PATH_ELASTICSEARCH_COMPATIBILITY, $store);
    }

    /**
     * @param mixed $store
     * @return array
     */
    public function getClientConfig($store = null)
    {
        return $this->getValue(self::XML_PATH_ELASTICSEARCH_CONFIG, $store);
    }

    public function getIndexPrefix($store = null)
    {
        return $this->getValue(self::XML_PATH_ELASTICSEARCH_INDEX_PREFIX, $store);
    }

    public function getEngine($store = null)
    {
        return $this->getValue(self::XML_PATH_CATALOG_SEARCH_ENGINE, $store);
    }

    public function getEnableDebugMode($store = null)
    {
        return $this->getValue(self::XML_PATH_ELASTICSEARCH_ENABLE_DEBUG_MODE, $store);
    }

    public function getServers($store = null)
    {
        return explode(',', $this->getValue(self::XML_PATH_ELASTICSEARCH_SERVERS, $store));
    }

    /**
     * {@inheritdoc}
     */
    public function getEntitySearchableAttributes(
    $entity,
        $store = null
    )
    {

        return explode(',', $this->getValue('elasticsearch/types/' . $entity . '/attributes', $store));
    }

    /**
     * {@inheritdoc}
     */
    public function getExcludedPageIds($store = null)
    {
        return explode(',', $this->getValue(self::XML_PATH_ELASTICSEARCH_EXCLUDED_PAGES, $store));
    }

    /**
     * @param string $path
     * @param mixed $store
     * @return bool
     */
    public function getFlag(
    $path,
        $store = null
    )
    {

        return $this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexSettings($store = null)
    {
        $settings = $this->getValue(self::XML_PATH_ELASTICSEARCH_INDEX_SETTINGS, $store);

        return json_decode($settings, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage($store = null)
    {
        return \Locale::getDisplayLanguage($this->getLocaleCode($store), 'en_US');
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit(
    $entity,
        $store = null
    )
    {

        return (int) $this->getFlag('elasticsearch/types/' . $entity . '/limit', $store);
    }

    /**
     * @param mixed $store
     * @return mixed
     */
    public function getLocaleCode($store = null)
    {
        return $this->getValue(Custom::XML_PATH_GENERAL_LOCALE_CODE, $store);
    }

    /**
     * @param mixed $store
     * @return int
     */
    public function getProductImageSize($store = null)
    {
        return $this->getValue(self::XML_PATH_ELASTICSEARCH_IMAGE_SIZE, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryOperator($store = null)
    {
        return $this->getValue(self::XML_PATH_ELASTICSEARCH_QUERY_OPERATOR, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function getShowCategoryPath($store = null)
    {
        return $this->getFlag(self::XML_PATH_ELASTICSEARCH_SHOW_CATEGORY_PATH, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnablePubFolder($store = null)
    {
        return $this->getFlag(self::XML_PATH_ELASTICSEARCH_ENABLE_PUB_FOLDER, $store);
    }
    
    /**
     * @param mixed $store
     * @return string
     */
    public function getTheme($store = null)
    {
        return $this->getValue(DesignInterface::XML_PATH_THEME_ID, $store);
    }

    /**
     * @param string $path
     * @param mixed $store
     * @return mixed
     */
    public function getValue($path,
        $store = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function isFuzzyQueryEnabled($store = null)
    {
        return $this->getFlag(self::XML_PATH_ELASTICSEARCH_ENABLE_FUZZY_QUERY, $store);
    }

    public function getFuzzyQueryMode($store = null)
    {
        return $this->getValue(self::XML_PATH_ELASTICSEARCH_FUZZY_QUERY_MODE, $store);
    }

    public function isProductWeightEnabled($store = null)
    {
        return $this->getFlag(self::XML_PATH_ELASTICSEARCH_ENABLE_PRODUCT_WEIGHT, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function isIndexationEnabled(
    $entity,
        $store = null
    )
    {

        return $this->getFlag('elasticsearch/types/' . $entity . '/enable', $store);
    }

    /**
     * {@inheritdoc}
     */
    public function isIndexOutOfStockProducts($store = null)
    {
        return $this->getFlag(InventoryConfig::XML_PATH_SHOW_OUT_OF_STOCK, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function isManageStock($store = null)
    {
        return $this->getFlag(InventoryConfig::XML_PATH_MANAGE_STOCK, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function isSafeReindexEnabled($store = null)
    {
        return $this->getFlag(self::XML_PATH_ELASTICSEARCH_SAFE_REINDEX, $store);
    }

    /**
     * {@inheritdoc}
     */
    public function isSearchEnabled(
    $entity,
        $store = null
    )
    {
        return ($entity == "doyoumean" || $this->isIndexationEnabled($entity, $store)) &&
            $this->getFlag('elasticsearch/types/' . $entity . '/enable_search', $store);
    }

}
