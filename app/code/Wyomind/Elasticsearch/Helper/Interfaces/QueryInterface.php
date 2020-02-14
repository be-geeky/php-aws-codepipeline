<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper\Interfaces;

interface QueryInterface
{
    /**
     * @param mixed $store
     * @return string
     */
    public function getQueryOperator($store = null);

    /**
     * @param mixed $store
     * @return bool
     */
    public function isFuzzyQueryEnabled($store = null);
    
    public function getCompatibility($store = null);
}
