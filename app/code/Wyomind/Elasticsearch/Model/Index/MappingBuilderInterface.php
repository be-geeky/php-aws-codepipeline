<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model\Index;

interface MappingBuilderInterface
{
    /**
     * @param int $storeId
     * @return array
     */
    public function build($storeId);

    /**
     * @param string $code
     *
     * @return TypeInterface
     */
    public function getType($code);

    /**
     * @return TypeInterface[]
     */
    public function getTypes();
}
