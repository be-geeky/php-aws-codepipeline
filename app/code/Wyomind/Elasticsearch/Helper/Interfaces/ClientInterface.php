<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper\Interfaces;

interface ClientInterface
{
    /**
     * @param mixed $store
     * @return array
     */
    public function getClientConfig($store = null);
}
