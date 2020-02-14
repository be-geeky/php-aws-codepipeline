<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Client;

use Wyomind\Elasticsearch\Helper\Interfaces\ClientInterface as ClientHelperInterface;
use Magento\Framework\DataObject;

class Config implements ConfigInterface
{
    /**
     * @var DataObject
     */
    protected $config;

    /**
     * @param ClientHelperInterface $clientHelper
     * @param mixed $store
     */
    public function __construct(ClientHelperInterface $clientHelper, $store = null)
    {
        $this->config = new DataObject($clientHelper->getClientConfig($store));
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectTimeout()
    {
        return (int) $this->getValue('timeout');
    }

    /**
     * {@inheritdoc}
     */
    public function getHosts()
    {
        return explode(',', $this->getValue('servers', ''));
    }
    
    public function isProductWeightEnabled()
    {
        return $this->getValue('enable_product_weight');
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexPrefix()
    {
        return $this->getValue('index_prefix', '');
    }
    
    public function getEnableDebugMode()
    {
        return $this->getValue('enable_debug_mode', '0');
    }

    /**
     * @param string $path
     * @param null $default
     * @return mixed
     */
    protected function getValue($path, $default = null)
    {
        $value = $this->config->getData($path);

        return null === $value ? $default : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function isVerifyHost()
    {
        return (bool) $this->getValue('verify_host', true);
    }
}
