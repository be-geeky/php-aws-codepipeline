<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Autocomplete;

use Wyomind\Elasticsearch\Helper\Interfaces\QueryInterface;
use Wyomind\Elasticsearch\Model\Client\ConfigInterface as ClientConfigInterface;
use Magento\Framework\DataObject;

class Config extends DataObject implements ClientConfigInterface, ConfigInterface, QueryInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConnectTimeout()
    {
        return (int) $this->getValue('client_config/timeout', 5);
    }

    /**
     * {@inheritdoc}
     */
    public function getHosts()
    {
        return explode(',', $this->getValue('client_config/servers', '127.0.0.1:9200'));
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexPrefix()
    {
        return $this->getValue('client_config/index_prefix', '');
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        return (int) $this->getValue('config/autocomplete/limit', 5);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryOperator($store = null)
    {
        return $this->getValue('client_config/query_operator', 'AND');
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return $this->getValue('config/types', []);
    }

    public function isProductWeightEnabled()
    {
        return $this->getValue('client_config/enable_product_weight');
    }

    public function getFuzzyQueryMode($store = null)
    {

        return (string) $this->getValue('client_config/fuzzy_query_mode', "AUTO");
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function getValue(
    $key,
            $default = null
    )
    {

        $data = $this->getData($key);

        return null !== $data ? $data : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function isFuzzyQueryEnabled($store = null)
    {
        return (bool) $this->getValue('client_config/enable_fuzzy_query', true);
    }

    /**
     * {@inheritdoc}
     */
    public function isVerifyHost($store = null)
    {
        return (bool) $this->getValue('client_config/verify_host', true);
    }

    public function getCompatibility($store = null)
    {
        return $this->getValue('client_config/compatibility', true);
    }
    public function getEnableDebugMode($store = null)
    {
        return $this->getValue('client_config/enable_debug_mode', true);
    }
    

}
