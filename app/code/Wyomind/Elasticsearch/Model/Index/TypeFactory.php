<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Index;

use Magento\Framework\ObjectManagerInterface;

class TypeFactory
{

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates index type object
     *
     * @param string $instanceName
     * @param string $code
     * @return TypeInterface
     */
    public function create(
        $instanceName,
        $code
    ) {
    
        $type = $this->objectManager->create($instanceName, ['code' => $code]);

        if (!$type instanceof TypeInterface) {
            throw new \LogicException('Type must implement ' . TypeInterface::class);
        }

        return $type;
    }
}
