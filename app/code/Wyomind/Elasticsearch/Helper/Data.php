<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager = null;
    protected $_systemStore = null;
    protected $_configHelper = null;
    
    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Store\Model\System\Store $systemStore,
        \Wyomind\Elasticsearch\Helper\Config $configHelper
    ) {
    
        $this->_storeManager = $storeManager;
        $this->_systemStore = $systemStore;
        $this->_configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * @param mixed $store
     * @return string
     */
    public function getAutocompleteUrl($store = null)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore($store);
        $url = sprintf(
            '%sautocomplete.php?store=%s',
            $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, $store->isCurrentlySecure()),
            $store->getCode()
        );

        return $url;
    }
    
    public function isElasticSearch() {
        $store = $this->_storeManager->getStore();
        return $this->_configHelper->getEngine($store) == "elasticsearch";
    }
    
    

    /**
     * @param mixed $store
     * @return float
     */
    public function getCurrentCurrencyRate($store = null)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore($store);

        return (float) $store->getCurrentCurrencyRate();
    }

    public function getFirstStoreviewCode()
    {
        $stores = $this->_systemStore->getStoreCollection();
        foreach ($stores as $store) {
            $store = $store->getCode();
            break;
        }
        return $store;
    }
    
    public function getStoreCode($storeId)
    {
        $store = $this->_systemStore->getStoreData($storeId);
        return $store->getCode();
    }
    
    public function getAllStoreviews()
    {
        return $this->_systemStore->getStoreCollection();
    }
}
