<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper\Interfaces;

interface AdapterInterface
{
    /**
     * @param mixed $store
     * @return array
     */
    public function getIndexSettings($store = null);

    /**
     * @param mixed $store
     * @return bool
     */
    public function isSafeReindexEnabled($store = null);
}
