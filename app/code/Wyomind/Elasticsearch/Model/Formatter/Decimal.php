<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Formatter;

use Wyomind\Elasticsearch\Model\FormatterInterface;

class Decimal implements FormatterInterface
{
    /**
     * @param mixed $value
     * @param mixed $store
     * @return mixed
     */
    public function format($value, $store = null)
    {
        if (strpos($value, ',')) {
            $value = array_unique(array_map('floatval', explode(',', $value)));
        } else {
            $value = (float) $value;
        }

        return $value;
    }
}
