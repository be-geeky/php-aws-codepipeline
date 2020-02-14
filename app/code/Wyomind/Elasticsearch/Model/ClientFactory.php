<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Model\Client\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;

class ClientFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, $instanceName = ClientInterface::class)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Creates search client
     *
     * @param ConfigInterface $config
     * @return ClientInterface
     */
    public function create(ConfigInterface $config)
    {
        return $this->objectManager->create($this->instanceName, ['config' => $config]);
    }
}
