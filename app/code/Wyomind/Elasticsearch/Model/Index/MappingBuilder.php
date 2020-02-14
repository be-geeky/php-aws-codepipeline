<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Index;

class MappingBuilder implements MappingBuilderInterface
{

    /**
     * @var TypeInterface[]
     */
    protected $typePool = [];
    protected $configHelper = null;

    /**
     * @param TypeFactory $typeFactory
     * @param array $types
     */
    public function __construct(
    TypeFactory $typeFactory,
            \Wyomind\Elasticsearch\Helper\Config $configHelper,
            array $types = []
    )
    {
        $this->configHelper = $configHelper;
        foreach ($types as $code => $typeClass) {
            $this->typePool[$code] = $typeFactory->create($typeClass, $code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build($storeId)
    {
        $mapping = [];
        foreach ($this->typePool as $type) {
            $compatibility = $this->configHelper->getCompatibility($storeId);
            if ($compatibility == 6) {
                $mapping[$type->getCode()] = [
                    'properties' => $type->getProperties($storeId),
                ];
            } elseif ($compatibility < 6) {
                $mapping[$type->getCode()] = [
                    '_all' => [
                        'analyzer' => $type->getLanguageAnalyzer($storeId),
                    ],
                    'properties' => $type->getProperties($storeId),
                ];
            }
        }

        return $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($code)
    {
        return isset($this->typePool[$code]) ? $this->typePool[$code] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return $this->typePool;
    }

}
