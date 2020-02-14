<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Model\Index\TypeInterface;

interface QueryBuilderInterface
{

    /**
     * @param string $q
     * @param TypeInterface $type
     * @param mixed $store
     * @return array
     */
    public function build($q, TypeInterface $type, $store = null);
}
