<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper;

use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\App\Helper\AbstractHelper;

class Attribute extends AbstractHelper
{
    /**
     * @param EntityAttribute $attribute
     * @return bool
     */
    public function isBool(EntityAttribute $attribute)
    {
        return $attribute->getSourceModel() == 'eav/entity_attribute_source_boolean'
            || $attribute->getFrontendInput() == 'boolean';
    }
    /**
     * @param EntityAttribute $attribute
     * @return bool
     */
    public function isDate(EntityAttribute $attribute)
    {
        return $attribute->getBackendType() == 'datetime';
    }

    /**
     * @param EntityAttribute $attribute
     * @return bool
     */
    public function isDecimal(EntityAttribute $attribute)
    {
        return $attribute->getBackendType() == 'decimal'
            || $attribute->getFrontendClass() == 'validate-number';
    }

    /**
     * @param EntityAttribute $attribute
     * @return bool
     */
    public function isImage(EntityAttribute $attribute)
    {
        return $attribute->getFrontendInput() == 'media_image';
    }

    /**
     * @param EntityAttribute $attribute
     * @return bool
     */
    public function isInteger(EntityAttribute $attribute)
    {
        return $attribute->usesSource() && $attribute->getBackendType() == 'int'
            || $attribute->getFrontendClass() == 'validate-digits';
    }
}
