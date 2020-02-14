<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Helper\Interfaces;

use Magento\Store\Api\Data\StoreInterface;

interface AutocompleteInterface
{
    /**
     * @param StoreInterface $store
     * @return void
     */
    public function saveConfig(StoreInterface $store);
}
