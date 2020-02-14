<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\System\Config\Source\Query;

use Magento\Framework\Option\ArrayInterface;

class Operator implements ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'OR', 'label' => __('OR')],
            ['value' => 'AND', 'label' => __('AND')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['OR' => __('OR'), 'AND' => __('AND')];
    }
}
