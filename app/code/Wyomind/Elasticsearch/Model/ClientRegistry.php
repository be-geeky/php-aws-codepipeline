<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Model\Client\ConfigFactory;

class ClientRegistry
{
    /**
     * @var array
     */
    protected $clients = [];

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @param ClientFactory $clientFactory
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        ClientFactory $clientFactory,
        ConfigFactory $configFactory
    ) {
        $this->clientFactory = $clientFactory;
        $this->configFactory = $configFactory;
    }

    /**
     * Returns search client object created with store parameters and config
     *
     * @param int $storeId
     * @return ClientInterface
     */
    public function get($storeId)
    {
        if (!isset($this->clients[$storeId])) {
            $config = $this->configFactory->create($storeId);
            $this->clients[$storeId] = $this->clientFactory->create($config);
        }

        return $this->clients[$storeId];
    }
}
