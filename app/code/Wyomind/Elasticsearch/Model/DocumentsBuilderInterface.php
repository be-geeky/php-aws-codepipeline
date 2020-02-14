<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Model;

use Wyomind\Elasticsearch\Model\Index\TypeInterface;

interface DocumentsBuilderInterface
{
    /**
     * @param array $response
     * @param TypeInterface $type
     * @return array
     */
    public function build(array $response, TypeInterface $type);
}
