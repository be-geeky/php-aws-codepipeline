<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\Elasticsearch\Model\System\Config\Source\Fuzzyness;

use Magento\Framework\Option\ArrayInterface;

class Mode implements ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'AUTO', 'label' => __('AUTO')],
            ['value' => '0', 'label' => __('0')],
            ['value' => '1', 'label' => __('1')],
            ['value' => '2', 'label' => __('2')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
