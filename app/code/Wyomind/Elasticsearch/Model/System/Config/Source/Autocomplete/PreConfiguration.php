<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\Elasticsearch\Model\System\Config\Source\Autocomplete;

class PreConfiguration implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = [
            'value' => "STANDARD",
            'label' => __("Standard / One Column"),
        ];
        $options[] = [
            'value' => "CUSTOMIZED",
            'label' => __("Custom"),
        ];
        
        return $options;
    }

}
