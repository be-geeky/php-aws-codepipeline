<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Helper\Attribute as AttributeHelper;
use Wyomind\Elasticsearch\Model\Formatter;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\ObjectManagerInterface;

class FormatterFactory
{

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Attribute helper
     *
     * @var AttributeHelper
     */
    protected $attributeHelper;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param AttributeHelper $attributeHelper
     */
    public function __construct(ObjectManagerInterface $objectManager, AttributeHelper $attributeHelper)
    {
        $this->objectManager = $objectManager;
        $this->attributeHelper = $attributeHelper;
    }

    /**
     * @param EntityAttribute $attribute
     * @return FormatterInterface
     */
    public function create(EntityAttribute $attribute)
    {
        $instanceName = Formatter\Standard::class;

        if ($this->attributeHelper->isDecimal($attribute)) {
            $instanceName = Formatter\Decimal::class;
        } elseif ($this->attributeHelper->isBool($attribute)) {
            $instanceName = Formatter\Boolean::class;
        } elseif ($this->attributeHelper->isInteger($attribute)) {
            $instanceName = Formatter\Integer::class;
        } elseif ($this->attributeHelper->isImage($attribute)) {
            $instanceName = Formatter\Image::class;
        }

        return $this->objectManager->create($instanceName);
    }
}
