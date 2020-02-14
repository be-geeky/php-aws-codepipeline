<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Formatter;

use Wyomind\Elasticsearch\Model\FormatterInterface;

class Integer implements FormatterInterface
{

    /**
     * @param mixed $value
     * @param mixed $store
     * @return mixed
     */
    public function format($value, $store = null)
    {
        $newValue = $value;
        if (strpos($value, ',')) {
            $newValue = array_unique(array_map('intval', explode(',', $value)));
        } else {
            $newValue = (int) $value;
        }
        
        if ($newValue > 2147438647) { // > (2^31)-1 [max integer for elasticsearch]
            return $value;
        }

        return $newValue;
    }
}
