<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Autocomplete;

interface ConfigInterface
{
    /**
     * @return int
     */
    public function getLimit();

    /**
     * @return array
     */
    public function getTypes();
}
